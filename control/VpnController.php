<?php

/**
 * Description: vpn相关
 * User: duanyc@infogo.com.cn
 * Date: 2021/07/5 15:53
 * Version: $Id: VpnController.php 153494 2021-08-19 01:25:07Z duanyc $
 */

!defined('IN_INIT') && exit('Access Denied');
include PATH_CONTROL . "/BaseController.php";

class VpnController extends BaseController
{

    /**
     * 判断当前ip是否在一个IP范围之内，对应老交易 getvpninfo
     *
     * @return mixed
     * @throws Exception
     */
    public function check()
    {
        $requestType = request('requestType', 'request');
        hlp_check::checkEmpty($requestType);
        $deviceId = request('deviceid', 'request', 0, 'int');
        $ip = getRemoteAddress();
        hlp_check::checkEmpty($ip);
        VpnServiceProvider::checkVpnIpRange($deviceId, $ip);
        $this->errmsg = L(21140001);
        return [];
    }

    /**
     * VPN更新设备信息，对应老交易 vpnupdevinfo
     *
     * @return mixed
     * @throws Exception
     */
    public function upDevinfo()
    {
        $requestType = request('requestType', 'request');
        $ip = request('ip', 'request');
        $deviceId = request('deviceid', 'request', 0, 'int');
        hlp_check::checkEmpty($ip);
        hlp_check::checkEmpty($deviceId);

        if ($requestType === "mClient" && $ip !== "") {
            VpnServiceProvider::updateDeviceInfo($deviceId, $ip);
            $this->errmsg = L(21140005);
        } else {
            T(21100002);
        }

        return [];
    }
}
