<?php
/**
 * Description: ��ʱ��ʵ��
 * User: duanyc@infogo.com.cn
 * Date: 2021/9/13 17:07
 * Version: $Id$
 */

/* ����ؼ����� */
define('PATH_CUR', __DIR__);

/** ��ʼ�� */
require PATH_CUR . '/../init.php';


class Test
{
    /**
     * ����
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