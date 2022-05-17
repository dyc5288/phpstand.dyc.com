<?php

/**
 * Description: 语言相关
 * User: duanyc@infogo.com.cn
 * Date: 2021/07/5 15:53
 * Version: $Id: lang.func.php 157194 2021-09-18 01:26:42Z duanyc $
 */

/**
 * 异常退出
 * @param $msg
 */
function exception_exit($msg)
{
    cutil_php_log("lang config {$msg} error!", 'system');
    if (DEBUG_LEVEL === true) {
        printf("Message code not defined\n");
        debug_print_backtrace();
        exit();
    } else {
        header('HTTP/1.1 403 File Not Exist', true, 403);
        json_print(['errcode' => '403', 'errmsg' => 'Lang File Not Exist']);
    }
}

/**
 *  获取语言文件
 *
 * @param string $key
 * @param array $params
 *
 * @return string
 */
function L($key, $params = [])
{
    /* 获取语言包 */
    $lang = defined('LANG') ? LANG : 'zh_CN';
    $lang = isset($params['language']) ? $params['language'] : $lang;
    $prefix = substr($key, 0, 5);
    require_once PATH_CONFIG . '/inc_lang.php';
    if (!isset($GLOBALS['CONFIG']['LANG_FILE'][$prefix])) {
        exception_exit($key);
    }

    $file = $GLOBALS['CONFIG']['LANG_FILE'][$prefix];
    require_once PATH_DATA . '/lang/' . $lang . '/' . $file . '.php';
    if (!isset($GLOBALS['LANG'][$prefix][$key])) {
        exception_exit($key);
    }

    $message = $GLOBALS['LANG'][$prefix][$key];
    if ($params) {
        foreach ($params as $k => $v) {
            $message = str_replace("{" . $k . "}", $v, $message);
        }
    }

    return $message;
}

/**
 * 抛出异常错误
 *
 * @param int $code
 * @param $params
 * @param $data
 *
 * @return void
 * @throws Exception
 */
function T($code, $params = [], $data = [])
{
    $message = L($code, $params);
    cutil_php_log("errcode:{$code}, errmsg:{$message}", 'exception');
    $GLOBALS['CONFIG']['EXCEPTION_DATA'] = $data;
    throw new Exception($message, $code);
}

/**
 * 抛出用指定变量存储的异常信息
 *
 * @param int $code
 * @param string $name
 *
 * @return void
 * @throws Exception
 */
function TE($code, $name)
{
    throw new exception(serialize([$name => $code]));
}

/**
 * 反向获取错误信息（与TE对应使用）
 *
 * @param string $message
 *
 * @return array
 */
function ET($message, &$return)
{
    $data = @unserialize($message);

    if (!empty($data)) {
        foreach ($data as $err_name => $err_code) {
            $return['errorName'] = $err_name;
            $return['code'] = $err_code;
            $return['errorMsg'] = L($err_code);
            break;
        }
    }

    if (!isset($return['errorName'])) {
        $return['code'] = -1;
        $return['errorName'] = 'system';
        $return['errorMsg'] = $message;
    }

    return $return;
}