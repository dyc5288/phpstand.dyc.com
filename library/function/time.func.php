<?php
/**
 * Description: 时间相关函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: time.func.php 147146 2021-06-17 02:04:51Z duanyc $
 */

/**
 * 将秒数转换成时间值
 *
 * @param int $calltime
 *
 * @return string
 */
function func_time_getTime($calltime = 0)
{
    $str = "";
    if (is_array($calltime)) {
        $calltime = $calltime [0];
    }
    if ($calltime < 1) {
        return 0;
    }

    $value = $calltime % 60;
    $str = $value . L(11008) . $str;
    $calltime = floor($calltime / 60);
    if ($calltime < 1) {
        return $str;
    }

    $value = $calltime % 60;
    $str = $str != '0' . L(11008) ? $value . L(11007) . $str : $value . L(11007);
    $calltime = floor($calltime / 60);
    if ($calltime < 1) {
        return $str;
    }

    $value = $calltime % 24;
    $str = $value . L(11006) . $str;
    $calltime = floor($calltime / 24);
    if ($calltime < 1) {
        return $str;
    }

    $str = $calltime . L(11009) . $str;
    return $str;
}

/**
 *    获取毫秒级的数字时间戳
 * @return float
 */
function func_time_getMSec()
{
    list($t1, $t2) = explode(' ', microtime());

    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}


/**
 * 获取服务器当前时间
 *
 * @param string $Param 默认 "Y-m-d H:i:s"
 *
 * @return string "2016-01-01 12:33:11"
 */
function func_time_getNow($Param = "Y-m-d H:i:s")
{
    ini_set('date.timezone', 'Asia/Shanghai');
    return date($Param, time());
}
