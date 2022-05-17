<?php

/**
 * Description: 常量配置
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: inc_constants.php 174952 2022-04-29 13:33:57Z duanyc $
 */
/* 访问控制 */
!defined('IN_INIT') && exit('Access Denied');

/* 调试模式，生产关闭 */
define('DEBUG_LEVEL', false);

/* 当前域名地址 */
define('URL', 'http://127.0.0.1');

/* 类库文件目录常量 */
define('PATH_LIBRARY', PATH_ROOT . '/library');

/* 数据库文件目录常量 */
define('PATH_MODEL', PATH_ROOT . '/model');

/* 帮助文件目录常量 */
define('PATH_HELPER', PATH_ROOT . '/helper');

/* 配置文件目录常量 */
define('PATH_CONFIG', PATH_ROOT . '/config');

/* 数据文件目录常量 */
define('PATH_DATA', PATH_ROOT . '/data');

/* 日志文件目录常量 */
define('PATH_LOG', '/tmp/logs');

/* 临时目录常量 */
define('PATH_TMP', '/tmp');

/* local目录常量 */
define('PATH_LOCAL', '/usr/local');

/* etc文件目录常量 */
define('PATH_ETC', (PHP_OS == 'WINNT') ? 'c:/etc/' : '/etc/');

/* asm文件目录常量 */
define('PATH_ASM', '/asm/');

/* usr文件目录常量 */
define('PATH_USR', '/usr/');

/* sys文件目录常量 */
define('PATH_SYS', '/sys/');

/* 原代码目录 */
define('PATH_HTML', '/var/www/html');

/* API文件目录常量 */
define('PATH_API', PATH_ROOT . '/api');

/* 业务模型层 */
define('PATH_SERVICE', PATH_ROOT . '/service');

/* 控制层目录 */
define('PATH_CONTROL', PATH_ROOT . '/control');

/* 入口当前目录 */
define('PATH_DIR', getcwd());

/* 请求时间 */
define('TIME', $_SERVER['REQUEST_TIME']);
