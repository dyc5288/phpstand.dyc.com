<?php

/**
 * Description: 网络方面函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: net.func.php 172396 2022-03-30 12:41:23Z huyf $
 */

/**
 * @param $startip
 * @param $endip
 *
 * @return bool
 * 比较IPv6地址,如果开始IP小于结束IP，返回真，否则返回假
 *
 */
function CompareIPv6($startip, $endip)
{
    $startip = inet_pton($startip);
    $startip = bin2hex($startip);
    $endip = inet_pton($endip);
    $endip = bin2hex($endip);
    if (strcmp($startip, $endip) > 0) {
        return false;
    }
    return true;
}

/**
 * 比较IP大小,开始IP小于结束IP返回真
 *
 * @param string $startip
 * @param string $endip
 * @param int $type 默认为0，默认返回    0/1;$type==1 时，开始IP小于结束IP时 返回 结束IP与开始IP的差值 ，
 *
 * @return int
 */
function CompareIP($startip, $endip, $type = 0)
{
    if (bindec(decbin(ip2long($startip))) <= bindec(decbin(ip2long($endip)))) {
        $v = $type != 0 ? bindec(decbin(ip2long($endip))) - bindec(decbin(ip2long($startip))) : 1;
        return $v;
    } else {
        return 0;
    }
}

/**
 * 判断ip是否在IP段范围内
 *
 * @param string $needip
 * @param string $startip
 * @param string $endip
 *
 * @return int 是：1 否：0
 */

function FindInIP($needip, $startip, $endip)
{
    $a = CompareIP($startip, $needip);
    $b = CompareIP($needip, $endip);
    if ($a && $b) {
        return 1;
    } else {
        return 0;
    }
}


/**
 * 判断字符串中是否包含有mac
 *
 * @param string $strTing
 *
 * @return bool 存在: mac (00:00:00:00:00:00) 不存在:false
 * @example IsStrHaveMac("========00.11.5b.62.b7.69=====") =>  "00.11.5b.62.b7.69"
 */
function IsStrHaveMac($strTing, &$macold = "")
{
    $aArray = [
        "/^[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}$/i", // 00:11:5b:62:b7:69
        "/^[A-F\d]{4}-[A-F\d]{4}-[A-F\d]{4}$/i", // 001d-7d27-985a
        "/^[A-F\d]{4}\.[A-F\d]{4}\.[A-F\d]{4}$/i", // 001d.7d27.985a
        "/^[A-F\d]{2}-[A-F\d]{2}-[A-F\d]{2}-[A-F\d]{2}-[A-F\d]{2}-[A-F\d]{2}$/i", // 00-11-5b-62-b7-69
        "/^[A-F\d]{2}\.[A-F\d]{2}\.[A-F\d]{2}\.[A-F\d]{2}\.[A-F\d]{2}\.[A-F\d]{2}$/i"  // 00.11.5b.62.b7.69
    ];
    foreach ($aArray as $item) {
        $serchMac = preg_match($item, $strTing, $result);
        if ($serchMac > 0) {
            $macold = $result [0];
            return macChanges($result [0]);
        }
    }
    return false;
}


/**
 * 两个交换机端口名是否是一个
 *
 * @param string $p1 端口短名或长名
 * @param string $p2 端口短名或长名
 *
 * @return true / false
 * @example  PortNameIsSame("FastEthernet0/3","Fa0/1") return true
 */
function PortNameIsSame($p1, $p2)
{
    if ($p1 == $p2) {
        return true;
    }
    $p11 = "";
    $p22 = "";
    $l = strlen($p1);
    for ($i = 0; $i < $l - 1; $i++) {
        $t = ord(substr($p1, $i, 1));
        if (($t >= ord("0")) && ($t <= ord("9"))) {
            $p11 = substr($p1, $i, $l - $i);
            break;
        }
    }

    $l = strlen($p2);
    for ($i = 0; $i < $l - 1; $i++) {
        $t = ord(substr($p2, $i, 1));
        if ($t >= ord("0") && $t <= ord("9")) {
            $p22 = substr($p2, $i, $l - $i);
            break;
        }
    }
    if (strtoupper($p11) == strtoupper($p22) && strtoupper(substr($p1, 0, 1)) == strtoupper(substr($p2, 0, 1))) {
        return true;
    } else {
        return false;
    }
}


