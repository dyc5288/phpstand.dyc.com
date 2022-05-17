<?php
/**
 * Description: 基本配置
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: inc_config.php 163009 2021-11-25 15:12:19Z duanyc $
 */
/* 初始化全局配置变量 */
$GLOBALS['CONFIG'] = array();

$cacheInfo = parse_ini_file(PATH_ETC . 'AsmCache.ini');

//redis配置
$GLOBALS['CONFIG']['redis_config'] = array();
$GLOBALS['CONFIG']['redis_config']['password'] = $cacheInfo['password'];
$GLOBALS['CONFIG']['redis'] = array();
$GLOBALS['CONFIG']['redis'][0] = array('host'=>$cacheInfo['host'], 'port'=>'6379', 'timeout'=>3);//主

//队列配置
$GLOBALS['CONFIG']['queue'] = array();
$GLOBALS['CONFIG']['queue']['AUTH_SUCCESS'] = array('name' => '认证成功处理', 'timeout' => 5, 'num' => 1);

//yar配置
$GLOBALS['CONFIG']['yar_server'] = array();

// 语言配置
$GLOBALS['CONFIG']['LANG'] = array(
    'zh' => 'zh_CN',
    'en' => 'en_US',
);
// 语言映射
$GLOBALS['CONFIG']['LANG_MAP'] = array(
    'zh_CN' => 'zh',
    'en_US' => 'en',
);
// 抛出异常后的数据暂存
$GLOBALS['CONFIG']['EXCEPTION_DATA'] = [];
// 多个原因数据保存
$GLOBALS['CONFIG']['ALL_MESSAGE'] = null;