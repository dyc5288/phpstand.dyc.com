<?php
/**
 * Description: 函数库
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: lib_function.php 173228 2022-04-13 07:32:38Z huyf $
 */
!defined('IN_INIT') && exit('Access Denied');

/**
 * 自动转义
 *
 * @param array $array
 *
 * @return void
 */
function auto_addslashes(&$array)
{
    if ($array) {
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                /* key值处理 */
                $tmp_key = addslashes($key);
                $array[$tmp_key] = addslashes($value);

                if ($tmp_key != $key) {
                    /* 删除原生元素 */
                    unset($array[$key]);
                }
            } else {
                auto_addslashes($array[$key]);
            }
        }
    }
}

/**
 * 是否序列化
 *
 * @param string $string
 *
 * @return boolean
 */
function is_serialize($string)
{
    $string = trim($string);

    if (preg_match('/^s:[0-9]+:.*;$/s', $string)) {
        return true;
    }

    return false;
}

/**
 * 设置string存储 cache_set_string
 *
 * @param string $preifx
 * @param string $key
 * @param $value
 * @param $expire
 * @throws Exception
 *
 * @return boolean
 */
function SM($preifx, $key, $value, $expire = 0)
{
    if (!isset($GLOBALS["DATABASE"]['CACHED_PREFIX'][$preifx])) {
        T(11100002);
    }
    return lib_redis::set($GLOBALS["DATABASE"]['CACHED_PREFIX'][$preifx], $key, $value, $expire);
}

/**
 * 获取string存储 cache_get_string
 *
 * @param string $preifx
 * @param string $key
 * @throws Exception
 *
 * @return array
 */
function GM($preifx, $key)
{
    if (!isset($GLOBALS["DATABASE"]['CACHED_PREFIX'][$preifx])) {
        T(11100002);
    }
    return lib_redis::get($GLOBALS["DATABASE"]['CACHED_PREFIX'][$preifx], $key);
}

/**
 * 删除string存储
 *
 * @param string $preifx
 * @param string $key
 * @throws Exception
 *
 * @return void
 */
function DM($preifx, $key)
{
    if (!isset($GLOBALS["DATABASE"]['CACHED_PREFIX'][$preifx])) {
        T(11100002);
    }
    lib_redis::del($GLOBALS["DATABASE"]['CACHED_PREFIX'][$preifx], $key);
}

/**
 * 向指定网址CURL请求)
 *
 * @param string $url
 * @param string $method GET/POST
 * @param string $postdata
 * @param int    $connet_timeout
 * @param int    $timeout
 * @param array  $otherParams
 *
 * @return array
 * @throws Exception
 */
function curl(string $url, $method = 'GET', $postdata = '', $connet_timeout = 1, $timeout = 3, $otherParams = [])
{
    $return = [];
    $ci = curl_init();
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $connet_timeout); //连接超时时间
    curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);        //总的超时时间

    if (isset($otherParams['SSLVERSION'])) {
        curl_setopt($ci, CURLOPT_SSLVERSION, $otherParams['SSLVERSION']); //CURL_SSLVERSION_TLSv1
    }

    if (isset($otherParams['USERAGENT'])) {
        curl_setopt($ci, CURLOPT_USERAGENT, $otherParams['USERAGENT']);
    }

    if (isset($otherParams['REFERER'])) {
        curl_setopt($ci, CURLOPT_REFERER, $otherParams['REFERER']);
    }

    if (isset($otherParams['COOKIE'])) {
        curl_setopt($ci, CURLOPT_COOKIEFILE, $otherParams['COOKIE']);
        curl_setopt($ci, CURLOPT_COOKIEJAR, $otherParams['COOKIE']);
    }

    if (strtoupper($method) === 'POST') {
        curl_setopt($ci, CURLOPT_POST, true);
        if (is_array($postdata) || is_object($postdata)) {
            curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postdata));
        } else {
            curl_setopt($ci, CURLOPT_POSTFIELDS, $postdata);
        }
    }

    curl_setopt($ci, CURLOPT_URL, $url);

    if (isset($otherParams['host']) && !empty($otherParams['host'])) {
        curl_setopt($ci, CURLOPT_HTTPHEADER, ["Host: {$otherParams['host']}"]);
    }

    if (isset($otherParams['header']) && !empty($otherParams['header'])) {
        curl_setopt($ci, CURLOPT_HTTPHEADER, $otherParams['header']);
    }

    $ret = curl_exec($ci);
    $return['code'] = curl_getinfo($ci, CURLINFO_HTTP_CODE);

    if ($ret === false) {
        cutil_php_log('Curl error: ' . curl_error($ci), 'curl');
        T(21100008);
    }

    curl_close($ci);
    $return['data'] = trim($ret);

    return $return;
}

