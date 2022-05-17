<?php

/**
 * Description: 基本类
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: BaseModel.php 174776 2022-04-28 09:18:26Z duanyc $
 */
class BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    public const TABLE_NAME = '';
    public const PRIMARY_KEY = 'ID';
    protected static $columns = [
        'one' => 'ID',
        'all' => 'ID',
        '*'   => '*',
    ];
    protected static $data = []; // 预处理参数

    /**
     * 单条
     *
     * @param string $ID
     * @param string $Column
     *
     * @return mixed
     */
    public static function getOne($ID, $Column = '*')
    {
        if (empty($ID) || empty($Column)) {
            return false;
        }

        self::$data = [];
        $column = static::$columns[$Column];
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $primaryKey = static::PRIMARY_KEY;
        $where = "WHERE {$primaryKey} = " . self::setData($ID);
        $sql = "SELECT {$column} FROM {$table['name']} {$where} ";

        return lib_database::getOne($sql, $table['index'], false, 1, self::$data);
    }

    /**
     * 分布式下根据dasc的ID获取dasm的ID
     *
     * @param string $DAscID
     * @param string $OrigID
     *
     * @return mixed
     */
    public static function getPrimaryId($DAscID, $OrigID)
    {
        if (empty($DAscID) || empty($OrigID)) {
            return false;
        }

        self::$data = [];
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $primaryKey = static::PRIMARY_KEY;
        $where = "WHERE DAscID = " . self::setData($DAscID) . " AND OrigID = " . self::setData($OrigID);
        $sql = "SELECT {$primaryKey} FROM {$table['name']} {$where} ";
        $result = lib_database::getOne($sql, $table['index'], false, 1, self::$data);
        return $result[$primaryKey] ?? '';
    }

    /**
     * 根据条件获取单条
     *
     * @param array $cond
     * @param mixed $order
     *
     * @return array
     */
    public static function getSingle($cond = [], $order = false)
    {
        self::$data = [];
        $where = static::getWhere($cond);
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $order = !empty($order) ? "ORDER BY {$order}" : "";

        $column = isset($cond['column']) ? static::$columns[$cond['column']] : '*';
        $sql = "SELECT {$column}  FROM {$table['name']} {$where} {$order} ";
        return lib_database::getOne($sql, $table['index'], false, 1, self::$data);
    }

    /**
     * 获取自增ID
     * @return array|bool
     */
    public static function getAutoIncrement()
    {
        self::$data = [];
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $sql = "show table status where Name=" . self::setData($table['name']);
        $result = lib_database::getOne($sql, $table['index'], false, 0, self::$data);

        if ($result) {
            return $result['Auto_increment'];
        }

        return false;
    }

    /**
     * 插入
     *
     * @param array $key_values
     *
     * @return int
     */
    public static function insert($key_values = [])
    {
        if (empty($key_values)) {
            return false;
        }

        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $result = lib_database::insert($key_values, $table['name'], $table['index']);
        cutil_php_log(json_encode(['model_insert', $table, self::$data, $key_values]), "model_{$table['name']}");

        if ($result) {
            return lib_database::insertId();
        }

        return $result;
    }

    /**
     * 批量插入
     *
     * @param array $key_values
     *
     * @return int
     */
    public static function insertPatch($key_values = [])
    {
        if (empty($key_values)) {
            return false;
        }

        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $result = lib_database::insertPatch($key_values, $table['name'], $table['index']);
        cutil_php_log(json_encode(['model_insertPatch', $table, $key_values]), "model_{$table['name']}");
        return $result;
    }

    /**
     * 存储过程调用
     *
     * @param string $functionName
     *
     * @return int
     */
    public static function callFunction($functionName)
    {
        if (empty($functionName)) {
            return false;
        }

        $sql = "call {$functionName}()";
        $result = lib_database::query($sql);
        return $result;
    }

    /**
     * 修改
     *
     * @param string $ID
     * @param array $key_values
     *
     * @return boolean
     */
    public static function update($ID, $key_values = [])
    {
        if (empty($ID) || empty($key_values)) {
            return false;
        }

        self::$data = [];
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $primaryKey = static::PRIMARY_KEY;
        $where = " {$primaryKey} = " . self::setData($ID);
        cutil_php_log(json_encode(['model_update', $table, $where, self::$data, $key_values]), "model_{$table['name']}");
        return lib_database::update($key_values, $where, $table['name'], $table['index'], self::$data);
    }

    /**
     * 批量修改
     *
     * @param array $cond
     * @param array $key_values
     *
     * @return boolean
     */
    public static function updatePatch($cond, $key_values = [])
    {
        if (empty($cond) || empty($key_values)) {
            return false;
        }

        self::$data = [];
        $where = substr(static::getWhere($cond), 6);
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        cutil_php_log(json_encode(['model_updatePatch', $table, $where, self::$data, $key_values]), "model_{$table['name']}");
        return lib_database::update($key_values, $where, $table['name'], $table['index'], self::$data);
    }

    /**
     * 列表
     *
     * @param array $cond
     * @param mixed $order
     * @param int $start
     * @param int $limit
     *
     * @return array
     */
    public static function getList($cond = [], $order = false, $start = 0, $limit = 1000)
    {
        self::$data = [];
        $where = static::getWhere($cond);
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $order = !empty($order) ? "ORDER BY {$order}" : "";
        $groupBy = isset($cond['groupby']) ? "GROUP BY {$cond['groupby']}" : "";
        $column = isset($cond['column'], static::$columns[$cond['column']]) ? static::$columns[$cond['column']] : '*';

        $sql = "SELECT {$column}  FROM {$table['name']} {$where} {$groupBy} {$order} LIMIT {$start}, {$limit}";
        return lib_database::getAll($sql, $table['index'], false, self::$data);
    }

    /**
     * 获取数量
     *
     * @param array $cond
     *
     * @return mixed
     */
    public static function getCount($cond)
    {
        self::$data = [];
        $where = static::getWhere($cond);
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);

        $sql = "SELECT COUNT(*) as count FROM {$table['name']} {$where}";
        $result = lib_database::getOne($sql, $table['index'], false, 1, self::$data);

        if ($result) {
            return $result['count'];
        }

        return false;
    }

    /**
     * 删除
     *
     * @param array $cond
     *
     * @return mixed
     */
    public static function delete(array $cond)
    {
        self::$data = [];
        $where = static::getWhere($cond);
        $table = hlp_common::getSplitTable(null, static::TABLE_NAME);
        $sql = "DELETE FROM {$table['name']} {$where}";
        cutil_php_log(json_encode(['model_delete', $table, $sql, self::$data, $cond]), "model_{$table['name']}");
        return lib_database::query($sql, $table['index'], false, self::$data);
    }

    /**
     * 插入失败时，重试几次（比如遇到锁等待）
     * ps：一般不建议使用！！！
     * @param array $params
     * @param int $repeatTimes
     * @return bool
     */
    public static function repeatInsertToDb(array $params, $repeatTimes = 5): bool
    {
        try {
            $table = \hlp_common::getSplitTable(null, static::TABLE_NAME);
            $result = \lib_database::insert($params, $table['name'], $table['index']);
            cutil_php_log(json_encode(['model_repeatInsert', $table, $params]), "model_{$table['name']}");
            if (!$result) {
                T(10003);
            }
        } catch (Exception $e) {
            // 重试完设定的次数还是失败则记录日志，放弃重试
            if (1 === $repeatTimes) {
                \lib_database::errorLog($e,static::TABLE_NAME);
                return false;
            }
            usleep(1000);
            --$repeatTimes;
            self::repeatInsertToDb($params, $repeatTimes);
        }
        return true;
    }

    /**
     * pdo方式预处理非数组数据组装
     *
     * @param $value
     */
    protected static function setData($value)
    {
        self::$data[] = $value;
        return '? ';
    }

    /**
     * pdo方式预处理数组数据组装
     *
     * @param $values
     */
    protected static function setArrayData(array $values)
    {
        $return = [];
        foreach ($values as $value) {
            self::$data[] = $value;
            $return[] = "?";
        }
        return implode(',', $return);
    }

    /**
     * 获取条件
     *
     * @param array $cond
     *
     * @return string
     */
    protected static function getWhere($cond = []):string
    {
        $where = "";

        if (isset($cond['ID'])) {
            $where .= "AND ID = ".self::setData($cond['ID']);
        }

        return !empty($where) ? "WHERE 1 = 1 {$where}" : "";
    }
}
