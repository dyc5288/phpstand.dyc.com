<?php
/**
 * Description: 定时器实例
 * User: duanyc@infogo.com.cn
 * Date: 2021/9/13 17:07
 * Version: $Id$
 */

/* 定义关键常量 */
define('PATH_CUR', __DIR__);

/** 初始化 */
require PATH_CUR . '/../init.php';


class Test
{
    /**
     * 测试
     * @param $flag
     */
    public function abc()
    {
        echo 'hello world!';
        cutil_php_log("hello world!", 'cronb');
    }
}

try
{
    $test = new Test();
    $test->abc();
}
catch (Exception $e)
{
    echo "code: " . $e->getCode() . ", message: ". $e->getMessage();
}