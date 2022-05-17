<?php

/**
 * Description: 路由相关
 * User: duanyc@infogo.com.cn
 * Date: 2021/07/5 15:53
 * Version: $Id: route.func.php 156876 2021-09-16 01:50:33Z duanyc $
 */

/**
 * 执行控制器返回结果
 *
 * @param $controller_name
 * @param $action
 *
 * @return array
 */
function run_ctl($controller_name, $action)
{
    $return = ['errcode' => '0', 'errmsg' => 'ok'];

    try {
        if (API_VERSION >= '1.0') {
            if (empty($GLOBALS['ROUTE']['csrfWhiteList'][$controller_name][$action])) {
                lib_request::csrfCheck();
            }
        } else {
            if (empty($GLOBALS['ROUTE']['csrfWhiteList'][$controller_name][$action])) {
                hlp_compatible::csrfCheck();
            }
        }
        $instance = new $controller_name();
        $result = $instance->$action();
        null_clear($result);

        if (API_VERSION < '1.0') {
            hlp_compatible::printData($result, 'success', $instance->errmsg);
        }

        if (isset($result['errcode'])) {
            $return = $result;
        } else {
            $return['data'] = is_array($result) ? $result : ['val' => $result];
            $return['errmsg'] = !empty($instance->errmsg) ? $instance->errmsg : 'ok';
        }
    } catch (Exception $e) {
        if (API_VERSION < '1.0') {
            hlp_compatible::printData([], 'fail', $e);
        }

        $code = $e->getCode();
        $return['errcode'] = !empty($code) ? strval($code) : '10000000';
        $return['errmsg'] = $e->getMessage();
        $return['data'] = $GLOBALS['CONFIG']['EXCEPTION_DATA'];
    }

    $requestConf = isset($_SERVER['HTTP_REQUESTCONF']) ? $_SERVER['HTTP_REQUESTCONF'] : '';
    header("requestConf: {$requestConf}");
    return $return;
}

/**
 * null处理
 *
 * @param $data
 */
function null_clear(&$data)
{
    if (is_array($data)) {
        foreach ($data as $key => $val) {
            null_clear($data[$key]);
        }
    } else if ($data === null) {
        $data = "";
    }
}

/**
 * 开启yar服务
 *
 * @param $className
 * @throws Exception
 */
function execute_yar($className)
{
    $path = PATH_ROOT . '/webroot/rpc/' . $className . '.php';
    if (is_file($path)) {
        require($path);
    } else {
        throw new Exception("{$className} is not exists!");
    }
    $control = new $className();
    $service = new Yar_Server($control);
    $service->handle();
    exit();
}

/**
 * 控制器调用函数
 *
 * @param $controller_name
 * @param $action
 *
 * @return void
 */
function execute_ctl($controller_name, $action = '')
{
    try {
        $action = empty($action) ? 'index' : $action;
        $controller_name = hlp_common::firstUpper($controller_name) . 'Controller';
        // 启用yar server
        $yarrpc = request('yarrpc', 'request', 'int', 0);
        if (!empty($yarrpc)) {
            execute_yar($controller_name);
        }
        $path = PATH_CONTROL . '/' . $controller_name . '.php';

        if (is_file($path)) {
            require($path);
        } else {
            throw new Exception("{$controller_name} is not exists!");
        }

        if (method_exists($controller_name, $action) === true) {
            $return = run_ctl($controller_name, $action);
            hlp_compatible::parseReturn($return);
            $return['encode'] = 'UTF-8';
            json_print($return);
        } else {
            throw new Exception("Method {$action}() is not exists!");
        }
    } catch (Exception $e) {
        cutil_php_log('control and method not find, error:' .$e->getMessage(), 'system');
        if (DEBUG_LEVEL === true) {
            exit($e->getMessage() . $e->getTraceAsString());
        } else {
            header('HTTP/1.1 403 File Not Exist', true, 403);
            json_print(['errcode' => '403', 'errmsg' => 'Control File Not Exist']);
        }
    }
}