/**
 * 获得当前的Url
 * @param $isPrefix
 * @param $httpType
 * @return string
 */
function get_cururl($isPrefix = false, $httpType = false)
{
    if (!empty($_SERVER["REQUEST_URI"])) {
        $scriptName = $_SERVER["REQUEST_URI"];
        $nowurl = $scriptName;
    } else {
        $scriptName = $_SERVER["PHP_SELF"];
        $nowurl = empty($_SERVER["QUERY_STRING"]) ? $scriptName : $scriptName . "?" . $_SERVER["QUERY_STRING"];
    }

    $isHttps = false;
    if (!$httpType) {
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
            $isHttps = true;
        }
    } else if (strtolower($httpType) === 'https') {
        $isHttps = true;
    }

    $http_method = $isHttps ? 'https' : 'http';
    $host = getServerAddr();
    $port = '';
    if (parse_url($host, PHP_URL_PORT) === null) {
        if (!$isHttps) {
            $httpPort = getHttpPort();
            if ((int)$httpPort !== 80) {
                $port = $httpPort;
            }
        }
        if (!empty($port)) {
            $port = ':' . $port;
        } else {
            $port = '';
        }
    }

    $base_url = ($httpType ?: $http_method) . '://' . $host . $port . ($isPrefix ? '' : $nowurl);
    return $base_url;
}

/**
 * 获取表单请求参数
 *
 * @param string $field 表单字段
 * @param string $method
 * @param mixed  $default_value 默认值
 * @param mixed  $dataType 默认值
 *
 * @return mixed
 */
function request($field, $method = 'post', $default_value = '', $dataType = 'char')
{
    return lib_request::item($field, $default_value, $method, $dataType);
}

/**
 * 进行自定义Base64解密
 *
 * @param string $str
 * @return string
 */
function stringDecode($str)
{
    return str_replace(array(",", ":", '.'), array("+", '=', '/'), $str);
}

/**
 * 进行自定义Base64加密
 *
 * @param string $str
 *
 * @return string
 */
function stringEncode($str)
{
    return str_replace(array("+", "=", '/'), array(",", ':', '.'), $str);
}

/**
 * 进行自定义Base64解密(Base64Ext)
 *
 * @param string $str
 * @param bool   $checkBase64 检查是否是base64数据
 * @return string
 */
function Base64DeExt($str, $checkBase64 = false)
{
    if ($checkBase64) {
        $baseStr = Base64DeExt($str);
        if (base64_encode(base64_decode($str)) != $str && Base64EnExt($baseStr) != $str) {
            cutil_php_log("str:{$str}", 'base64_error'); // 记录日志推动调用方改动
            return strGbkToUtf8($str);
        }
    }

    $result = base64_decode(stringDecode($str));
    return strGbkToUtf8($result);
}

/**
 * 进行自定义Base64加密
 *
 * @param string $str
 *
 * @return string
 */
function Base64EnExt($str)
{
    return stringEncode(base64_encode($str));
}

/**
 * 根据请求的参数输出 json 格式的数据
 *
 * @param mixed $data
 *
 * @return null
 */
function json_print($data)
{
    if (!empty($GLOBALS['HeaderContentType'])) {
        header("Content-type: {$GLOBALS['HeaderContentType']};charset=utf-8", true);
    } else {
        header('Content-type: application/json;charset=utf-8', true);
    }
    exit(json_encode($data));
}

/**
 * 输出js使用的
 *
 * @param $val
 *
 * @return mixed|string
 */
function ajax_escape($val)
{
    //$val = iconv("GBK", "UTF-8", $val);
    $val = str_replace("\\", "\\\\", $val);
    $val = str_replace("\r", "\\r", $val);
    $val = str_replace("\n", "\\n", $val);
    $val = str_replace("'", "\\'", $val);
    $val = str_replace('"', '\\"', $val);
    //$val = iconv("UTF-8", "GBK", $val);
    return $val;
}

/**
 * 输出js使用的
 *
 * @param $value
 *
 * @return mixed|string
 */
