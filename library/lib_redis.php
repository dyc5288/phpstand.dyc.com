<?php

/**
 * Description: redis操作
 * User: duanyc@infogo.com.cn
 * Date: 2019/04/06 10:32
 * Version: $Id: lib_redis.php 167924 2022-01-25 06:31:27Z duanyc $
 */

!defined('IN_INIT') && exit('Access Denied');

class lib_redis
{
    /**
     * 当前从redis连接标识
     *
     * @access protected
     * @var array[Redis]
     */
    protected static $redis = null;
    /**
     * 全局前缀
     *
     * @access protected
     * @var string
     */
    protected static $prefix = "";
    /**
     * 错误代码
     * @var int
     */
    public static $errno = 0;
    /**
     * 错误消息
     * @var string
     */
    public static $error = '';

    /**
     * 初始化redis对象
     * @access protected
     *
     * @param bool $is_master
     *
     * @return Redis
     */
    protected static function init($is_master = false)
    {
        $index = self::getRedisServer($is_master);
        if (empty(self::$redis[$index]) || !self::ping($index)) {
            try {
                if (!isset($GLOBALS['CONFIG']['redis'][$index])) {
                    throw new Exception("Unknown redis server({$index})");
                }
                if (!empty(self::$redis[$index])) {
                    self::close($index);
                }
                $redis = $GLOBALS['CONFIG']['redis'][$index];
                $redisObj = new Redis();
                $redisObj->connect($redis['host'], $redis['port'], $redis['timeout']);
                //$redisObj->setOption(Redis::OPT_PREFIX, self::$prefix . ":");
                // 认证
                if (!empty($GLOBALS['CONFIG']['redis_config']['password'])) {
                    $redisObj->auth((string)$GLOBALS['CONFIG']['redis_config']['password']);
                }
                self::$redis[$index] = $redisObj;
            } catch (Exception $e) {
                self::$errno = $e->getCode();
                self::$error = $e->getMessage();
                self::errorLog();
                return null;
            }
        }
        return self::$redis[$index];
    }

    /**
     * 设置前缀
     * @access private
     *
     * @param mixed $key
     * @param string $prefix
     *
     * @return mixed
     */
    private static function getKey($key, $prefix = '')
    {
        $prefix = empty($prefix) ? '' : $prefix . "";
        if (is_array($key)) {
            if ($prefix) {
                $is_num = isset($key[0]);
                foreach ($key as $_k => $_v) {
                    if ($is_num) {
                        $key[$_k] = $prefix . $_v;
                    } else {
                        $key[$prefix . $_k] = $_v;
                        unset($key[$_k]);
                    }
                }
            }
        } else {
            $key = $prefix . $key;
        }
        return $key;
    }

    /**
     * 获取redis配置组
     * @static
     *
     * @param bool $is_master
     *
     * @return int|string
     */
    private static function getRedisServer($is_master)
    {
        $index = 0;//默认第一台为Master
        if (!$is_master) {
            $index = array_rand($GLOBALS['CONFIG']['redis']);
        }
        return $index;
    }

