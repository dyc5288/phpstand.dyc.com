<?php

/**
 * Description: ini文件相关函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: ini.func.php 147275 2021-06-18 06:40:52Z duanyc $
 */

/**
 * 读取一个ini文件
 *
 * @param string $filepath :文件全路径
 *
 * @return array
 * @throws Exception
 */
function read_inifile($filepath)
{
    $aResult = [];
    if (!is_file($filepath)) {
        throw new Exception('file [' . $filepath . '] not exist!', -2);
    }
    if (!is_readable($filepath)) {
        throw new Exception('file [' . $filepath . '] not exist!', -2);
    }
    $handle = @fopen($filepath, "r");
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle);
            $buffer = str_replace(
                [
                    "\r\n",
                    "\r",
                    "\n",
                ],
                "",
                $buffer
            );
            if (strlen($buffer) > 0) {
                if (substr($buffer, 0, 1) == ';' || substr($buffer, 0, 1) == '#' || strpos($buffer, '=') === false) {
                    // 本行是注释
                    $aResult [] = $buffer;
                } else {
                    // 本行不是注释
                    $aRow = explode("=", $buffer);
                    $key = rtrim(ltrim($aRow [0]));
                    $value = ltrim(ltrim($aRow [1]));
                    if (strpos($value, '"') !== false) {      // 有双引号
                        $value = substr($value, strpos($value, '"') + 1);
                        $value = substr($value, 0, strpos($value, '"'));
                    }
                    $aResult [$key] = $value;
                }
            }
        }
        fclose($handle);
    }
    return $aResult;
}

/**
 * 读取ini文件(用PHP内置的parse_init_file() 函数来读取ini文件，原ini读取函数有误，无法读取含有=的参数配置)
 *
 * @param string $filepath :文件全路径
 *
 * @return array
 * @throws Exception
 */
function parse_initfile($filepath)
{
    if (!is_file($filepath)) {
        throw new Exception('file [' . $filepath . '] not exist!', -2);
    }
    if (!is_readable($filepath)) {
        throw new Exception('file [' . $filepath . '] not exist!', -2);
    }
    $result = parse_ini_file($filepath);
    return $result;
}

/**
 * 将数组转换成标准ini文件格式的字符串
 *
 * @param mixed $aData
 * @param int $param 1时有双引号，0时无双引号
 *
 * @return string
 */
function array_to_inistr($aData, $param = 1)
{
    $str = '';
    $aKey = array_keys($aData);
    for ($i = 0; $i < count($aKey); $i++) {
        if (gettype($aKey [$i]) != 'integer') {
            switch ($param) {
                case '1': // 有双引号
                    $str .= $aKey [$i] . '="' . $aData [$aKey [$i]] . '"' . "\n";

                    break;
                case '0': // 无双引号
                    $str .= $aKey [$i] . '=' . $aData [$aKey [$i]] . "\n";
                    break;
                default:
            }
        } else {
            $str .= $aData [$aKey [$i]] . "\n";
        }
    }
    return $str;
}

/**
 * @param $path
 * @param mixed $param
 *
 * @return array|string
 * @throws Exception
 * 获取配置信息内容，path为配置信息路径
 * $param为字符串就返回一个值，如果是数组就返回相应的数组，默认为空，放回所有的配置信息
 */
function get_ini_info($path, $param = '')
{
    $devethinfo = read_inifile($path);
    if ($param == '') {
        return $devethinfo;
    }
    if (is_string($param)) {
        return isset($devethinfo[$param]) ? $devethinfo[$param] : '';
    }
    $newarr = [];
    foreach ($param as $key => $value) {
        if (isset($devethinfo[$value])) {
            $newarr[$value] = $devethinfo[$value];
        }
    }

    return $newarr;
}