function ajax_to_js($value)
{
    // 802.1x返回进行特殊处理
    $callfrom = request('callfrom', 'request');
    $type = gettype($value);
    if ($type == "boolean") {
        if ($callfrom == '802.1x') {
            return ($value) ? "\"1\"" : "\"0\"";
        }
        return ($value) ? "Boolean(true)" : "Boolean(false)";
    } else if ($type == "integer") {
        if (isset($_SERVER['HTTP_USER_AGENT']) &&
            strpos($_SERVER['HTTP_USER_AGENT'], "MAC") !== false) {
            return "'" . $value . "'";
        } else {
            return "\"" . $value . "\"";
        }
    } else if ($type == "double") {
        return "parseFloat($value)";
    } else if ($type == "array" || $type == "object") {
        $s = "{ ";
        if ($type == "object") {
            $value = get_object_vars($value);
        }
        if ($type == "array") {
            $s .= "\"length\": " . count($value) . (count($value) > 0 ? "," : "");
        }
        foreach ($value as $k => $v) {
            $esc_key = ajax_escape($k);
            if (is_numeric($k)) {
                $s .= "$k: " . ajax_to_js($v) . ", ";
            } else {
                $s .= "\"$esc_key\": " . ajax_to_js($v) . ", ";
            }
        }
        if (count($value)) {
            $s = substr($s, 0, -2);
        }
        return $s . " }";
    } else {
        $esc_val = ajax_escape($value);
        $s = "\"$esc_val\"";
        return $s;
    }
}

/**
 * 输出js使用的
 *
 * @param $val
 *
 * @return mixed|string
 */
function php_to_js($v)
{
    if (!empty($GLOBALS['HeaderContentType'])) {
        header("Content-type: {$GLOBALS['HeaderContentType']};charset=gbk", true);
    } else {
        header('Content-type: text/javascript; charset=gbk', true);
    }
    $str = "__res = " . trim(ajax_to_js($v));
    $str = utf8ToGbk($str);
    return $str;
}

/**
 * 路径是否存在
 *
 * @param $path
 *
 * @return bool
 */
function path_exists($path)
{
    $pathinfo = pathinfo($path . '/tmp.txt');
    if (!empty ($pathinfo ['dirname'])) {
        if (file_exists($pathinfo ['dirname']) === false) {
            if (mkdir($pathinfo ['dirname'], 0777, true) === false) {
                return false;
            }
        }
    }
    return $path;
}

/**
 * 获取字典值
 *
 * @param string $type 对应字典表中的字段
 * @param string $itemname 对应字典表中的字段
 * @param string $defval 查询失败情况下默认返回的字符
 *
 * @return string 字典表中的键值
 */
function cutil_dict_get($type, $itemname, $defval = '')
{
    $get_data = DictModel::getOneItem($type, $itemname);
    if (empty($get_data)) {
        return $defval;
    }
    if (array_key_exists('ItemValue', $get_data)) {
        return $get_data['ItemValue'];
    } else {
        return $get_data;
    }
}

/**
 * 改变字段名
 *
 * @param array  $data
 * @param string $culumn_name
 * @param string $default_value
 */
function get_column(&$data, $culumn_name, $default_value = '')
{
    if (isset($data[$culumn_name])) {
        return $data[$culumn_name];
    }

    return $default_value;
}

/**
 * 引入函数库
 *
 * @param $func
 */
function include_function($func)
{
    include_once PATH_LIBRARY . "/function/{$func}.func.php";
}

/**
 * 引入函数库
 *
 * @param $config
 */
function include_config($config)
{
    include_once PATH_CONFIG . "/inc_{$config}.php";
}

// function目录的公共方法引入
include PATH_LIBRARY . "/function/lang.func.php";
include PATH_LIBRARY . "/function/route.func.php";
include PATH_LIBRARY . "/function/string.func.php";
include PATH_LIBRARY . "/function/ini.func.php";
include PATH_LIBRARY . "/function/log.func.php";
include PATH_LIBRARY . "/function/exec.func.php";
include PATH_LIBRARY . "/function/net.func.php";
include PATH_LIBRARY . "/function/header.func.php";
include PATH_LIBRARY . "/function/crypt.func.php";
include PATH_LIBRARY . "/function/socket.func.php";
include PATH_LIBRARY . "/function/xml.func.php";
include PATH_LIBRARY . "/function/time.func.php";
include PATH_LIBRARY . "/function/array.func.php";
include PATH_LIBRARY . "/function/pub.func.php";
include PATH_LIBRARY . "/function/cache.func.php";
