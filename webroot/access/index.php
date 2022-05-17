<?php
/**
 * Description: 入口
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: index.php 165504 2021-12-22 06:58:57Z duanyc $
 */

header('Content-Type: text/html; charset=utf-8');
define('PATH_CUR', __DIR__);
require PATH_CUR . '/../../init.php';

/* 防止页面cache */
if (!headers_sent()) {
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
}

// 数据处理和路由
lib_request::init();

// 接口常用参数
define('API_VERSION', request('api_version', 'request'));

/********************** 兼容老版本开始 ***********************/
if (API_VERSION < '1.0') {
    hlp_compatible::parseSource();
}
/********************** 兼容老版本结束 ***********************/
define('OS_TYPE', request('os_type', 'request', hlp_compatible::getOsType()));
define('SOURCE_TYPE', request('source_type', 'request'));
define('CODE_TYPE', request('code_type', 'request'));
define('IS_MOBILE', in_array(OS_TYPE, [OSTYPE_IOS, OSTYPE_ANDROID])); // 是否移动端
define('IS_CLIENT', hlp_compatible::getIsClient()); // 是否客户端调用

/* 控制器 */
$GLOBALS['CT'] = request('ct', 'request', 'index');
$GLOBALS['AC'] = request('ac', 'request', 'index');

/* 当前网址 */
$cururl = get_cururl();
define('URL_CURRENT', $cururl);

/* 当前语言 */
$lang = request('local_lguage_set', 'request', 'zh');
$langDir = $GLOBALS['CONFIG']['LANG'][$lang] ?? 'zh_CN';
define('LANG', $langDir);

/********************** 兼容老版本开始 ***********************/
if (API_VERSION < '1.0') {
    hlp_compatible::parseControl();
}
/********************** 兼容老版本结束 ***********************/

/* 控制器路由 */
execute_ctl($GLOBALS['CT'], $GLOBALS['AC']);
