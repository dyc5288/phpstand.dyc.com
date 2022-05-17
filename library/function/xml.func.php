<?php

/**
 * Description: XML相关函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: xml.func.php 157618 2021-09-23 15:01:55Z duanyc $
 */

/*
 * !start---替换掉XML报文中的&,<,>
 */
function xmlReplace($strs)
{
    $replaces = ['&' => '＆', '<' => '＜', '>' => '＞'];
    foreach ($replaces as $search => $replace) {
        $strs = str_replace($search, $replace, $strs);
    }
    // $strs = str_replace('\'','&apos;',$strs);
    // $strs = str_replace('"','&quot;',$strs);
    return $strs;
}

/*
 * 替换掉XML报文中的&,<,>---end!
 */
/*
 * !start---替换掉XML报文中的＆,＜,＞;
 */

function xmlChangeReplace($strs)
{
    $searchs = [0 => '＆', 1 => '＜', 2 => '＞'];
    $replaces = [0 => '&', 1 => '<', 2 => '>'];
    foreach ($replaces as $key => $replace) {
        $strs = str_replace($searchs[$key], $replace, $strs);
    }
    // $arr_search = array('<','>','&','\'','"');
    // $arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
    // $strs = str_ireplace($arr_search,$arr_replace,$strs);

    return $strs;
}

/**
 * fix both XML encoding to be UTF8, and replace standard XML entities < > " & '
 * @author xialf@infogo.com.cn
 * @time 2019/12/06 18:23:00
 *
 * @param string $string
 *
 * @return string
 * @access private
 */
function fixXmlEncoding($string)
{
    return strtr(
        $string,
        [
        '&'  => '&amp;',
        '>'  => '&gt;',
        '<'  => '&lt;',
        '"'  => '&quot;',
        '\'' => '&apos;',
        ]
    );
}

/**
 * 将数组转换成xml
 *
 * @param $datas = array("status" => "false","info" => "返回的提示内容格式错误！" );
 *                  array("status" => "true","info" =>
 *     "正确内容","aa"=>"aa1","bb"=>array("a"=>"b","c"=>"d")... );
 * */
function FuncMakeXML($datas)
{
    if (is_array($datas)) {
        header("Content-type: text/xml");
        printf("<?xml version=\"1.0\" encoding=\"gbk\"?>\n");
        printf("\t<rows>\n");
        foreach ($datas as $key => $item) {
            if (is_array($item)) {
                $aKeys = array_keys($item);
                printf("\t\t<row>\n");
                foreach ($aKeys as $str) {
                    printf("\t\t\t<cell name='$str'><![CDATA[" . $item[$str] . "]]></cell>\n");
                }
                printf("\t\t</row>\n");
            } else {
                printf("\t\t<cell name='$key' ><![CDATA[" . $item . "]]></cell>\n");
            }
        }
        printf("\t</rows>\n");
        return true;
    } else {
        return false;
    }
}

/**
 * 将xml转换为数组
 *
 * @param $xmlStr
 * @throws Exception
 * @return mixed
 */
function xmlToArray($xmlStr)
{
    $xmlObj = new cls_xml($xmlStr);
    $xml = o2a($xmlObj->xml);
    return convertEncode($xml);
}

/**
 * 将对象转换为数组
 *
 * @param $o
 *
 * @return array
 */
function o2a($o)
{
    if (is_object($o)) {
        $o = (array)$o;
        $o = o2a($o);
    } elseif (is_array($o)) {
        foreach ($o as $k => $v) {
            $o[$k] = o2a($v);
        }
    }
    return $o;
}

/**
 * 转码
 *
 * @param $mix
 *
 * @return array|string
 */
function convertEncode($mix)
{
    if (is_array($mix)) {
        foreach ($mix as $k => $v) {
            $mix[$k] = convertEncode($v);
        }
    } elseif (is_string($mix)) {
        $mix = utf8ToGbk($mix);
    }
    return $mix;
}
