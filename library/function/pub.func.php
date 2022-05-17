<?php
/**
 * Description: MQ 下发的相关函数.
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: pub.func.php 161940 2021-11-17 03:06:40Z duanyc $
 */

/**
 * 单表数据下发
 *
 * @param  $tableName string 表名
 * @param  $data      array 数据
 * @param  $action    string 动作类型：insert/delete
 *
 * @return int 命令执行是否成功，0为成功
 * @throws Exception
 */
function single_table_pub($tableName, $data, $action)
{
    auto_addslashes($data);
    $topic = '/report/' . $tableName;
    $AscID = get_ini_info(PATH_ETC . 'asc.ini', 'AscID');
    $report = [];
    $report['type'] = 'mysql';
    $report['AscID'] = $AscID ?: '11:11:11:11:11:11';
    $report['Message'] = [];
    $report['Message']['action'] = $action;
    $report['Message']['TableName'] = $tableName;
    $report['Message']['ID'] = null;
    $report['Message']['ItemNames'] = $action === 'insert' ? $data : '';
    $report['Message']['terms'] = '';
    return (int)pub_mq_jsonmsg($topic, $report, true);
}

/**
 * sql推送
 *
 * @param $string
 *
 * @return int
 * @throws Exception
 */
function sql_string_pub($string)
{
    $topic = '/cfg/config';
    $AscID = get_ini_info(PATH_ETC . 'asc.ini', 'AscID');
    $report = [];
    $report['type'] = 'mysql_sql';
    $report['AscID'] = $AscID ?: '11:11:11:11:11:11';
    $report['Message'] = [];
    $report['Message']['sql'] = $string;
    return (int)pub_mq_jsonmsg($topic, $report, true);
}

/**
 * 获取服务器类型 ASM|ASC|DASC|dblb
 * @return string
 * @throws Exception
 */
function get_server_type()
{
    if (file_exists(PATH_ETC . "devinfo.ini")) {
        $res = read_inifile(PATH_ETC . 'devinfo.ini');
        return $res['devtype'];
    }
    return "";
}

/**
 * 获取dasc状态
 * @return bool
 * @throws Exception
 */
function get_dasc_status()
{
    $c_path = PATH_ETC . "asc.ini";
    $d_path = PATH_ETC . "dasc_rsync.ini";
    $cfg = read_inifile($c_path);
    $dcfg = read_inifile($d_path);
    if (is_array($cfg) && $cfg['IsConnect'] == '1' && is_array($dcfg) && $dcfg['check_mode'] == '2' && $cfg['AscID'] != '') {
        return true;
    } else {
        return false;
    }
}

/**
 * 是否通知下发到dasm，是则获取dasm服务器的ip
 * @return bool
 */
function get_pub_dasm_ip()
{
    try {
        $devtype = get_ini_info(PATH_ETC . 'devinfo.ini', 'devtype');
        if ($devtype === 'dasc') {
            $ascinfo = read_inifile(PATH_ETC . 'asc.ini');
            if (empty($ascinfo['IsConnect'])) {
                return false;
            }
            return $ascinfo['ManageIp'];
        }
    } catch (Exception $e) {
        cutil_php_log($e->getMessage(), 'pub_mq_jsonmsg');
    }
    return false;
}

/**
 * 获取dasm的IP
 * @return array|string
 * @throws Exception
 */
function get_dasm_ip()
{
    if (get_dasc_status()) {
        $c_path = PATH_ETC . "asc.ini";
        $cfg = read_inifile($c_path);
        return $cfg['ManageIp'];
    }
    return "";
}
