<?php

/**
 * Description: 数据库操作
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: lib_database.php 164887 2021-12-15 01:41:44Z duanyc $
 */
!defined('IN_INIT') && exit('Access Denied');

class lib_database
{
    protected static $current_link_ip = null; // 当前连接HOST_IP
    /**
     * PDO类
     * @var PDO
     */
    protected static $current_pdo = null;    // 当前连接标识
    protected static $query;
    protected static $query_count = 0;
    protected static $config_name = 'DATABASE';
    protected static $slow_time = 0.1;
    /* 数据库标识列表  */
    protected static $link_list = [];
    /* 例外参数不加单引号 */
    public static $exceptConfig = [
        'now()' => 1, 'null' => 1
    ];
    /* 日志文件 */
    public static $log_file = 'db_debug';
    /* 默认分区 */
    public const DEFAULT_SECTION = '0_0';

    /**
     * 重新启动 MySQL 链接标识
     *
     * @return void
     */
    public static function restartMysql()
    {
        self::$current_link_ip = null;
        self::$current_pdo = null;
        self::$query = null;
        self::$query_count = 0;
        self::$link_list = [];
    }

    /**
     * 关闭 MySQL 链接标识
     *
     * @return void
     */
    public static function closeMysql()
    {
        if (!empty(self::$link_list)) {
            self::$current_link_ip = null;
            self::$current_pdo = null;
            self::$query = null;
            self::$query_count = 0;
            self::$link_list = [];
        }
    }

    /**
     * 连接数据库+选择数据库
     *
     * @param boolean $is_read
     * @param string $index
     *
     * @return mixed
     */
    protected static function initMysql($is_read, $index)
    {
        list($index, $index_min) = explode("_", $index);
        $config = $GLOBALS[self::$config_name];
        $section = $GLOBALS[self::$config_name]['section'][$index]["ips"][$index_min];

        if (empty($section)) {
            return false;
        }

        // 读写分离
        if ($is_read === true) {
            $link = 'link_read_' . $index . '_' . $index_min;
            $key = array_rand($section['slave']);
            $db_host = $section['slave'][$key]['db_host'];
            $db_user = isset($section['slave'][$key]['db_user']) ? $section['slave'][$key]['db_user'] : $config['databases']['db_user'];
            $db_pass = isset($section['slave'][$key]['db_pass']) ? $section['slave'][$key]['db_pass'] : $config['databases']['db_pass'];
            $db_name = isset($section['slave'][$key]['db_name']) ? $section['slave'][$key]['db_name'] : $config['databases']['db_name'];
        } else {
            $link = 'link_write_' . $index . '_' . $index_min;
            $db_host = $section['master']['db_host'];
            $db_user = isset($section['master']['db_user']) ? $section['master']['db_user'] : $config['databases']['db_user'];
            $db_pass = isset($section['master']['db_pass']) ? $section['master']['db_pass'] : $config['databases']['db_pass'];
            $db_name = isset($section['master']['db_name']) ? $section['master']['db_name'] : $config['databases']['db_name'];
        }

        self::$current_link_ip = $db_host;

        if (empty(self::$link_list[$link]) || !self::ping(self::$link_list[$link])) {
            try {
                $db_host = explode(":", $db_host);
                $charset = str_replace('-', '', strtolower($GLOBALS[self::$config_name]['databases']['db_charset']));
                $dsn = "mysql:host={$db_host[0]};port={$db_host[1]};dbname={$db_name};charset={$charset}";
                $link_resource = new PDO($dsn, $db_user, $db_pass);
                self::$link_list[$link] = $link_resource;
            } catch (PDOException  $e) {
                self::errorLog($e, 'PDOException');
            } catch (Exception $e) {
                self::errorLog($e, 'Exception');
            }
        }

        return self::$link_list[$link];
    }

