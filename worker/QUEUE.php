<?php

/**
 * Description: 执行队列
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: QUEUE.php 156563 2021-09-13 15:04:16Z duanyc $
 */
define('PATH_ROOT_CLI', strtr(__FILE__, ['\\' => '/', '/worker/QUEUE.php' => '', 'QUEUE.php' => '']));
define('IN_ACCESS', true);
include PATH_ROOT_CLI . '/init.php';

/* 调试模式 */
$flag = hlp_common::getCmdFlag();

if (isset($flag['help']) || empty($flag)) {
    printf("php QUEUE.php -test 1 : test addJob data." . PHP_EOL);
    printf("php QUEUE.php -name MYTEST : run doJob." . PHP_EOL);
    printf(PHP_EOL);
    exit();
}

if (!isset($flag['name'])) {
    printf("please input name : queue name." . PHP_EOL);
    printf(PHP_EOL);
    exit();
}

// 队列名
$queueName = $flag['name'];

if (!isset($GLOBALS['CONFIG']['queue'][$queueName])) {
    printf("queue {$queueName} not config." . PHP_EOL);
    exit();
}

if (!empty($flag['test'])) {
    $count = $flag['test'];
    unset($flag['test']);
    unset($flag['name']);
    for ($i = 0; $i < $count; $i++) {
        lib_queue::addJob($queueName, $flag);
        //lib_mosquitto::publish($queueName, $params);
    }
    var_dump($flag);
    exit();
}

// 订阅主题
lib_queue::doJob($queueName);
//lib_mosquitto::subscribe($queueName);
