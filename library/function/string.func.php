<?php
/**
 * Description: 字符串相关操作函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: string.func.php 157150 2021-09-17 09:10:16Z duanyc $
 */

/**
 * 将数组转换成标准ini文件格式的字符串
 *
 * @param array $aData
 * @param int $param 1时有双引号，0时无双引号
 *
 * @return string
 */
function arrayToIniString($aData, $param = 1)
{
    $str = '';
    $aKey = array_keys($aData);
    for ($i = 0; $i < count($aKey); $i++) {
        if ($param == 1) {
            $str .= $aKey [$i] . '="' . $aData [$aKey [$i]] . '"' . "\n";
        } else {
            $str .= $aKey [$i] . '=' . $aData [$aKey [$i]] . "\n";
        }
    }
    return $str;
}

/**
 * 将十进制字符串转换成十六进制
 *
 * @param string $str "12345"
 *
 * @return string AABBCCDD
 */
function stringTohex($str)
{
    $r_str = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $r_str .= ord($str [$i]) < 16 ? '0' . dechex(ord($str [$i])) : dechex(ord($str [$i]));
    }
    return $r_str;
}

/**
 * 将十六进制字符串转换成十进制
 *
 * @param string $str AABBCCDD
 *
 * @return string "123456"
 */
function hexTostring($str)
{
    $str1 = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i = $i + 2) {
        $str1 .= chr(hexdec(substr($str, $i, 2)));
    }
    return $str1;
}

/**
 * 去掉数组中为空的值(回调函数)
 *
 * @param string $var
 *
 * @return bool
 */
function filter($var)
{
    if ($var == '') {
        return false;
    }
    return true;
}

/**
 * 截取中间字符串
 *
 * @param  string $strSrc ="aabbcc", $strFirst="aa", $strLast="cc"
 *
 * @return "bb"
 *
 */
function GetSubStr($strSrc, $strFirst, $strLast)
{
    $p1 = stripos($strSrc, $strFirst);
    if ($p1 === false) {
        return "";
    }
    $len = strlen($strFirst);
    $p2 = stripos($strSrc, $strLast, $p1 + $len);
    if ($p2 == false) {
        return "";
    }
    return substr($strSrc, $p1 + $len, $p2 - $p1 - $len);
}

/**
 * 字符是只包含0或1
 *
 * @param $str
 *
 * @return boolean
 * */
function is_str_0_1($str)
{
    if ($str == 1 || $str == 0) {
        return true;
    } else {
        return false;
    }
}


/**
 * 二维数组按字符串大小自然排序
 *
 * @author zhangkb 201208
 *
 * @param array $arr 需要排序的数组
 * @param string $keys 按照排序的key
 * @param string $type asc/dsc 正序/倒序
 *
 * @return array
 */
function array_sort_b($arr, $keys, $type = 'asc')
{
    $arrayfind = [];
    $keysvalue = [];
    $new_array = [];
    foreach ($arr as $k => $v) {
        $keysvalue [$k] = $v [$keys];
    }
    natsort($keysvalue);
    if ($type != 'asc') {
        $keysvalue = array_reverse($keysvalue, true);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array [] = $arr [$k];
    }
    return $new_array;
}


/**
 * 二维数组按字符串的长度排序
 *
 * @param array $arr 需要排序的数组
 * @param string $keys 按照排序的key
 * @param string $type asc/dsc 正序/倒序
 *
 * @return array
 */
function array_sort($arr, $keys, $type = 'asc')
{
    $keysvalue = [];
    $new_array = [];
    foreach ($arr as $k => $v) {
        $keysvalue [$k] = strlen($v [$keys]);
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array [] = $arr [$k];
    }
    return $new_array;
}


/**
 * 将条件转换成传递的url参数字符串
 *
 * @param array $aQuery
 *
 * @return string url
 */
function query_to_whereurl($aQuery)
{
    if (!is_array($aQuery)) {
        return '';
    }
    $aKey = array_keys($aQuery);
    $str = '';
    for ($i = 0; $i < count($aQuery); $i++) {
        $str .= "&" . $aKey [$i] . "=" . $aQuery [$aKey [$i]];
    }
    return $str;
}

