<?php
/**
 * Description: 认证接口暴露
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: AuthServiceProvider.php 174739 2022-04-28 05:13:50Z duanyc $
 */
use Services\Auth\Services;
use Services\Common\Services\RoleService;
use Services\Common\Services\DepartService;

class AuthServiceProvider extends BaseServiceProvider
{

    /**
     * 初始化认证服务
     *
     * @param $params
     *
     * @return Object|Services\AdAutoAuthService|Services\AdDomainAuthService|Services\DingTalkAuthService|Services\EmailAuthService|Services\FingerAuthService|Services\GuestAuthService|Services\LdapAuthService|Services\MacAuthService|Services\MobileAuthService|Services\NoAuthService|Services\RadiusAuthService|Services\SelfGuestAuthService|Services\SmsAuthService|Services\UKeyAuthService|Services\UserAuthService|Services\WebAuthService|Services\WechatAuthService|Services\WeWorkAuthService|Services\FeiShuAuthService|Services\SsoAuthService
     * @throws Exception
     */
    public static function initAuthService($params)
    {
        $getType = $params['servicePrefix'];
        $serviceClass = '\\Services\\Auth\\Services\\' . $getType . 'AuthService';
        return (new ReflectionClass($serviceClass))->newInstance($params);
    }

    /**
     * 按照传入的顺序验证多个认证服务
     *
     * @param $authServers
     * @param $params
     * @param $authService Object|Services\BaseAuthService
     *
     * @return bool|object
     * @throws Exception
     */
    public static function validateAuthServer($authServers, &$params, $authService)
    {
        if (empty($authServers)) {
            return false;
        }

        include_config('auth');
        $return = null;
        $exception = null;
        $allMessage = [];
        foreach ($authServers as $server) {
            if (!isset($GLOBALS['AUTH_CONFIG']['Service'][$server])) {
                T(21120010);
            }

            $getType = $GLOBALS['AUTH_CONFIG']['Service'][$server];
            $serviceClass = '\\Services\\Auth\\Services\\' . $getType . 'AuthService';
            $service = (new ReflectionClass($serviceClass))->newInstance($params);

            try {
                $service->setBaseDeviceInfo($authService->getBaseDeviceInfo());
                $service->setOnlineDeviceInfo($authService->getOnlineDeviceInfo());
                $return = $service->auth();
                $params = $service->getParams();
                return $return;
            } catch (Exception $e) {
                // 默认返回首选认证方式提示
                if (empty($exception)) {
                    $exception = $e;
                    $params = $service->getParams();
                }
                // 已识别用户类型时，优先返回该认证方式提示
                $userinfo = $service->getUserInfo();
                if (!empty($userinfo)) {
                    $exception = $e;
                    $params = $service->getParams();
                }
                $allMessage[] = $service->getUserTypeName() . "：" . $e->getMessage();
            }
        }

        if (count($allMessage) > 1) {
            $params['allMessage'] = implode(' ', $allMessage);
        }

        if (!$return && !empty($exception)) {
            throw $exception;
        }

        return $return;
    }

    /**
     * 补充公共数据信息
     *
     * @param $data
     * @param $userInfo
     * @param $params
     * @throws Exception
     */
    public static function setCommonInfo(&$data, $userInfo, $params)
    {
        //通过auth_interface.php进入来自于8021x调用则双因子认证 自动失效
        if ($params['callfrom'] === '8021X') {
            $data['FactorAuth'] = false;
        } else {
            $data['FactorAuth'] = $params['factorAuth'] ?? false;
        }
        $data['IsAgainReg'] = (string)self::getIsAgainReg($data['RoleID'], $data['DeviceID']);
        //分析检查参数
        unset($data['CheckParam'], $data['Password']);
        //获取部门信息
        $DepartID = $data["DepartID"] ?? 0;
        $departInfo = DepartModel::getOne($DepartID, 'one');
        $data["DepartName"] = $departInfo["DepartName"];
        $data["UpID"] = $departInfo["UpID"];
        $data["AllDepartID"] = $departInfo["AllDepartID"];
        $departService = new DepartService();
        $rootDepartID = $departService->getRootDepartID($data["DepartID"]);
        if ($rootDepartID) {
            $data['RootDepartID'] = $rootDepartID;
        }
        $data["TrueNames"] = !empty($data["TrueNames"]) ? $data["TrueNames"] : $userInfo['TrueNames'];
        $data["nowTime"] = date("Y-m-d H:i:s");
        $data["AuthTime"] = $userInfo['AuthTime'];
        $aBridge = read_inifile(PATH_TBRIDGE_PRIVATE);
        $data['BRIDGE_TYPE'] = $aBridge['BRIDGE_TYPE']; // 认证安检成功读取当前服务器的准入类型
        $data['Token'] = LoginServiceProvider::setSession($data);
        $qdata = ['deviceId' => $data['DeviceID'], 'Token' => $data['Token'], 'UserID' => $data['UserID']];
        lib_queue::addJob('AUTH_SUCCESS', $qdata);
    }

    /**
     * 获取信息
     *
     * @param $routeType
     * @param $managerIp
     * @param $port
     * @return mixed
     * @throws Exception
     */
    public static function getOtherUserInfo($routeType, $managerIp, $port)
    {
        if (!in_array($routeType, ['wework', 'dingtalk', 'wechat', 'feishu'])) {
            return false;
        }

        return lib_otheruser::getInfo($routeType, $managerIp, $port);
    }