/**
 * 转换mac格式
 *
 * @param string $strs 原始mac字符串
 * @param string $type ":/-/."
 * @param string $num 2 连续几个字母 AA:BB:CC:DD:EE:FF 或AABB-CCDD-EEFF
 *
 * @return string 默认:mac aa:aa:aa:aa:aa:aa
 *
 */
function macChanges($strs, $type = ':', $num = '2')
{
    $searchs = array(0 => ' ', 1 => "\n", 2 => '.', 3 => '-', 4 => ':', 5 => '：');
    foreach ($searchs as $search) {
        $strs = str_replace($search, '', $strs);
    }
    $strcount = strlen($strs);
    $str = '';
    for ($i = 0; $i < $strcount; $i++) {
        if ($i % $num == 0) {
            $str .= substr($strs, $i, $num);
            if ($i < ($strcount - $num)) {
                $str .= $type;
            }
        }
        $i = $i + 1;
    }
    // $str = substr($str,0,($strcount/$num-1+12));
    return $str;
}


/**
 * 判断是否存在IP
 *
 * @param string $str
 * @param int $type =1 时, 如果存在IP,则返回IP  -- zhangkb 20150430
 *
 * @return 1:存在 0:不存在, 或 IP
 *
 * */
function isHavIP($str, $type = 0)
{
    $ipsearch = "/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])){3}$/";
    $ip = preg_match($ipsearch, $str, $aIP);
    if ($ip && !$type) {
        return 1;
    } elseif ($ip && $type) {
        return $aIP[0];
    } else {
        return 0;
    }
}

/**
 * 切割交换机端口 方便排序
 *
 * @param string $name 交换机端口字符串 FastEthernet0/3
 *
 * @return array
 */
function split_port_name($name)
{
    $name_1 = substr($name, 0, 1);
    $index = strrpos($name, '/');
    $name_2 = substr($name, $index + 1);
    return [
        "char" => $name_1,
        "num"  => $name_2,
    ];
}


/**
 * 将交换机口子根据第一位来分组，方便交换机面板是否需要换行画面板
 *
 * @param string $name 交换机端口字符串 FastEthernet0/3
 *
 * @return array
 */
function group_port_name($name)
{

    $num = substr_count($name, '/');
    $index = strpos($name, '/');
    if ($num < 3) {
        $name_2 = substr($name, 0, $index);
    } else {
        $name_2 = substr($name, 0, ($index + 3));
    }
    //return $name_2;
    return trim(eregi_replace("[^0-9]", "", $name_2));
}


/**
 *判断当前ip是否在一个ip列表范围之内如果在返回true反之false
 * @example：VpnIpRange("192.168.46.100","192.168.46.1-192.168.46.200,172.17.47.1-172.17.47.200")
 *
 * @param string $ip IP地址 "192.168.46.100"
 * @param string $iprange IP地址范围 "192.168.46.1-192.168.46.200,172.17.47.1-172.17.47.200"
 *
 * @return Boolean
 */
function VpnIpRange($ip, $iprange, $split = ',')
{

    if ($ip != "" && $iprange != "") {
        $ipstr_arr1 = explode($split, $iprange);
        foreach ($ipstr_arr1 as $str1) {
            $iparr = explode("-", $str1);
            $start = ipToNum($iparr[0]);
            $stop = ipToNum($iparr[1]);
            $nowip = ipToNum($ip);
            if ($nowip >= $start && $nowip <= $stop) {
                return true;
            }
        }
    }
    return false;
}

/**
 *判断当前ip是否在一个ip列表范围之内如果在返回true反之false
 * @example：ipInIpRange("192.168.46.100","192.168.46.1-192.168.46.200,172.17.47.1-172.17.47.200")
 *
 * @param string $ip IP地址 "192.168.46.100"
 * @param string $iprange IP地址范围 "192.168.46.1-192.168.46.200,172.17.47.1-172.17.47.200"
 * @param string $split 分隔符
 *
 * @return Boolean
 */
function ipInIpRange($ip, $iprange, $split = ',')
{
    return VpnIpRange($ip, $iprange, $split);
}

/*
 * 判断mac是否是伪造
 * @param string $MAC
 * @return boolean true:是伪造 false:否
 */

function RelativelyIsFake($MAC)
{
    if (!strlen($MAC)) {
        return false;
    }
    $mac_arr = explode(":", $MAC);
    //yanzj 20180810 适配IPv6，直接将最后的00去掉
    if (strtolower($mac_arr [0]) != "ff") {
        return false;
    }
    return true;
}

