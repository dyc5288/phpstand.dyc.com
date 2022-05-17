<?php

/**
 * Description: 数据库配置
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: inc_database.php 147146 2021-06-17 02:04:51Z duanyc $
 */
/* 访问控制 */
!defined('IN_INIT') && exit('Access Denied');
$GLOBALS["DATABASE"] = array();

$dbHost = parse_ini_file(PATH_ETC . 'AsmDbHost.ini');
$dbInfo = parse_ini_file(PATH_ETC . 'AsmDb.ini');

/* 数据库 */
$GLOBALS['DATABASE']['databases']['db_user']    = $dbInfo['DbUser'];
$GLOBALS['DATABASE']['databases']['db_pass']    = $dbInfo['DbPass'];
$GLOBALS['DATABASE']['databases']['db_name']    = $dbInfo['DbName'];
$GLOBALS['DATABASE']['databases']['db_charset'] = 'utf-8';

// 分表分库
$GLOBALS['DATABASE']['section'] = array();
$GLOBALS['DATABASE']['section'][0] = array();
$GLOBALS['DATABASE']['section'][0]['table_name'] = array();
$GLOBALS['DATABASE']['section'][0]["table_range"] = 1;
$GLOBALS['DATABASE']['section'][0]["group_count"] = 1;
$GLOBALS['DATABASE']['section'][0]["ips"][0]["master"] = array("db_host" => "{$dbHost['DbHost']}:{$dbInfo['DbPort']}", "db_name" => $dbInfo['DbName']);  // 默认组
$GLOBALS['DATABASE']['section'][0]["ips"][0]["slave"][] = array("db_host" => "{$dbHost['DbHost']}:{$dbInfo['DbPort']}", "db_name" => $dbInfo['DbName']); // 默认组

/* cached Keys */
$GLOBALS["DATABASE"]['CACHED_PREFIX'] = array();

/* model层 */
$GLOBALS["DATABASE"]['CACHED_PREFIX']['D_100'] = 'DeviceModel::getOne';

/* service层 */
$GLOBALS["DATABASE"]['CACHED_PREFIX']['S_100'] = 'ASG_DeviceToken:';
