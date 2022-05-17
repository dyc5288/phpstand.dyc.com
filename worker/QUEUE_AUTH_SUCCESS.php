<?php

/**
 * Description: 认证成功队列
 * User: duanyc@infogo.com.cn
 * Date: 2021/09/13 15:53
 * Version: $Id: QUEUE_AUTH_SUCCESS.php 156563 2021-09-13 15:04:16Z duanyc $
 */
if (!defined('IN_ACCESS')) {
    exit('Access Denied!');
}

if (PHP_SAPI != 'cli' || !isset($this) || !$this instanceof cls_queue) {
    exit();
}

/**
 * 任务主函数
 *
 * @param $params
 */
if (!function_exists('QUEUE_AUTH_SUCCESS')) {
    /**
     * 认证成功处理
     *
     * @param $params
     *
     * @return bool
     */
    function QUEUE_AUTH_SUCCESS($params)
    {
        cutil_php_log("AUTH_SUCCESS:" . var_export($params, true), 'net_auth');
        if (empty($params['deviceId'])) {
            return false;
        }
        AuthServiceProvider::tacticsChangeSel($params['deviceId']);
        return true;
    }
}

QUEUE_AUTH_SUCCESS($this->params);
