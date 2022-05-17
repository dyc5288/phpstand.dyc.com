<?php
/**
 * Description: 原来的LoadXml类
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: cls_xml.php 158006 2021-09-27 11:37:50Z zhangkun $
 */

class cls_xml
{
    public $xml;
    public $res;        //返回结果

    /**
     * 构造函数
     *
     * @param $str
     *
     * @throws Exception
     */
    public function __construct($str)
    {
        $str = str_replace("& ", "&amp; ", $str);
        $str = str_replace("\n", "", $str);
        $str = str_replace("\r", "        ", $str);
        $str = IsUTF8($str) ? utf8ToGbk($str) : $str;
        $str = stripslashes($str);
        $this->xml = simplexml_load_string($str);
        if (get_class($this->xml) != 'SimpleXMLElement') {
            $str = $this->findSpecialChar($str);
            $this->xml = @simplexml_load_string($str);
            if (@get_class($this->xml) != 'SimpleXMLElement') {
                throw new Exception("Error parsing xml message" . $str, -2);
            }
        }
    }

    /**
     * 编码转换
     */
    public function convertEncode()
    {
        foreach ($this->res as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    if (is_array($value2)) {
                        foreach ($value2 as $key3 => $value3) {
                            $this->res[$key][$key2][$key3] = strval($value3);
                        }
                    } else {
                        $this->res[$key][$key2] = strval($value2);
                    }
                }
            } else {
                $this->res[$key] = strval($value);
            }
        }
    }

    /**
     * 找到特殊字符
     *
     * @param $str
     *
     * @return string
     */
    public function findSpecialChar($str)
    {
        $num = strlen($str);
        $item = '';
        for ($i = 0; $i < $num; $i++) {
            $f_str = ord($str[$i]);
            $f_str_1 = ord($str[$i + 1]);
            if (($f_str > 31 && $f_str < 127) || $f_str == 10 || $f_str == 13 || $f_str == 23) {//a-zA-Z1-9标点符号等可见字符
                $item .= $str[$i];
                continue;
            } else if ($f_str > 127) {
                if ($f_str >= 129 && $f_str <= 254 && (($f_str_1 >= 64 && $f_str_1 <= 149) || ($f_str_1 >= 128 && $f_str_1 <= 254)) && isset($f_str_1)) {
                    //汉字
                    $item .= $str[$i] . $str[$i + 1];
                    $i += 1;
                }
            } else {
                //cutil_php_debug("过滤掉的特殊字符ASCII::" . $f_str, "cutil_shell_exec");
            }
        }
        return $item;
    }
}
