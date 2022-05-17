<?php

/**
 * Description: http头及Server相关函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: header.func.php 175039 2022-05-05 07:33:41Z duanyc $
 */

/**
 * 获取浏览器以及版本
 *
 * @return string 获取服务器自己的IP地址 如: 192.168.46.142
 */
function getServerAddr()
{
    //当配置负载器后 LVS_HTTP_HOST 就会存在, 值从 HTTP_HOST 中取得, 这里要注意有可能会被漏洞扫描到
    if (isset($_SERVER['LVS_HTTP_HOST'])
        && filter_var($_SERVER['LVS_HTTP_HOST'], FILTER_VALIDATE_IP)
    ) {
        return $_SERVER['LVS_HTTP_HOST'];
    }

    if (isset($_SERVER['HTTP_X_SERVER_ADDR'])) {
        return $_SERVER['HTTP_X_SERVER_ADDR'];
    }

    return $_SERVER['HTTP_HOST'];
}

/**
 * 获取浏览器以及版本
 *
 * @author zhangkb 20120904
 * @return string 浏览器名称以及版本
 */
function getBrower()
{
    $Brower = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : null;
    if (strpos($Brower, "MSIE 10.0")) {
        return "IE10.0";
    } elseif (strpos($Brower, "MSIE 9.0")) {
        return "IE9.0";
    } elseif (strpos($Brower, "MSIE 8.0")) {
        return "IE8.0";
    } else if (strpos($Brower, "MSIE 7.0")) {
        return "IE7.0";
    } else if (strpos($Brower, "MSIE 6.0")) {
        return "IE6.0";
    } else if (strpos($Brower, "Firefox/")) {
        return "Firefox";
    } else if (strpos($Brower, "Chrome")) {
        return "Google Chrome";
    } else if (strpos($Brower, "Safari")) {
        return "Safari";
    } else if (strpos($Brower, "NetCaptor")) {
        return "NetCaptor";
    } else if (strpos($Brower, "Netscape")) {
        return "Netscape";
    } else if (strpos($Brower, "Lynx")) {
        return "Lynx";
    } else if (strpos($Brower, "Konqueror")) {
        return "Konqueror";
    } else if (strpos($Brower, "Mozilla")) {
        return "Mozilla";
    } else if (strpos($Brower, "Opera")) {
        return "Opera";
    } else {
        return $Brower;
    }
}

/**
 * 获取远程服务器IP
 *
 * @return string 192.168.1.2
 */
function getRemoteAddress()
{
    $ip = null;
    if (isset($HTTP_SERVER_VARS)) {
        if (@$HTTP_SERVER_VARS ["HTTP_X_FORWARDED_FOR"]) {
            $ip = $HTTP_SERVER_VARS ["HTTP_X_FORWARDED_FOR"];
        } elseif (@$HTTP_SERVER_VARS ["HTTP_CLIENT_IP"]) {
            $ip = $HTTP_SERVER_VARS ["HTTP_CLIENT_IP"];
        } elseif (@$HTTP_SERVER_VARS ["REMOTE_ADDR"]) {
            $ip = $HTTP_SERVER_VARS ["REMOTE_ADDR"];
        }
    }

    if ($ip === null) {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "0.0.0.0";
        }
    }
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    } else {
        return "";
    }
}


/**
 * 根据桥模块源IP端口和目的IP端口构造的IPV6地址计算出源IPV4地址
 *
 * @param $BridgeIpv6IP
 *
 * @return string 获取源IPV4地址 如: 192.168.46.142
 *
 */

function getSourceIpv4FromBridgeIpv6($BridgeIpv6IP)
{
    $ipv6arr = explode(':', $BridgeIpv6IP);
    $ipv4IP = hexdecIpv4IP($ipv6arr[2]) . "." . hexdecIpv4IP($ipv6arr[3]);
    return $ipv4IP;
}

/**
 * 根据桥模块IPV6地址规定的小段计算出对应的IPV4格式的一半字符串
 *
 * @param $string
 *
 * @return string 获取源IPV4地址 如: 192.168
 *
 */
function hexdecIpv4IP($string)
{
    $ipv4 = "";
    $length = strlen($string);
    $num = 4 - $length;
    if ($num > 0 && $num < 4) {  //当IPV6的一段长度小于4位时需要在前面补0补齐4位
        for ($i = 0; $i < $num; $i++) {
            $string = "0" . $string;
        }
    }
    $ipv4 .= hexdec(substr($string, 0, 2));
    $ipv4 .= "." . hexdec(substr($string, 2, 2));
    return $ipv4;
}

/**
 * url跳转
 *
 * @param $url
 */
function gotoUrl($url)
{
    header("Location: {$url}");
    exit();
}
