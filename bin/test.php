<?php
/**
 * Description: 测试
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: test.php 164922 2021-12-15 06:34:09Z duanyc $
 */

/* 定义关键常量 */
define('PATH_CUR', __DIR__);

/** 初始化 */
require PATH_CUR . '/../init.php';
try {
    $Token = '518059610f6f3f1a6611f3782a0c5d7f';
    $ResID = 2;
    $Session = LoginServiceProvider::getSessionInfo($Token, 'policy');
    $res = PolicyServiceProvider::checkPolicy($Session, $ResID);
    var_dump($res);die;
}
catch (Exception $e) {
    var_export($e->getMessage());
}