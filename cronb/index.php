<?php

/**
 * Description: 定时器
 * User: duanyc@infogo.com.cn
 * Date: 2021/08/24 15:53
 * Version: $Id: index.php 158081 2021-09-28 04:48:29Z duanyc $
 */
define('PATH_ROOT_CLI', strtr(__FILE__, ['\\' => '/', '/cronb/index.php' => '', 'index.php' => '']));
define('IN_ACCESS', true);
include PATH_ROOT_CLI . '/init.php';

/* 永不超时 */
ini_set('max_execution_time', 0);

/* 执行CROND */
crond();
exit();

/**
 * CROND函数
 */
function crond()
{
    include_config('cronb');
    $time = microtime(true);

    /* 提取要执行的文件 */
    $exe_file = array();

    foreach ($GLOBALS['CROND_TIMER']['the_format'] as $format)
    {
        $key = date($format, ceil($time));

        if (isset($GLOBALS['CROND_TIMER']['the_time'][$key]) && is_array(@$GLOBALS['CROND_TIMER']['the_time'][$key]))
        {
            foreach ($GLOBALS['CROND_TIMER']['the_time'][$key] as $file) {
                $exe_file[] = $file;
            }
        }
    }

    echo "\n" . date('Y-m-d H:i', time()), "\n\n";
    cutil_php_log('start crond: ', 'cronb');

    /* 加载要执行的文件 */
    foreach ($exe_file as $file)
    {
        cutil_php_log("start ". $file, 'cronb');
        echo '>> ', $file,"\n";
        //@include __THIS__ . '/' . $file;
        process_execute($file);
        echo "\n\n";
    }

    echo 'crond total: ', microtime(true) - $time . "\n";
    $use_time   = microtime(true) - $time;
    $sleep_time = intval(60 - $use_time - 5);

    if ($sleep_time > 0)
    {
        echo date('Y-m-d H:i:s') . " crond sleep {$sleep_time}s\n";
        //sleep($sleep_time); // 不用进程管理，则不睡眠
    }

    cutil_php_log("finish sleep {$sleep_time}", 'cronb');
}

/**
 * 多进程执行脚本
 * @param mixed $input
 */
function process_execute($input)
{
    //创建子进程
    $pid = pcntl_fork();

    //子进程
    if ($pid == 0)
    {
        $pid = posix_getpid();
        echo "** Sub process {$pid} was created for {$input}:\n\n";

        try {
            if (is_file(__DIR__ . '/' . $input))
            {
                if (!empty($args))
                {
                    extract($args);
                }

                $cronb = '/cronb/' . $input;
                $numCmd = "ps aux | grep '{$cronb}' | grep -v grep | wc -l";
                $num = intval(trim(cutil_exec_wait($numCmd)));

                if ($num < 1) {
                    $cmd = "php " . PATH_ROOT . $cronb;
                    cutil_php_log("run ". $cmd, 'cronb');
                    cutil_exec_no_wait($cmd);
                }
                exit();
            } else {
                if (empty($GLOBALS['CONFIG']['queue'][$input])) {
                    throw new Exception("queue {$input} no config.");
                }

                $work = '/worker/QUEUE.php -name ' . $input;
                $numCmd = "ps aux | grep '{$work}' | grep -v grep | wc -l";
                $num = intval(trim(cutil_exec_wait($numCmd)));

                if ($num < $GLOBALS['CONFIG']['queue'][$input]['num']) {
                    $cmd = "php " . PATH_ROOT . $work;
                    cutil_php_log("run ". $cmd, 'cronb');
                    cutil_exec_no_wait($cmd);
                }
                exit();
            }
        } catch (Exception $e) {
            cutil_php_log("run error". $e->getMessage(), 'cronb');
            echo "error:" . $e->getMessage() . PHP_EOL;
        }
    }
    //创建失败
    elseif ($pid < 0)
    {
        echo "\n** Error to create sub process\n";
        exit;
    }
}