    /**
     * 根据用户ID获取用户名
     *
     * @param $userId
     *
     * @return bool
     */
    public static function getUserNameById($userId)
    {
        if (empty($userId)) {
            return false;
        }

        $user = AuthUserModel::getOne($userId, 'base');

        if (empty($user)) {
            return false;
        }

        return $user['UserName'];
    }

    /**
     * 获取角色信息
     *
     * @param $roleID
     *
     * @return array|bool
     */
    public static function getRoleInfo($roleID)
    {
        $roleService = new RoleService();
        $data = $roleService->getRoleInfo($roleID);
        return $data;
    }

    /**
     * 获取设备在线信息
     *
     * @param $deviceid
     *
     * @throws Exception
     */
    public static function getOnlineInfo($deviceid)
    {
        $onlineDevice = NacOnLineDeviceModel::getOne($deviceid, 'user');
        if (empty($onlineDevice['RoleID'])) {
            T(21120036);
        }
        $roleService = new RoleService();
        $aRoleData = $roleService->getRoleInfo($onlineDevice['RoleID'], 0);
        $onlineDevice = array_merge($onlineDevice, $aRoleData);
        //小助手保活时间
        $AuthInterval = $aRoleData['AuthInterval'];
        $aIntTime = strtotime($onlineDevice['LastHeartTime']);
        $aNowTime = time();
        $aHowLongNoHeart = $aNowTime - $aIntTime;
        if ($aHowLongNoHeart > ($AuthInterval * 60)) {
            T(21120035);
        }
        return $onlineDevice;
    }

    /**
     * 已自动审核的设备需要重新审核则返回未注册的状态
     *
     * @param int $roleID 当前角色id
     * @param int $deviceId 设备id
     *
     * @return int  1:需要 0:不需要
     */
    public static function getIsAgainReg($roleID, $deviceId)
    {
        $roleService = new RoleService();
        $roleInfo = $roleService->getRoleInfo($roleID);
        $ClientCheckInfo = DictModel::getAll("ClientCheck");
        $devIsAgainReg = DeviceAuditLogModel::getJoinComputer($deviceId);
        $devIsAgainReg = !empty($devIsAgainReg) ? 0 : 1;
        if ($ClientCheckInfo['reAudit'] == '1' && $roleInfo['IsNeedAuto'] == '1' && $devIsAgainReg) {
            return 1;
        }
        return 0;
    }

    /**
     * 获取用户
     *
     * @param $userType
     * @param $userName
     *
     * @return array|bool
     */
    public static function getUserByUserName($userType, $userName)
    {
        if (empty($userType) || empty($userName)) {
            return false;
        }

        return AuthUserModel::getOneByUserName($userType, $userName);
    }

    /**
     * 是否必须修改密码
     *
     * @param $username
     *
     * @return bool
     */
    public static function isMustChangePasswrod($username)
    {
        if (empty($username)) {
            return false;
        }

        $userinfo = AuthUserModel::getOneByUserName('User', $username, 'auth');

        if (!empty($userinfo)) {
            return $userinfo['MustChange'];
        }

        return false;
    }

    /**
     * 获取微信状态
     *
     * @param $ip
     * @param $otheruserid
     *
     * @return string c关闭 o开启 a自动认证
     */
    public static function getWechatStatus($ip, $otheruserid)
    {
        //获取微信配置
        $wechatconfig = DictModel::getAll("WeChatConfig");
        $wechat = "c";//c 关闭 o开启 a自动认证
        //是否开启微信推广
        if ($wechatconfig['openwechat'] == '1') {
            if (VpnIpRange($ip, $wechatconfig['clientips'], '|@@|')) {
                $wechat = 'o';
            }
            if ($otheruserid) {
                // 是否从微信接口进入 识别当天之内的id，防止没有通过微信接口非法进入页面
                $res = OtherUserModel::getSingle(['ID' => $otheruserid, 'LikeLastUpTime' => date("Y-m-d")]);
                if ($res['subscribe'] == '1') {
                    $wechat = 'a';
                }
            }
        }
        return $wechat;
    }

    /**
     * 策略变更通知
     *
     * @param $deviceId
     */
    public static function tacticsChangeSel($deviceId)
    {
        if (empty($deviceId)) {
            return;
        }

        $dataList = NacOnLineDeviceModel::getRolePolicyList($deviceId);
        if (!empty($dataList)) {
            $insertParams = [];
            foreach ($dataList as $row) {
                $PolicyID = $row['PolicyID'] ?? 0;
                $insertParams[] = ['DeviceID' => $row['DeviceID'], 'RoleID' => $row['RoleID'], 'PolicyID' => $PolicyID];
            }
            PolicyChangeModel::delete(['DeviceID' => $deviceId]);
            PolicyChangeModel::insertPatch($insertParams);
        }
    }

    /**
     * 认证后，同步账号信息到设备
     *
     * @param $deviceId int
     * @param $userinfo array
     *
     */
    public static function syncDeviceInfo($deviceId, $userinfo)
    {
        if (empty($deviceId)) {
            return;
        }

        $dparams = ['UserName' => $userinfo['name'], 'Tel' => $userinfo['mobile'], 'EMail' => $userinfo['email']];
        \DeviceModel::update($deviceId, $dparams);
    }
}