/**
 * @param $ip
 * @param string $type
 *
 * @return bool|string
 * 根据ip伪造mac地址;
 * 默认伪造ipv4的mac地址，
 * 当type为6时，伪造IPv6的mac地址
 */
function MakeMacByIP($ip, $type = "4")
{
    $mac = '';
    if ($ip == '') {
        return false;
    }
    if ($type == 4) {
        $tmp = sscanf($ip, "%d.%d.%d.%d");
        $mac = sprintf("ff:%02X:%02X:%02X:%02X:00", $tmp[0], $tmp[1], $tmp[2], $tmp[3]);
    } elseif ($type == 6) {
        $IPv6Hex = bin2hex(inet_pton($ip));
        $FakeMAC = "FF:$IPv6Hex[18]$IPv6Hex[19]:$IPv6Hex[20]$IPv6Hex[21]:$IPv6Hex[26]$IPv6Hex[27]:$IPv6Hex[28]$IPv6Hex[29]:$IPv6Hex[30]$IPv6Hex[31]";
        $mac = $FakeMAC;
    }
    return $mac;
}

/**
 * 将IP字符串转为数字
 *
 * @param string $ip 192.168.1.1 转换为数字3456778
 *
 * @return string
 */
function ipToNum($ip)
{
 // IP转换为数字
    return sprintf("%u", ip2long($ip));
}

/**
 * @param $cStartIP
 * @param $cEndIP
 * @param $needStartIP
 * @param $needEndIP
 *
 * @return bool
 * 判断是否有交集
 */
function checkdiffIP($cStartIP, $cEndIP, $needStartIP, $needEndIP)
{
    //starip 在目标范围内
    if (FindInIP($cStartIP, $needStartIP, $needEndIP)) {
        return false;
    }
    //$cEndIP 在模板范围内
    if (FindInIP($cEndIP, $needStartIP, $needEndIP)) {
        return false;
    }
    //IP访问包含 目标范围
    if (ipToInt($cStartIP) < ipToInt($needStartIP) && ipToInt($cEndIP) > ipToInt($needEndIP)) {
        return false;
    }
    return true;
}

/**
 * IP地址转换为大整型数值
 *
 * @param $ip
 *
 * @return number
 */
function ipToInt($ip)
{
    return bindec(decbin(ip2long($ip)));
}

/**
 * ip形式转化为对象的网关 数字
 *
 * @param $mask
 *
 * @return string
 */
function netmaskto($mask)
{

    switch ($mask) {
        case "128.0.0.0":
            $bcmask = "1";
            break;
        case "192.0.0.0":
            $bcmask = "2";
            break;
        case "224.0.0.0":
            $bcmask = "3";
            break;
        case "240.0.0.0":
            $bcmask = "4";
            break;
        case "248.0.0.0":
            $bcmask = "5";
            break;
        case "252.0.0.0":
            $bcmask = "6";
            break;
        case "254.0.0.0":
            $bcmask = "7";
            break;
        case "255.0.0.0":
            $bcmask = "8";
            break;
        case "255.128.0.0":
            $bcmask = "9";
            break;
        case "255.192.0.0":
            $bcmask = "10";
            break;
        case "255.224.0.0":
            $bcmask = "11";
            break;
        case "255.240.0.0":
            $bcmask = "12";
            break;
        case "255.248.0.0":
            $bcmask = "13";
            break;
        case "255.252.0.0":
            $bcmask = "14";
            break;
        case "255.254.0.0":
            $bcmask = "15";
            break;
        case "255.255.0.0":
            $bcmask = "16";
            break;
        case "255.255.128.0":
            $bcmask = "17";
            break;
        case "255.255.192.0":
            $bcmask = "18";
            break;
        case "255.255.224.0":
            $bcmask = "19";
            break;
        case "255.255.240.0":
            $bcmask = "20";
            break;
        case "255.255.248.0":
            $bcmask = "21";
            break;
        case "255.255.252.0":
            $bcmask = "22";
            break;
        case "255.255.254.0":
            $bcmask = "23";
            break;
        case "255.255.255.0":
            $bcmask = "24";
            break;
        case "255.255.255.128":
            $bcmask = "25";
            break;
        case "255.255.255.192":
            $bcmask = "26";
            break;
        case "255.255.255.224":
            $bcmask = "27";
            break;
        case "255.255.255.240":
            $bcmask = "28";
            break;
        case "255.255.255.248":
            $bcmask = "29";
            break;
        case "255.255.255.252":
            $bcmask = "30";
            break;
        case "255.255.255.254":
            $bcmask = "31";
            break;
        case "255.255.255.255":
            $bcmask = "32";
            break;
        default:
            $bcmask = "wrong";
            break;
    }
    return $bcmask;
}