/**
 * 判断字符串是否是UTF-8编码
 *
 * @param string $str 要判断的字符串
 *
 * @return boolean
 */
function IsUTF8($str)
{
    return preg_match('~[\x{4e00}-\x{9fa5}]+~u', $str);
}

/**
 * 自动检测版本的gbk转utf8
 *
 * @param $str
 *
 * @return bool|string
 */
function strGbkToUtf8($str)
{
    $utf8Result = !IsUTF8($str) ? @iconv('GBK', 'UTF-8', $str) : false;
    if ($str != "" && $utf8Result != '') {
        $str = $utf8Result;
    }
    return $str;
}

/**
 * GBK转UTF-8
 *
 * @param $result
 *
 * @return array|string
 */
function gbkToUtf8($result)
{
    if (is_string($result)) {
        $result = iconv('GBK', 'UTF-8', $result);
    } else if (is_array($result)) {
        foreach ($result as $k => $v) {
            $result[$k] = gbkToUtf8($v);
        }
    }
    return $result;
}

/**
 * UTF-8转GBK
 *
 * @param $result
 *
 * @return array|string
 */
function utf8ToGbk($result)
{
    if (is_string($result)) {
        $result = iconv('UTF-8', 'GBK', $result);
    } else if (is_array($result)) {
        foreach ($result as $k => $v) {
            $result[$k] = utf8ToGbk($v);
        }
    }
    return $result;
}

/**
 * 去除标签
 *
 * @param $result
 *
 * @return array|string
 */
function dataStrpTag($result)
{
    if (is_string($result)) {
        $result = strip_tags(str_replace('&nbsp;', '', $result));
    } else if (is_array($result)) {
        foreach ($result as $k => $v) {
            $result[$k] = $v;
        }
    }
    return $result;
}

/**
 * 去除特殊不可见字符
 * @author zhangkb 20150413
 *
 * @param string $str 需要去除的字符串
 *
 * @return string 去除后的字符串
 * */
function FindSpecialChar($str)
{
    $num = strlen($str);
    $item = '';
    for ($i = 0; $i < $num; $i++) {
        $f_str = ord($str[$i]);
        $f_str_1 = ord($str[$i + 1]);
        if (($f_str > 31 && $f_str < 127) || $f_str == 10 || $f_str == 9 || $f_str == 20 || $f_str == 13 || $f_str == 23) {//a-zA-Z1-9标点符号等可见字符
            $item .= $str[$i];
            continue;
        } else if ($f_str > 127) {
            if ($f_str >= 129 && $f_str <= 254 && (($f_str_1 >= 64 && $f_str_1 <= 149) || ($f_str_1 >= 128 && $f_str_1 <= 254)) && isset($f_str_1)) {
                //汉字
                $item .= $str[$i] . $str[$i + 1];
                $i += 1;
            }
        } else {
            cutil_php_debug("Filter special characters ASCII::" . $f_str, "cutil_shell_exec");
        }
    }
    return $item;
}

/**
 * @author zhangkb 20150430
 *
 * @param  string $str 判断是否有禁止的字符串, 如果有, 抛出异常
 *
 * @return exception / 原始字符串
 * @throws Exception
 * */
function GetNormalDownStr($str)
{
    $strs = $str;
    $str = FindSpecialChar($str); //只允许可见字符
    $aInfo = [";", "..", "`", "~", "&", "?", '"', "<", ">", "|"];
    $aInfo_1 = [".php", ".htm"];//禁止上传下载php,htm,html文件
    $str = str_ireplace($aInfo, "", $str);
    $str = str_ireplace($aInfo_1, "", $str);
    if ($strs != $str) {
        $time_err = "400" . time();
        $str = "#########Start#########GetNormalDownStr[" . $time_err . "]\nOriginal command:>>>" . var_export($strs, true) . "<<<" . strlen($strs) . "\n";
        $str .= "Execute the order:>>>" . var_export($str, true) . "<<<" . strlen($str) . "\n";
        cutil_php_debug($str, "cutil_shell_exec");
        throw new Exception("Please carefully check the string containing illegal or submitted information.", -2);
    }
    return $str;
}