    /**
     * 写入一个redis string类型的数组
     *
     * @param string $prefix
     * @param string $key
     * @param mixed $value
     * @param int $expire 如果有效期 要考虑到批量（mset）操作的
     *
     * @return boolean true/false
     */
    public static function set($prefix, $key, $value, $expire = 0)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                // 带过期时间
                if ($expire > 0) {
                    return $redis->set($key, $value) && $redis->expire($key, $expire);
                }
                // 默认持久化的
                return $redis->set($key, $value);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 获取Srings内容
     *
     * @param string $prefix
     * @param $key
     *
     * @return mixed
     */
    public static function get($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->get($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 批量获取Strings内容
     *
     * @param string $prefix
     * @param array $keys
     *
     * @return mixed
     */
    public static function mget($prefix, $keys)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $keys = self::getKey($keys, $prefix);
                return $redis->mget($keys);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 删除某个KEY
     *
     * @param $prefixs
     * @param $key
     *
     * @return int
     */
    public static function del($prefixs, $key)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefixs);
                return $redis->del($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 由列表头部添加字符串值。如果不存在该键则创建该列表。
     * 如果该键存在，而且不是一个列表，返回FALSE
     *
     * @param string $prefix
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public static function lpush($prefix, $key, $value)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->lPush($key, $value);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 计算redis Lists 元素数量
     *
     * @param  string $prefix
     * @param  mixed $key
     *
     * @return mixed
     */
    public static function lSize($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->lSize($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 由列表尾部添加字符串值。如果不存在该键则创建该列表。
     * 如果该键存在，而且不是一个列表，返回FALSE。
     *
     * @param string $prefix
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public static function rpush($prefix, $key, $value)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->rPush($key, $value);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 返回指定键存储在列表中指定的元素。
     * 0第一个元素，1第二个… -1最后一个元素，-2的倒数第二…错误的索引或键不指向列表则返回FALSE。
     *
     * @param string $prefix
     * @param string $key
     * @param int $index
     * @params string $key
     *
     * @return bool | string | array
     */
    public static function lget($prefix, $key, $index = 0)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->lGet($key, $index);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 从头部并删除一个元素,并返回删除的值
     *
     * @param  string $prefix
     * @param  string $key
     *
     * @internal param int $num
     * @return mixed
     */
    public static function lpop($prefix, $key)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->lPop($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 从尾部并删除一个元素,并返回删除的值
     *
     * @param  string $prefix
     * @param  string $key
     *
     * @internal param int $num
     * @return mixed
     */
    public static function rpop($prefix, $key)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->rPop($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 获取指定列表范围内的元素
     * 0:第一个元素, 1:第二个元素, -1:最后一个, -2:倒数第二个...
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $start
     * @param int $end
     *
     * @return mixed
     */
    public static function lRange($prefix, $key, $start, $end)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->lRange($key, $start, $end);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 返回某个KEY的过期时间
     * 返回设置过过期时间-1表示不存在,或者未设置过期时间
     *
     * @param int $prefix
     * @param string $key
     * return int
     */
    public static function ttl($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->ttl($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 为某个key设置过期时间,同setTimeout
     *
     * @param string $prefix
     * @param string $key
     * @param int $expire
     *
     * @return mixed
     */
    public static function expire($prefix, $key, $expire)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->expire($key, $expire);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 检测key是否存在
     *
     * @access public
     *
     * @param string $prefix
     * @param string $key
     *
     * @return bool
     */
    public static function exists($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->exists($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return false;
    }

    /**
     * Removes the first count occurences of the value element from the list.
     * If count is zero, all the matching elements are removed.
     * If count is negative, elements are removed from tail to head.
     *
     * @access public
     *
     * @param mixed $prefix
     * @param mixed $key
     * @param mixed $value
     * @param int $count
     */
    public static function lremove($prefix, $key, $value, $count = 0)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->lRemove($key, $value, $count);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Increment the number stored at key by offset
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $offet
     *
     * @return mixed
     */
    public static function incr($prefix, $key, $offet = 1)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                if ($offet > 1) {
                    return $redis->incrBy($key, $offet);
                } else {
                    return $redis->incr($key);
                }
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Decrement the number stored at key by offset
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $offet
     *
     * @return mixed
     */
    public static function decr($prefix, $key, $offet = 1)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                if ($offet > 1) {
                    return $redis->decrBy($key, $offet);
                } else {
                    return $redis->decr($key);
                }
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 根据field的类型返回不同种类的数据
     * $field = null 返回一整行
     * $field is array 返回指定列数组
     * $field is string 返回指定列的值
     *
     * @param string $prefix
     * @param string $key
     * @param array|string|null $field
     * @return array|string
     */
    public static function getHash($prefix, $key, $field)
    {
        $key = self::getKey($key, $prefix);
        $redis = self::init(true);
        if ($redis) {
            try {
                if (!$redis->exists($key)) return null;
                if ($field === null || $field === '') {
                    return $redis->hGetAll($key);
                }

                if (is_array($field)) {
                    $ret = $redis->hMGet($key, $field);
                    array_walk($ret, function (&$val) {
                        if ($val === false) $val = null;
                    });
                    return $ret;
                }

                $ret = $redis->hGet($key, $field); //直接返回单个数据
                return $ret === false ? null : $ret;
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 设置一个哈希表元素
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param string $hashKey
     * @param mixed $value
     *
     * @return mixed
     */
    public static function hset($prefix, $key, $hashKey, $value)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->hSet($key, $hashKey, $value);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 设置多个哈希表元素
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param array $values
     *
     * @return mixed
     */
    public static function hmset($prefix, $key, $values)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->hMset($key, $values);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * @param string $name 缓存变量名
     * @param array $array 存储数组数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public static function hMSetEx($name, $array, $expire = null)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                if (null === $expire) {
                    return $redis->hMset($name, $array);
                }

                return $redis->hMset($name, $array) && $redis->expire($name, $expire);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * @param string $keyPrefix 键前缀
     * @param null|string|array $keys 支持多个，可以是数组也可以是字符串（字符串id以逗号分隔）
     * @param null|string $field 属性名(如：TDevice.LastTime)，默认删除key的所有数据
     * @return bool 成功为true
     */
    public static function mDel($keyPrefix, $keys, $field = null)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $pipe = $redis->multi(Redis::PIPELINE);

                foreach ($keys as $key) {
                    $allKey = $keyPrefix . $key;
                    if ($field === null) {
                        $pipe->del($allKey);
                    } else {
                        $pipe->hDel($allKey, $field);
                    }
                }
                $pipe->exec();
                return true;
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 获取单个哈希表元素
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param string $hashKey
     *
     * @return mixed
     */
    public static function hget($prefix, $key, $hashKey)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->hGet($key, $hashKey);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 获取哈希表全部元素
     * @static
     *
     * @param string $prefix
     * @param string $key
     *
     * @return null
     */
    public static function hgetAll($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->hGetAll($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 获取哈希表长度
     * @static
     *
     * @param string $prefix
     * @param string $key
     *
     * @return mixed
     */
    public static function hLen($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->hLen($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Add one or more members to a sorted set or update its score if it already exists
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $score
     * @param mixed $value
     *
     * @return mixed
     */
    public static function zAdd($prefix, $key, $score, $value)
    {
        $redis = self::init(true);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->zAdd($key, $score, $value);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Add multi members to a sorted set or update its score if it already exists
     *
     * @param string $prefix
     * @param string $key
     * @param array $member_score_list
     *
     * @return mixed|null
     */
    public static function zMultiAdd($prefix, $key, $member_score_list)
    {
        $redis = self::init(true);

        if ($redis) {
            try {
                $params[] = self::getKey($key, $prefix);

                foreach ($member_score_list as $member => $score) {
                    $params[] = $score;
                    $params[] = $member;
                }

                return call_user_func_array([$redis, 'zAdd'], $params);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }

        return null;
    }

    /**
     * Returns a range of elements from the ordered set stored at the specified key
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $start
     * @param int $stop
     * @param bool $withscore
     *
     * @return mixed
     */
    public static function zRange($prefix, $key, $start = 0, $stop = -1, $withscore = false)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->zRange($key, $start, $stop, $withscore);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Returns a range of elements from the ordered set stored at the specified key
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $start
     * @param int $stop
     * @param bool $withscore
     *
     * @return mixed
     */
    public static function zrevrange($prefix, $key, $start = 0, $stop = -1, $withscore = false)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->zRevRange($key, $start, $stop, $withscore);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Returns the elements of the sorted set stored at the specified key
     * which have scores in the range [start,end].
     * @static
     *
     * @param string $prefix
     * @param string $key
     * @param int $start score
     * @param int $end score
     * @param bool $reverse
     * @param array $options withscores => TRUE, limit => array($offset, $count)
     *
     * @return mixed
     */
    public static function zRangeByScore($prefix, $key, $start, $end, $reverse = false, $options = [])
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                if ($reverse) {
                    return $redis->zRevRangeByScore($key, $end, $start, $options);
                }
                return $redis->zRangeByScore($key, $start, $end, $options);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Returns the score of a given member in the specified sorted set
     *
     * @param string $prefix
     * @param string $key
     * @param mixed $member
     *
     * @return float|null
     */
    public static function zScore($prefix, $key, $member)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->zScore($key, $member);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 获取key
     *
     * @param $prefix
     *
     * @return float|null
     */
    public static function keys($prefix)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                return $redis->keys('*' . $prefix . '*');
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Returns the cardinality of an ordered set.
     *
     * @param string $prefix
     * @param string $key
     *
     * @return int|null
     */
    public static function zCard($prefix, $key)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->zCard($key);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * Returns the score of a given member in the specified sorted set
     *
     * @param string $prefix
     * @param string $key
     * @param mixed $member
     *
     * @return float|null
     */
    public static function zRevRank($prefix, $key, $member)
    {
        $redis = self::init(false);
        if ($redis) {
            try {
                $key = self::getKey($key, $prefix);
                return $redis->zRevRank($key, $member);
            } catch (Exception $e) {
                self::errorLog($e);
            }
        }
        return null;
    }

    /**
     * 静态魔术方法调用方法
     * @static
     *
     * @param string $name
     * @param array $args
     *
     * @throws Exception
     * @return bool|mixed
     */
    public static function __callStatic($name, $args)
    {
        $prefix = isset($args[0]) ? $args[0] : false;
        $key = isset($args[1]) ? $args[1] : false;
        try {
            if ($prefix && $key) {
                $redis = self::init(true);
                if ($redis && method_exists($redis, $name)) {
                    unset($args[0]);
                    $args[1] = self::getKey($key, $prefix);
                    return call_user_func_array([$redis, $name], $args);
                } else {
                    throw new Exception("Unkown redis method: {$name}", 202);
                }
            } else {
                throw new Exception('Unspecified key or prefix', 203);
            }
        } catch (Exception $e) {
            self::$errno = $e->getCode();
            self::$error = $e->getMessage();
            self::errorLog();
            return null;
        }
    }

    /**
     * ping连接
     * @static
     *
     * @param $index
     *
     * @return bool
     */
    public static function ping($index)
    {
        try {
            if (empty(self::$redis[$index])) {
                return false;
            }
            return self::$redis[$index]->ping() == '+PONG';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 关闭连接
     * @static
     */
    public static function close($index = null)
    {
        if (!empty(self::$redis)) {
            if (isset($index)) {
                if (isset(self::$redis[$index])) {
                    self::$redis[$index]->close();
                    self::$redis[$index] = null;
                }
            } else {
                foreach (self::$redis as $index => $redis) {
                    $redis->close();
                    self::$redis[$index] = null;
                }
            }
        }
    }

    /**
     * 记录日志
     * @param $e Exception
     */
    public static function errorLog($e = null)
    {
        $msg = $e ? $e->getMessage() : self::$error;
        $code = $e ? $e->getCode() : self::$errno;
        $dbt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
        $message = "redis method: {$caller}, " . " ErrorCode:" . $code . "\r\nErrorMessage:" . $msg . "\r\n";
        cutil_php_log($message, 'redis');
    }
}