/**
 * @param $ip
 *
 * @return mixed|string
 * 获取服务IP地址名称，
 * 1.如果是纯IPv4环境，保持原有的不变；
 * 2.如果是纯IPv6环境，将:变成-;
 * 3.如果是IPv4和IPv6混合环境，IPv4地址加上IPv6环境，将IPv6地址中的:改成-;
 * @throws Exception
 */
function getServerIPName($ip)
{
    $ipinfo = get_ini_info(PATH_ETC . 'deveth.ini.noback', ['managerip', 'manager_ipv6_addrs']);

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        //是IPv4地址 如果v6配了, 则是混合环境
        if (!empty($ipinfo['manager_ipv6_addrs'])) {
            $ipv6s = explode(',', $ipinfo['manager_ipv6_addrs']);
            return $ip . '_' . str_replace(':', '-', $ipv6s[0]);
        }
    } else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $ip = str_replace(':', '-', $ip);
        //是IPv6地址 如果v4配了, 则是混合环境
        if (!empty($ipinfo['managerip'])) {
            return $ipinfo['managerip'] . '_' . $ip;
        }
        return '0.0.0.0_' . $ip;
    }

    return $ip;
}

/**
 * @param $mac1
 * @param $mac2
 * 比较mac大小
 * 如果mac1小于等于mac2则返回true，否则返回false;
 */
function compareMac($mac1, $mac2)
{
    $mac1 = str_replace(['-', ':', '.'], '', $mac1);
    $mac2 = str_replace(['-', ':', '.'], '', $mac2);
    $res = strncasecmp($mac1, $mac2, 12);
    if ($res <= 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $ipv6
 *
 * @return string
 * 将IPv6地址转化成0x开始的16进制串
 */
function IPv6ToIPv6Hex($ipv6)
{
    if ($ipv6 != '') {
        if (!strstr($ipv6, ':')) {
            return $ipv6;
        }
        $ipv6 = inet_pton($ipv6);
        $ipv6 = bin2hex($ipv6);
        $ipv6 = '0x' . $ipv6;
        return $ipv6;
    }
    return '';
}

/**
 * 生成ipv6报文
 *
 * @param $ipv6data
 *
 * @return string
 */
function setIPv6Xml($ipv6data)
{
    $xml = "<?xml version='1.0' encoding='gbk'?>
		<ASM>
            <TradeCode>{$ipv6data['TradeCode']}</TradeCode>
            <AgentID>{$ipv6data['DeviceID']}</AgentID>

		";
    if (!empty($ipv6data['IPv6Addrs'])) {
        $xml .= $ipv6data['IPv6Addrs'];
    } else {
        $xml .= "<IPv6Addrs>
                    <IPv6Addr>
                        <IPv6>{$ipv6data['IPv6']}</IPv6>
                        <MAC>{$ipv6data['Mac']}</MAC>
                    </IPv6Addr>
                ";
        if ($ipv6data['source'] == 'manual_dynamic_config') {
            $xml .= "<DelOldIPv6Addr>1</DelOldIPv6Addr>";
        }
        $xml .= "<uptade_source>{$ipv6data['source']}</uptade_source>";
        $xml .= "<IPv6Addrs>";
    }
    $xml .= "</ASM>";
    return $xml;
}

/**
 * 获取当前IP
 *
 * @return string|null
 */
function get_client_ip()
{
    if (!empty($GLOBALS['CONFIG']['ip'])) {
        return $GLOBALS['CONFIG']['ip'];
    }

    $result = getRemoteAddress();
    $GLOBALS['CONFIG']['ip'] = $result;
    return $result;
}

/**
 * 获取自定义的http端口信息
 * @return string
 * @throws Exception
 */
function getHttpPort()
{
    if (file_exists(PATH_ETC . "/asm/updateport/asm_http_port.ini")) {
        $httpPort = get_ini_info(PATH_ETC . '/asm/updateport/asm_http_port.ini', 'cur_http_port');
        $info = $httpPort ?: "80";
    } else {
        $info = "80";
    }
    return $info;
}