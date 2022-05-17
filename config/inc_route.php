<?php

/**
 * Description: 路由配置
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: inc_route.php 175039 2022-05-05 07:33:41Z duanyc $
 */

/* 访问控制 */
!defined('IN_INIT') && exit('Access Denied');
/* 初始化全局配置变量 */
$GLOBALS['ROUTE'] = array();

$GLOBALS['ROUTE']['routes'] = array(
    'access/([\d\.]+)/(ios|android|windows|mac|linux|server)/(object|json)/([a-z_]+)(/([a-z0-9_]+))?' =>
        'api_version=$1&os_type=$2&source_type=$3&ct=$4&ac=$6',
    '.*' => array('ct' => 'index', 'ac' => 'index')
);

/* 老方式的映射 key统一使用小写 */
$GLOBALS['ROUTE']['map'] = [
];

/* 老方式ajaxResult的兼容
{
    "status": "y",
    "info": "短信发送成功，请注意查收！"
}
*/
$GLOBALS['ROUTE']['json'] = [
];

/* 老方式msg/code格式的兼容
__res = {
    "length": 3,
    "status": "0",
    "code": "-1",
    "msg": "不在NAT地址列表！"
}
*/
$GLOBALS['ROUTE']['msgcode'] = [
];

/**
 * csrf白名单
 */
$GLOBALS['ROUTE']['csrfWhiteList'] = [
];