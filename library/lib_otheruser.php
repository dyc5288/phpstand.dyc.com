<?php

/**
 * Description: 第三方企业微信和钉钉
 * User: duanyc@infogo.com.cn
 * Date: 2021/05/20 17:46
 * Version: $Id: lib_otheruser.php 171338 2022-03-17 09:20:21Z zhangjc $
 */
!defined('IN_INIT') && exit('Access Denied');

class lib_otheruser
{
    /**
     * 获取第三方用户信息
     *
     * @param $userType
     * @param $managerIp
     * @param $port
     * @return mixed
     * @throws Exception
     */
    public static function getInfo($userType, $managerIp, $port)
    {
        define("NOWASMIP", $managerIp);
        define("HTTPPORT", $port);
        $int_file = PATH_LIBRARY . "/otheruser/{$userType}.class.php";

        if (!file_exists($int_file)) {
            T(21120011);
        }

        include_once($int_file);
        if (!class_exists($userType, false)) {
            T(21120012);
        }
        $int_obj = new $userType();
        if (!method_exists($int_obj, 'main')) {
            T(21120012);
        }
        return $int_obj->main();
    }

    /**
     * 获取第三方对象
     *
     * @param $userType
     * @return mixed
     * @throws Exception
     */
    public static function getInstance($userType, $params = null)
    {
        $int_file = PATH_LIBRARY . "/otheruser/{$userType}.class.php";

        if (!file_exists($int_file)) {
            T(21120011);
        }

        include_once($int_file);
        if (!class_exists($userType, false)) {
            T(21120012);
        }
        if (!is_null($params)) {
            return new $userType($params);
        }
        return new $userType();
    }
}