/**
 * 判断是否有危险字符, 如果有, 抛出异常
 * @author zhangkb 20150508
 *
 * @param string $cmd 主要用于去除特殊字符
 *
 * @throws Exception
 * */
function RemoveSpecialStr($cmd)
{
    $cmd = trim($cmd);
    $cmd_y = $cmd;
    // yanzj 20180320 放开网络的时候会发送一个PATH_ASM . "sbin/auth_client -t offline ，当用户中含有&字符串时会被组织掉，在此例外掉
    if (strstr($cmd, "warnner ") !== false || strstr($cmd, "auth_client ") !== false || strstr($cmd, "DascPubMssage") !== false) {
        return $cmd;
    }
    //  $cmd = FindSpecialChar($cmd); //只允许可见字符
    $cmd = str_replace('2>&1', "#INFOGO20150508#", $cmd);
    $cmd = str_replace("'color:red;font-size:22px;'", "#INFOGOcolor#", $cmd); //用于安检提交页面
    $cmd = str_replace('&nbsp;', "#INFOGO#", $cmd);//用于安检提交页面
    $find_s = strrpos($cmd, "&");
    $flag_1 = 0;
    if ($find_s !== false && $find_s == (strlen($cmd) - 1)) {
        $cmd = substr($cmd, 0, $find_s);
        $flag_1 = 1;
    }

    $aInfo = [";", "..", "`", "&", "\0", "\x00"];
    $cmd = str_ireplace($aInfo, "", $cmd);
    $cmd = str_replace("#INFOGO20150508#", '2>&1', $cmd);
    $cmd = str_replace("#INFOGO#", '&nbsp;', $cmd);
    $cmd = str_replace("#INFOGOcolor#", "'color:red;font-size:22px;'", $cmd);
    if (1 == $flag_1) {
        $cmd = $cmd . "&";
    }
    if (strcmp($cmd_y, $cmd) != 0) {
        $time_err = "300" . time();
        $str = "#########Start#########RemoveSpecialStr[" . $time_err . "]\nOriginal command:>>>" . var_export($cmd_y, true) . "<<<" . strlen($cmd_y) . "\n";
        $str .= "Execute the order:>>>" . var_export($cmd, true) . "<<<" . strlen($cmd) . "\n";
        cutil_php_debug($str, "cutil_shell_exec");
        throw new Exception("Please carefully check the string containing illegal or submitted information.");
    }

    return $cmd;
}

//防止中间部门节点丢失，自动补齐
function CheckData($arr, $UpID)
{
    $temp = false;
    if ($UpID != "") {
        foreach ($arr as $linearr) {
            if ($linearr['DepartID'] == $UpID) {
                $temp = true;
                break;
            }
        }
        $UpIDString = hexTostring($UpID);
        if (!$temp) {
            $uparr = explode("|", $UpIDString);
            $firstItem = array_shift($uparr);
            $newuparr = count($uparr) > 0 ? stringTohex(implode("|", $uparr)) : 0;
            $arr[] = ['UpID' => $newuparr, 'DepartID' => $UpID, 'DepartName' => $firstItem, 'root' => $newuparr];
        }
    }

    return $arr;
    //$AjaxResult->AReturn($ADdepart);
}

