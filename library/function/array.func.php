<?php

/**
 * Description: 数组相关操作（扩展数组操作类）
 * 主要作用：1. 用于弥补php5.3版本不支持的一些数组操作
 *         2. 针对一些特定的数组操作的公共方法
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: array.func.php 147146 2021-06-17 02:04:51Z duanyc $
 */

/**
 * 获取二维数组中的某一列.
 * 用法跟array_column一致，只是5.3版本中没有该方法
 *
 * @param array $array 必须为二维以上的数组，且二维里面必须数组类型
 * @param mixed $arrayKey
 * @param mixed $indexKey
 *
 * @return array|bool
 */
if (!function_exists('arrayColumn')) {
    function arrayColumn($array, $columnkey, $indexkey = null)
    {
        if (!is_array($array)) {
            return false;
        }
        if (PHP_VERSION > '5.3.3') {
            return array_column($array, $columnkey, $indexkey);
        }
        $result = [];
        foreach ($array as $subarray => $value) {
            if (array_key_exists($columnkey, $value)) {
                $val = $array[$subarray][$columnkey];
            } else if ($columnkey === null) {
                $val = $value;
            } else {
                continue;
            }
            if ($indexkey === null) {
                $result[] = $val;
            } elseif ($indexkey == -1 || array_key_exists($indexkey, $value)) {
                $result[($indexkey == -1) ? $subarray : $array[$subarray][$indexkey]] = $val;
            }
        }
        return $result;
    }
}

/**
 * 将后一个二维数组合并到前一个二维数组，keys作为唯一性判断重复
 *
 * @param array $arrayFrist 数组
 * @param array $arraySecond 数组
 * @param array $arrayKeys 一维数组
 *
 * @return array
 */
function arrayMerge($arrayFrist, $arraySecond, $arrayKeys)
{
    if (!is_array($arrayFrist) || !is_array($arraySecond)) {
        return [];
    }
    if (empty($arraySecond)) {
        return $arrayFrist;
    }
    if (empty($arrayKeys)) {
        $arrayFrist = array_merge($arrayFrist, $arraySecond);
    }
    foreach ($arraySecond as $key => $value) {
        $keExists = true;
        foreach ($arrayFrist as $arrayFristKey => $arrayFristValues) {
            foreach ($arrayKeys as $filed) {
                if (array_key_exists($filed, $arrayFristValues) && array_key_exists($filed, $value)) {
                    if (!empty($value[$filed]) && $arrayFristValues[$filed] == $value[$filed]) {
                        $keExists = false;
                    } else {
                        $keExists = true;
                        break;//不相等直接返回，其余字段无需判断
                    }
                }
            }
            if (!$keExists) {//存在相同的无需再判断
                break;
            }
        }
        if ($keExists) {
            $arrayFrist[] = $value;
        }
    }
    return $arrayFrist;
}

/**
 * 对所有部门数据进行递归分组 原fGroups方法
 *
 * @param array $aLastDepart
 * @param array $aInputID
 * @param array $aInputDepart
 * @return array
 */
function depart_groups($aLastDepart, $aInputID, $aInputDepart)
{
    $aWait = [];
    $aLast = [];
    for ($i = 0; $i < count($aLastDepart); $i++) {
        if ($aLastDepart [$i] ['UpID'] == 0) {
            $aInputID [] = $aLastDepart [$i] ['DepartID'];
            $aInputDepart [] = $aLastDepart [$i];
        } else {
            $aWait [] = $aLastDepart [$i];
        }
    }
    for ($i = 0; $i < count($aWait); $i++) {
        if (is_array($aInputID) && in_array($aWait [$i] ['UpID'], $aInputID)) {
            $aInputID [] = $aWait [$i] ['DepartID'];
            $aInputDepart [] = $aWait [$i];
        } else {
            $aLast [] = $aWait [$i];
        }
    }

    if (!count($aLast)) {
        return $aInputDepart;
    } else {
        return depart_groups($aLast, $aInputID, $aInputDepart);
    }
}
