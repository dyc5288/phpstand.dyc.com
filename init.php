<?php
/**
 * Description: 初始化
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: init.php 154440 2021-08-27 07:51:34Z duanyc $
 */

/* 严格开发模式 */
error_reporting(E_ALL);
define('IN_INIT', true);

/* 定义关键常量 */
define('PATH_ROOT', strtr(__FILE__, ['\\' => '/', '/init.php' => '', '\init.php' => '']));

/* 加载常量 */
require PATH_ROOT . '/config/inc_constants.php';

/* 加载全局配置文件 */
require PATH_ROOT . '/config/inc_config.php';

/* 数据库配置 */
require PATH_ROOT . '/config/inc_database.php';

/* 设置时区 */
date_default_timezone_set('Asia/Shanghai');

/* 加载函数库 */
require PATH_LIBRARY . '/lib_function.php';

/* composer加载 */
require PATH_ROOT . '/vendor/autoload.php';

/* 错误日志 */
$suffix = php_sapi_name() . '-' . date('Ymd') . '.log';
ini_set('error_log', PATH_LOG . '/access_php_error_' . $suffix);
ini_set('log_errors', '1');

/* 错误控制 */
if (DEBUG_LEVEL) {
    ini_set('display_errors', 'On');
    set_error_handler('debug_error_handler', E_ALL);
} else {
    ini_set('display_errors', 'Off');
}

/* 自动转义 */
auto_addslashes($_POST);
auto_addslashes($_GET);
auto_addslashes($_COOKIE);
auto_addslashes($_FILES);
auto_addslashes($_REQUEST);