if (!function_exists('e')) {
    /**
     * 将字符串中特殊转义为HTML实体编码, 用于防止注入
     * e 是 escape 的缩写, 来自Laravel
     *
     * @param  string $value 待转义的字符串
     * @param  bool $doubleEncode 是否转义双引号, 默认不转义
     *
     * @return string
     */
    function e($value, $doubleEncode = false)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

/**
 * 处理XSS跨站攻击的过滤函数
 *
 * @param string $str
 *
 * @return mixed
 */
function clean_xss($str)
{
    if (empty($str)) {
        return $str;
    }

    //插入代码不进用清理
    if (stripos($str, '</pre>') !== false) {
        //preg_replace会给匹配的字符串加反斜杠
        $str = preg_replace_callback(
            '/<pre\s+class=\\\?["\']?([a-z0-9]+)\\\?["\']?\s+rel=\\\?["\']?qz_source_code\\\?["\']?\s+name=\\\?["\']?code\\\?["\']?\s*>([\s\S]+?)<\/pre>/is',
            "parse_source_code",
            $str
        );
    }
    //去除非可见字符
    $str = preg_replace('/([\x00-\x08]|[\x0b-\x0c]|[\x0e-\x12]|[\x14-\x19])/', '', $str);
    //一般不需要转码,这里将unicode转码字符还原
    //如 <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()<>';
    $search .= '~`";:?+/={}[]-_|\'\\';
    $replaces = [];
    $patterns = [];
    $len = strlen($search);
    for ($i = 0; $i < $len; $i++) {
        //replaces
        $replaces[] = addslashes($search[$i]);
        //patterns
        $patterns[] = '/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)|(&#0{0,8}' . ord($search[$i]) . ';?)/i';
    }
    $str = preg_replace($patterns, $replaces, $str);
    //要过滤的标签
    $cleans['tags'] = ['head', 'title', 'body', 'form', 'javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'base', 'vmlframe', 'svg', 'input', 'video', 'plaintext'];
    //要过滤的事件
    $cleans['events'] = ['onurlflip', 'onundo', 'ontrackchange', 'ontimeerror', 'onsyncrestored', 'onstorage', 'onseek', 'onrowinserted', 'onrowdelete', 'onrowsenter', 'onreverse', 'onresume', 'onrepeat', 'onredo', 'onpopstate', 'onpause', 'onoutofsync', 'onoffline', 'onmessage', 'onmediaerror', 'onmediacomplete', 'onhashchange', 'onend', 'ondragdrop', 'onbegin', 'fscommand', 'seeksegmenttime', 'oninput', 'onprogress', 'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'ontouchstart', 'onsuspend'];
    //其他要过滤的
    $cleans_others = [
        '/<(a|math)[^>]+href=\\\?["\']?((java|vb)script|data):.*<\/\\1>/isU',
        '/<img[^>]+_?src=\\\?["\']?(java|vb)script:.*>/isU',
        '/class=\\\?["\']?[^"\'\\\]*((js\-\w)|(gadget\-state))[^"\'\\\]+\\\?["\']?/i',
        '/style=\\\?["\'][^"\'\\\]*(expression\(|javascript:|import\(|\/\*+?\/|\\\\\\\\\d+?).+\\\?["\']/iU',
        '/style=\\\?["\'][^"\'\\\]*behavior\:.+\\\?["\']/iU',
    ];
    //剩余可能包含的字符:\t,\n,\r
    $regex = '((&#[xX]0{0,8}([9ab]);)|(&#0{0,8}([9|10|13]);))*';
    //组装正则数组
    foreach ($cleans as $k => &$strip) {
        foreach ($strip as &$tag) {
            $pattern = '';
            $let = strlen($tag);
            for ($j = 0; $j < $let; $j++) {
                $pattern .= ($j > 0 ? $regex : '') . $tag[$j];
            }
            if ($k == 'tags') {
                /*$str = preg_replace("/<(".$pattern.")[^<>]*?>(.*?<\/\\1>)?/is", '', $str);*/
                $tag = "/<(" . $pattern . ")[^<>]*>?(.*?<\/\\1>)?/is";
            } else {
                //$str = preg_replace("/(".$pattern.")=/i", '*\\0', $str);
                $tag = "/(" . $pattern . "\s*=)/i";
            }
        }
    }

    //多次循环直到不包含相应标签
    $found = true;
    while ($found) {
        $old_str = $str;
        $str = preg_replace($cleans['tags'], '', $str);
        if ($old_str == $str) {
            $found = false;
        }
    }

    //过滤事件
    $str = preg_replace($cleans['events'], '-no-\\1', $str);
    $str_pk = $str;
    $str = preg_replace($cleans_others, '', $str);

    //正则回溯限制超出限制时返回NULL
    if (preg_last_error() != 0) {
        $str = $str_pk;
    }

    return $str;
}