    /**
     * SQL操作
     *
     * @param string $sql
     * @param string $index
     * @param boolean $is_master
     * @param array $data
     * @param int $redo
     *
     * @return Object|bool|int
     */
    public static function query($sql, $index = '', $is_master = false, $data = [], $redo = 3)
    {
        $sql = trim($sql);
        $index = empty($index) ? self::DEFAULT_SECTION : $index;

        /* 主从选择 */
        if (empty($is_master) && stripos($sql, 's') === 0) {
            $is_read = true;
        } else {
            $is_read = false;
        }

        self::$current_pdo = self::initMysql(true, $index);
        try {
            /* 记录慢查询日志 */
            $st = microtime(true);
            self::checkSql($sql);

            if ($is_read) {
                self::$current_pdo->setAttribute(PDO::ATTR_PERSISTENT, false);
                self::$current_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                self::$current_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$current_pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
                if (!empty($data)) {
                    self::$query = self::$current_pdo->prepare($sql);
                    if (self::$query === false) {
                        throw new Exception('Syntax error');
                    }
                    self::$query->execute($data);
                } else {
                    self::$query = self::$current_pdo->query($sql);
                }
            } else {
                self::$current_pdo->setAttribute(PDO::ATTR_PERSISTENT, false);
                self::$current_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                self::$current_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                self::$current_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                if (!empty($data)) {
                    $statement = self::$current_pdo->prepare($sql);
                    $statement->execute($data);
                    self::$query = $statement->rowCount();
                } else {
                    self::$query = self::$current_pdo->exec($sql);
                }
            }

            $cost_time = microtime(true) - $st;

            if ($cost_time > self::$slow_time) {
                cutil_php_log("use time: " . $cost_time . "s, sql: " . $sql, 'sql_slow');
            }

            if (self::$query === false) {
                throw new Exception('Execution failed');
            } else {
                self::$query_count++;
                return self::$query;
            }
        } catch (Exception $e) {
            if ($redo > 0) {
                self::errorLog($e, $sql . ", DATA: " . json_encode($data) . "ErrorInfo: " . json_encode(self::$current_pdo->errorInfo()), false);
                self::closeMysql();
                $statement = null;
                return self::query($sql, $index, $is_master, $data, $redo - 1);
            }
            self::errorLog($e, $sql . ", DATA: " . json_encode($data));
        }

        return false;
    }

    /**
     * 检查sql
     *
     * @param $sql
     *
     * @throws Exception
     */
    public static function checkSql($sql)
    {
        $whereStart = strpos(strtolower($sql), 'where');
        if ($whereStart === false) {
            return;
        }
        $whereSql = substr($sql, $whereStart);
        $whereCount = substr_count($whereSql, '?');
        if ($whereCount > 0) {
            return;
        }
        throw new Exception("{$sql} must use pdo prepare");
    }

    /**
     * 取得最后一次插入记录的ID值
     *
     * @return int
     */
    public static function insertId()
    {
        return self::$current_pdo->lastInsertId();
    }

    // ------------------------------------------------------------------ 数据库操作类扩展 -----------------------------------------------------

    /**
     * 获取方法扩展
     *
     * @param string $sql
     * @param string $index
     * @param boolean $is_master
     * @param array $data
     *
     * @return mixed
     */
    public static function getAll($sql, $index = '', $is_master = false, $data = [])
    {
        $stmt = self::query($sql, $index, $is_master, $data);
        if ($stmt) {
            try {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // 关闭游标，释放结果集
                $stmt->closeCursor();
                return $result;
            } catch (Exception $e) {
                self::errorLog($e, $sql);
            }
        }
        return null;
    }

    /**
     * 获取单行数据
     *
     * @param string $sql
     * @param string $index
     * @param bool $is_master
     * @param int $limit
     * @param array $data
     *
     * @return array
     */
    public static function getOne(string $sql, $index = '', $is_master = false, $limit = 1, $data = [])
    {
        $sql .= !empty($limit) ? " LIMIT {$limit}" : "";
        $stmt = self::query($sql, $index, $is_master, $data);
        if ($stmt) {
            try {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                // 关闭游标，释放结果集
                $stmt->closeCursor();
                return $result;
            } catch (Exception $e) {
                self::errorLog($e, $sql);
            }
        }
        return null;
    }

