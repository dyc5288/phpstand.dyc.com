<?php

/**
 * Description: 队列操作
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: lib_queue.php 156564 2021-09-13 15:29:33Z duanyc $
 */

!defined('IN_INIT') && exit('Access Denied');

class lib_queue
{
    /**
     * 添加队列
     *
     * @param $queue_name
     * @param $params
     * @param int $times
     */
    public static function addJob($queue_name, $params, $times = 3)
    {
        $result = false;

        for ($i = 0; $i < $times; $i++) {
            $result = lib_redis::lpush('QUEUE', strtoupper($queue_name), json_encode($params));

            if ($result) {
                return $result;
            }
        }

        return $result;
    }

    /**
     * 执行队列
     *
     * @param $queue_name
     */
    public static function doJob($queue_name)
    {
        if (empty($queue_name)) {
            exit(">> queue_name not empty\n\n");
        }

        $GLOBALS['JobStartTime'] = microtime(true);
        $func_file = PATH_ROOT . "/worker/QUEUE_{$queue_name}.php";

        if (!is_file($func_file)) {
            exit(">> queue_name not exist: {$func_file}\n\n");
        }

        //注册信号处理器
        self::_sigRegister();

        while (true) {
            pcntl_signal_dispatch();
            try {
                $params = null;
                try {
                    if (empty($GLOBALS['CONFIG']['queue'][$queue_name])) {
                        throw new Exception("no config {$queue_name}");
                    }
                    $timeout = $GLOBALS['CONFIG']['queue'][$queue_name]['timeout'];
                    // 提前5秒退出
                    if (microtime(true) - $GLOBALS['JobStartTime'] > $timeout * 60 - 5) {
                        cutil_php_log("{$queue_name} exit!", 'redisQueue');
                        exit();
                    }
                    $param = lib_redis::rpop('QUEUE', strtoupper($queue_name));
                    if (is_string($param) && strlen($param) > 0) {
                        $params = @json_decode($param, true);
                    }
                } catch (Exception $e) {
                    throw new Exception("QUEUE close recontect:" . $e->getMessage(), $e->getCode());
                }

                if (!empty($params)) {
                    $result = self::process(new cls_queue($func_file, $params));

                    if (empty($result) && $params['run_count'] < 3) {
                        $params['run_count'] = isset($params['run_count']) ? $params['run_count'] + 1 : 1;
                        lib_redis::lpush('QUEUE', strtoupper($queue_name), json_encode($params));
                    }

                    self::setInfo(">> memory usage: " . memory_get_usage(true));
                } else {
                    sleep(1);
                }
            } catch (Exception $e) {
                self::setError('error:', $e);
            }
        }
    }

    /**
     * 处理信号
     *
     * @param $sig
     */
    public static function sigHandler($sig)
    {
        exit("\n" . date("Y-m-d H:i:s") . ">> Process was terminated by signal {$sig}");
    }

    /**
     * 分发信号处理器
     */
    private static function _sigRegister()
    {
        pcntl_signal(SIGTERM, ['self', "sigHandler"]);
        pcntl_signal(SIGHUP, ['self', "sigHandler"]);
        pcntl_signal(SIGINT, ['self', "sigHandler"]);
        pcntl_signal(SIGQUIT, ['self', "sigHandler"]);
        pcntl_signal(SIGTSTP, ['self', "sigHandler"]);
    }

    /**
     * 多进程执行脚本
     *
     * @param cls_queue $input
     * @param bool $wifexited 是否等待子进程退出信号。如果要开启多线程模式，这里要设为false
     *
     * @throws Exception
     * @return mixed
     */
    public static function process($input, $wifexited = true)
    {
        if (empty($input) || !$input instanceof cls_queue) {
            return false;
        }
        //创建子进程
        $pid = pcntl_fork();
        //子进程
        if ($pid == 0) {
            $pid = posix_getpid();
            self::setInfo(sprintf("%s> Sub process {$pid} was created, and Executed:\n\n", date('Y-m-d H:i:s')));
            $result = $input->execute();
            if ($result) {
                exit();//标识正常退出
            }
            posix_kill($pid, SIGQUIT);
            return false;
        } elseif ($pid > 0) { //主进程
            if (!$wifexited) {
                return true;
            }
            //等待子进程执行完毕，取得子进程结束状态
            $pid = pcntl_wait($status, WUNTRACED);
            self::setInfo(sprintf("\n%s> Sub process: {$pid} exited with status {$status}\n", date('Y-m-d H:i:s')));
            if ($status == 65280) {
                self::setInfo(sprintf("\n%s> Fatal error in the input source\n\n", date('Y-m-d H:i:s')));
                return false;
            }
            return pcntl_wifexited($status);
        } else { //创建失败
            self::setInfo(sprintf("\n%s> Error to create sub process\n", date('Y-m-d H:i:s')));
            exit;
        }
    }

    /**
     * 打印信息
     *
     * @param $msg
     */
    private static function setInfo($msg)
    {
        printf($msg);
        cutil_php_log($msg, 'redisQueue');
    }

    /**
     * 设置错误
     *
     * @param $msg string
     * @param $e Exception
     */
    private static function setError($msg, $e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        cutil_php_log("{$msg} error:{$message}, code:{$code}", 'redisQueue');
    }
}
