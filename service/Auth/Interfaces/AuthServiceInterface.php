<?php
/**
 * Description: 认证服务
 * User: duanyc@infogo.com.cn
 * Date: 2021/2/18
 * Version: $Id: AuthServiceInterface.php 158480 2021-10-08 08:34:33Z huyf $
 */

namespace Services\Auth\Interfaces;

interface AuthServiceInterface
{
    /**
     * DeviceServiceInterface constructor.
     * @param $params
     */
    public function __construct($params);

    /**
     * 验证前处理
     *
     * @return bool
     */
    public function authBefore();

    /**
     * 验证
     *
     * @return bool
     */
    public function auth();

    /**
     * 解析参数
     *
     * @return bool
     */
    public function parseParams();

    /**
     * 检查帐号
     *
     * @param $data
     * @param $authType
     * @return bool
     */
    public function checkUser($data, $authType);

    /**
     * 认证后处理
     *
     * @param $data
     * @param $userInfo
     * @param $authType
     * @return int
     */
    public function authAfter(&$data, $userInfo, $authType);

    /**
     * 获取验证服务
     *
     * @return int
     */
    public function getAuthServer();

    /**
     * 返回认证信息
     *
     * @param $defRoleId
     *
     * @return mixed
     */
    public function getAuthDataFromDevice($defRoleId);

    /**
     * 记录认证失败记录
     *
     * @param $authType
     * @param $message
     *
     * @return mixed
     */
    public function recordAuthErrLog($authType, $message);

    /**
     * 记录认证成功记录
     *
     * @param $authType
     * @param $roleId
     * @param $remark
     *
     * @return mixed
     */
    public function recordAuthSucLog($authType, $roleId, $remark);

    /**
     * 验证设备用户绑定检查 原CheckDeviceBindUser方法
     *
     * @param $authType
     * @throws \Exception
     */
    public function checkDeviceBindUser($authType);
}