    /**
     * 以新的$key_values更新mysql数据,
     *
     * @param array $key_values
     * @param string $where
     * @param string $table_name
     * @param string $index
     * @param array $whereData
     *
     * @return boolean
     */
    public static function update($key_values, $where, $table_name, $index = '', $whereData = [])
    {
        $sql = "UPDATE `{$table_name}` SET ";
        $data = [];

        foreach ($key_values as $k => $v) {
            if (!empty(self::$exceptConfig[$v])) {
                $sql .= "`{$k}` = {$v},";
            } else {
                $sql .= "`{$k}` = ?,";
                $data[] = $v ?? '';
            }
        }

        if (!empty($whereData)) {
            foreach ($whereData as $val) {
                $data[] = $val ?? '';
            }
        }

        $sql = substr($sql, 0, -1) . "  WHERE {$where}";
        return self::query($sql, $index, false, $data);
    }

    /**
     * 插入一条新的数据
     *
     * @param array $key_values
     * @param string $table_name
     * @param string $index
     *
     * @return boolean
     */
    public static function insert($key_values, $table_name, $index = '')
    {
        $items_sql = "";
        $values_sql = "";
        $data = [];

        foreach ($key_values as $k => $v) {
            $items_sql .= "`$k`,";
            if (!empty(self::$exceptConfig[$v])) {
                $values_sql .= "$v,";
            } else {
                $values_sql .= "?,";
                $data[] = $v ?? '';
            }
        }

        $sql = "INSERT INTO {$table_name} (" . substr($items_sql, 0, -1) . ") VALUES (" . substr($values_sql, 0, -1) . ")";
        return self::query($sql, $index, false, $data);
    }

    /**
     * 替换一条新的数据
     *
     * @param array $key_values
     * @param string $table_name
     * @param string $index
     *
     * @return boolean
     */
    public static function replace($key_values, $table_name, $index = '')
    {
        $items_sql = "";
        $values_sql = "";
        $data = [];

        foreach ($key_values as $k => $v) {
            $items_sql .= "`$k`,";
            if (!empty(self::$exceptConfig[$v])) {
                $values_sql .= "$v,";
            } else {
                $values_sql .= "?,";
                $data[] = $v ?? '';
            }
        }

        $sql = "REPLACE INTO {$table_name} (" . substr($items_sql, 0, -1) . ") VALUES (" . substr($values_sql, 0, -1) . ")";
        return self::query($sql, $index, false, $data);
    }

    /**
     * 插入一条新的数据
     *
     * @param array $key_values
     * @param string $table_name
     * @param string $index
     *
     * @return boolean
     */
    public static function insertPatch($key_values, $table_name, $index = '')
    {
        $items_sql = "";
        $values_patch_sql = "";
        $columnStatus = false;
        $data = [];

        foreach ($key_values as $val) {
            $values_sql = "";
            foreach ($val as $k => $v) {
                if (!$columnStatus) {
                    $items_sql .= "`$k`,";
                }
                if (!empty(self::$exceptConfig[$v])) {
                    $values_sql .= "$v,";
                } else {
                    $values_sql .= "?,";
                    $data[] = $v ?? '';
                }
            }
            $columnStatus = true;
            $values_patch_sql .= "(" . substr($values_sql, 0, -1) . "),";
        }

        $sql = "INSERT INTO {$table_name} (" . substr($items_sql, 0, -1) . ") VALUES " . substr($values_patch_sql, 0, -1);
        return self::query($sql, $index, false, $data);
    }

    /**
     * 插入一条新的数据，已存在的情况下进行覆盖
     *
     * @param array $key_values
     * @param string $table_name
     * @param string $index
     *
     * @return boolean
     */
    public static function duplicate($key_values, $table_name, $index = '')
    {
        $items_sql = "";
        $values_sql = "";
        $update_sql = "";
        $data = [];

        foreach ($key_values as $k => $v) {
            $items_sql .= "`$k`,";
            $values_sql .= "?,";
            $data[] = $v ?? '';
        }

        foreach ($key_values as $k => $v) {
            $update_sql .= "`$k`=?,";
            $data[] = $v ?? '';
        }

        $sql = "INSERT INTO {$table_name} (" . substr($items_sql, 0, -1) . ") VALUES (" . substr($values_sql, 0, -1) . ") 
                ON DUPLICATE KEY UPDATE " . substr($update_sql, 0, -1);
        return self::query($sql, $index, false, $data);
    }

