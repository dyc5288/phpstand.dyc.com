<?php

/**
 * Description: 时间处理(兼容之前的方式)
 * 原来的处理方式不支持嵌套调用，且代码啰嗦，优化之后特此加上文件注释
 * User: renchen
 * Date: 2019/6/8 10:00
 * Version: $Id: cls_timer.php 148815 2021-07-02 08:37:32Z duanyc $.
 */

class cls_timer
{
    public $StartTime = 0;
    public $StopTime = 0;
    public $TimeSpent = 0;
    /**
     * 记录开始时间的数组，主要用于记录嵌套的时间区间
     * @var array
     */
    public $startTimeArr = array();
    /**
     * 记录结束时间的数组，主要用于记录嵌套的时间区间
     * @var array
     */
    public $stopTimeArr = array();

    /**
     * 存储字节转换单位.
     *
     * @var int[]
     */
    private static $sizes = array(
        'GB' => 1073741824,
        'MB' => 1048576,
        'KB' => 1024,
    );

    /**
     * 记录开始时间.
     *
     * @param string|int $function 计算子区间的标识，用于算出嵌套的时间消耗
     */
    public function start($function = ''):void
    {
        if ($function) {
            $this->startTimeArr[$function] = microtime(true);
        }
        $this->StartTime = microtime(true);
    }

    /**
     * 记录结束时间.
     *
     * @param string | int $function 计算子区间的标识，用于算出嵌套的时间消耗
     * @return float|int
     */
    public function stop($function = '')
    {
        if ($function) {
            $this->stopTimeArr[$function] = microtime(true);
        }
        $this->StopTime = microtime(true);
        return $this->spent($function);
    }

    /**
     * 计算所消耗的时间.
     *
     * @param string|int $function 计算子区间的标识。用于算出嵌套的时间消耗
     *
     * @return float|int
     */
    public function spent($function = '')
    {
        if ($this->TimeSpent && $function === '') {
            return $this->TimeSpent;
        }

        if ($function && isset($this->startTimeArr[$function], $this->stopTimeArr[$function])) {
            $this->TimeSpent = $this->stopTimeArr[$function] - $this->startTimeArr[$function];
        } else {
            $this->TimeSpent = $this->StopTime - $this->StartTime;
        }

        return round($this->TimeSpent, 8);
    }


    /**
     * 获取已使用的内存大小.
     * @param string $type peak,为获取峰值内存，其他值表示当前分配的内存
     * @return string
     */
    public function getMemory($type = 'peak'):string
    {
        return self::bytesToString($type==='peak' ? memory_get_peak_usage(true) : memory_get_usage(true));
    }

    /**
     * 字节转换.
     *
     * @param $bytes
     * @return string
     */
    public static function bytesToString($bytes):string
    {
        foreach (self::$sizes as $unit => $value) {
            if ($bytes >= $value) {
                return \sprintf('%.2f %s', $bytes >= 1024 ? $bytes / $value : $bytes, $unit);
            }
        }

        return $bytes . ' byte' . ((int) $bytes !== 1 ? 's' : '');
    }
}
