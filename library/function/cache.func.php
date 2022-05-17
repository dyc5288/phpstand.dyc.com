<?php
/**
 * Description: 缓存函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/05/21 12:46
 * Version: $Id: cache.func.php 147146 2021-06-17 02:04:51Z duanyc $
 */

/**
 * key转数组
 * @param $keys
 * @return array
 */
function keysToArray($keys)
{
    if (is_array($keys) || $keys instanceof ArrayAccess) {
        array_walk($keys, function (&$v) {
            $v = (string) $v;
        });
        return $keys;
    }
    if (is_string($keys)) {
        $keys = explode(',', $keys);
        return array_map(function ($k) {
            return trim($k);
        }, $keys);
    }
    return array((string) $keys);
}

/**
 * 获取值
 * 当指定key不存在时，二维数组中将不存在对应元素
 * 当指定field不存在时，此field的值将为null
 *
 * @param string $prefix 键前缀
 * @param int|string|array $keys 键 支持多个，可以是数组也可以是字符串（字符串id以逗号分隔）
 * @param string|array $field 属性名(如：TDevice.LastTime)，如果为空则返回$key对应的所有数据,如果是多个下标的数据则返回array
 * @return array|mixed|null 以$key为键：获取单个值返回一维，多个值返回二维 没有返回null
 * @example
 * cache_get_info('DeviceID_', 1); //['TDevice.LastTime' => '2014-11-10 22:00:33','TDevice.Online'=> 1]
 * cache_get_info('DeviceID_', 'x'); //null
 * cache_get_info('DeviceID_', 1, 'TDevice.LastTime'); //'2014-11-10 22:00:33'
 * cache_get_info('DeviceID_', 1, 'x'); //null
 * cache_get_info('DeviceID_', '1,2'); //['1' => ['TDevice.LastTime' => '2014-11-10 22:00:33','TDevice.Online'=>1], '2' => ['TDevice.LastTime' => 2014-11-10 22:00:33','TDevice.Online'=>1]]
 * cache_get_info('DeviceID_', 'x,2'); //['2' => ['TDevice.LastTime' => 2014-11-10 22:00:33','TDevice.Online'=>1]]
 * cache_get_info('DeviceID_', '1,2', 'TDevice.LastTime'); //['1' => '2014-11-10 22:00:33', '2' => '2014-11-10 22:00:33']
 * cache_get_info('DeviceID_', 'x,2', 'TDevice.LastTime'); //['2' => '2014-11-10 22:00:33']
 * cache_get_info('DeviceID_', '1,2', array('TDevice.LastTime')); //['1' => ['TDevice.LastTime' => '2014-11-10 22:00:33'], '2' => ['TDevice.LastTime' => 2014-11-10 22:00:33']]
 * cache_get_info('DeviceID_', 'x,2', array('TDevice.LastTime')); //['2' => ['TDevice.LastTime' => 2014-11-10 22:00:33']]
 * cache_get_info('DeviceID_', '1,2', array('TDevice.LastTime')); //['1' => ['TDevice.LastTime' => '2014-11-10 22:00:33'], '2' => ['TDevice.LastTime' => 2014-11-10 22:00:33']]
 * cache_get_info('DeviceID_', 'x,2', array('TDevice.LastTime', 'x)); //['2' => ['TDevice.LastTime' => 2014-11-10 22:00:33', 'x' => null]]
 */
function cache_get_info($prefix, $keys, $field = null)
{
    $keys = keysToArray($keys);
    //直接返回单行数据
    if (count($keys) === 1) {
        return lib_redis::getHash($prefix, $keys[0], $field);
    }

    $result = null;
    foreach ($keys as $key) {
        $val = lib_redis::getHash($prefix, $key, $field);
        if ($val === null) {
            continue;
        }
        $result[$key] = $val;
    }

    return $result;
}

/** @noinspection MoreThanThreeArgumentsInspection */
/**
 * 更新数据如果不存在则自动添加
 * $tabColVal 只能为一维数组
 *
 * @param string $prefix 键前缀
 * @param int|string|array $keys 设备id 支持多个，可以是数组也可以是字符串（字符串id以逗号分隔）
 * @param array $tabColVal 需要更新的字段可以多个 只能为一维数组
 * @param null|int $expire
 * @return bool true/false 成功/失败
 * @example
 * cache_set_info('112,113',array('TDevice.LastTime'=>'2014-11-10 22:00:33','TDevice.Online'=>1));
 * cache_set_info(array(112,113),array('TDevice.LastTime'=>'2014-11-10 22:00:33','TDevice.Online'=>1));
 */
function cache_set_info($prefix, $keys, array $tabColVal, $expire = null)
{
    $keys = keysToArray($keys);
    foreach ($keys as $key) {
        if (!lib_redis::hMSetEx($prefix . $key, $tabColVal, $expire)) {
            return false;
        }
    }
    return true;
}

/**
 * 删除一整个key或者某个属性字段
 * @param string $prefix 键前缀
 * @param int|string|array $keys 键 支持多个，可以是数组也可以是字符串（字符串id以逗号分隔）
 * @param null|string|array $field 表名.列名(如：TDevice.LastTime)，如果为空则删除key的所有数据
 * @return bool 成功为true
 */
function cache_del_info($prefix, $keys, $field = null)
{
    $keys = keysToArray($keys);
    return lib_redis::mDel($prefix, $keys, $field);
}