    /**
     * 取得一个表的初始数组,包括所有表字段及默认值，无默认值为''
     *
     * @param string $table_name
     * @param string $index
     *
     * @return array $result 表结构数组
     */
    public static function getStructure($table_name, $index = '')
    {
        $rt = self::getAll("DESC `{$table_name}`", $index);
        $result = [];

        foreach ($rt as $v) {
            $result[$v['Field']] = $v['Default'] === null ? '' : $v['Default'];
        }

        return $result;
    }

    /**
     * 检查连接是否可用
     *
     * @param PDO $dbconn 数据库连接
     *
     * @return Boolean
     */
    public static function ping($dbconn)
    {
        try {
            if (empty($dbconn)) {
                return false;
            }
            if (PHP_SAPI !== 'cli') {
                return true;
            }
            $serverInfo = $dbconn->getAttribute(PDO::ATTR_SERVER_INFO);
            if (false === $serverInfo || strpos($serverInfo, 'MySQL server has gone away') !== false) {
                return false;
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * 记录日志
     *
     * @param Exception $e
     * @param string $sql
     * @param bool $exit
     */
    public static function errorLog($e, $sql = '', $exit = true)
    {
        $msg = $e->getMessage();
        $code = $e->getCode();
        $trace = $e->getTraceAsString();
        $message = "MySQL:" . self::$current_link_ip . " ErrorCode:" . $code . "\r\nErrorMessage:" . $msg . "\r\nTrace:" . $trace . "\r\n";

        if ($sql) {
            $message .= "SQL: " . preg_replace("/\s+/", ' ', $sql) . "\r\n";
        }

        cutil_php_log($message, self::$log_file);

        if (strpos($msg, '1205 Lock') !== false || strpos($msg, '1213 Deadlock') !== false ) {
            self::recordDeadlock();
        }

        if (!$exit) {
            return;
        }

        // 错误提示
        if (DEBUG_LEVEL) {
            exit('<pre>' . $code . "\r\n" . $msg . "\r\n" . $trace . '</pre>');
        } else {
            header('HTTP/1.1 500 Service Unavailable', true, 500);
            json_print(['errcode' => '503', 'errmsg' => 'DB Service Unavailable']);
        }
    }

    /**
     * 记录死锁日志
     */
    public static function recordDeadlock()
    {
        $sql = "SHOW ENGINE INNODB STATUS";
        $res = self::getAll($sql);
        cutil_php_log("错误sql语句:{$sql}, 错误信息:" . var_export($res, true), self::$log_file);
        $sql = "select * from information_schema.processlist where command != ?";
        $res = self::getAll($sql, '', false, $data = ['Sleep']);
        cutil_php_log("错误sql语句:{$sql}, 错误信息:" . var_export($res, true), self::$log_file);
        $sql = "SHOW OPEN TABLES WHERE In_use > ?";
        $res = self::getAll($sql, '', false, $data = [0]);
        cutil_php_log("错误sql语句:{$sql}, 错误信息:" . var_export($res, true), self::$log_file);
        $sql = "SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS";
        $res = self::getAll($sql);
        cutil_php_log("错误sql语句:{$sql}, 错误信息:" . var_export($res, true), self::$log_file);
        $sql = "SELECT * FROM INFORMATION_SCHEMA.INNODB_TRX";
        $res = self::getAll($sql);
        cutil_php_log("错误sql语句:{$sql}, 错误信息:" . var_export($res, true), self::$log_file);
        $sql = "SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS";
        $res = self::getAll($sql);
        cutil_php_log("错误sql语句:{$sql}, 错误信息:" . var_export($res, true), self::$log_file);
    }

    /**
     * 获取当前的IP
     *
     * @return string
     */
    public static function getCurrentLinkIp()
    {
        return self::$current_link_ip;
    }

    /**
     * 销毁类时关闭数据库
     *
     * @return void
     */
    public function __destruct()
    {
        self::closeMysql();
    }
}
