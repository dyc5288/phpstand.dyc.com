<?php
/**
 * Description: 认证服务
 * User: duanyc@infogo.com.cn
 * Date: 2021/2/18
 * Version: $Id: BaseAuthService.php 171965 2022-03-25 07:04:18Z chenpan $
 */

namespace Services\Auth\Services;

use Services\Auth\Traits\BaseAuthServiceAfterTrait;
use Services\Auth\Traits\BaseAuthServiceBeforeTrait;
use Services\Auth\Traits\BaseAuthServiceExecuteTrait;
use Services\Common\Services\CommonService;
use Services\Common\Services\DepartService;
use Services\Common\Services\RoleService;

class BaseAuthService extends CommonService
{
    /**
     * 请求参数数组,和处理后的数据.
     *
     * @var array
     */
    protected $params;

    /**
     * 用户类型/认证方式
     * @var string
     */
    protected $userType = '';

    /**
     * 是否已识别用户，已识别该用户类型，则存储用户信息，用于给用户提示，优先该认证方式
     * @var array
     */
    protected $userInfo = [];

    /**
     * 认证方式名字
     * @var string
     */
    protected $userTypeName = '';

    /**
     * 设备id.
     *
     * @var int
     */
    protected $deviceId;

    /**
     * 设备信息.
     *
     * @var array
     */
    protected $deviceInfo;

    /**
     * 终端设备在线信息
     *
     * @var array
     */
    protected $onlineDeviceInfo;

    /**
     * 异常
     *
     * @var \Exception
     */
    protected $exception = null;

    /**
     * 角色公共服务
     *
     * @var RoleService
     */
    protected $roleService = null;

    /**
     * 部门公共服务
     *
     * @var DepartService
     */
    protected $departService = null;

    /**
     * 初始化
     *
     * @param $params
     */
    public function __construct($params)
    {
        parent::__construct();
        $this->logFileName = 'net_auth';
        $this->params = $params;
        $this->params['factorAuth'] = false;
        $this->params['authType'] = $this->userType;
        $this->deviceId = $params['deviceId'];
        $this->roleService = new RoleService();
        $this->departService = new DepartService();
    }

    // 入网前代码块
    use BaseAuthServiceBeforeTrait;

    // 入网中代码块
    use BaseAuthServiceExecuteTrait;

    // 入网后代码块
    use BaseAuthServiceAfterTrait;

    /**
     * 更新双因子认证记录
     * @throws \Exception
     */
    public function updateTwoFactorAuthLog()
    {
        // 802.1x时，认证记录由服务端维护
        if ($this->params['callfrom'] == AUTH_FROM_8021X && $this->params['isRecord'] == 0) {
            return;
        }
        $cache = cache_get_info('twoFactor', $this->deviceId);
        if (empty($cache)) {
            T(21120044);
        }
        $checkCode = empty($this->params['checkCode']) ? "" : $this->params['checkCode'];
        $successAuthLog = false;
        if (!empty($cache['RID'])) {
            $lcond = ['IsSuccess' => 0, 'RID' => $cache['RID'], 'column' => 'twoFactor'];
            $successAuthLog = \NacAuthLogModel::getSingle($lcond);
        }
        $cache['AuthCode'] = $checkCode;
        if (!empty($successAuthLog)) {
            $cache['IsSuccess'] = 1;
            $cache['FailReason'] = '';
            \NacAuthLogModel::update($cache['RID'], $cache);
        } else {
            unset($cache['RID']);
            \NacAuthLogModel::insert($cache);
        }
    }

    /**
     * TNacAuthLog中是否有未闭合的重复的认证记录,如果有就不插入(解决历史IP使用记录中有大量重复)
     *
     * @return bool
     */
    public function isHaveRepeatAuthLog()
    {
        //双因子认证中使用
        if ($this->params['isRecord'] == 0) {
            return true;
        }
        if (strlen($this->deviceId) <= 0) {
            return true;
        }
        // 更新最后在线离线时间
        $lastAuthID = $this->deviceInfo['LastAuthID'];
        if (!empty($lastAuthID)) {
            $authLog = \NacAuthLogModel::getOne($lastAuthID, 'offline');
            if (empty($authLog['OffLineTime'])) {
                \NacAuthLogModel::update($lastAuthID, ['OffLineTime' => 'now()']);
            }
        }
        return false;
    }

    /**
     * 记录认证成功记录
     *
     * @param $authType
     * @param $roleId
     * @param $remark
     * @param $userId
     *
     * @return mixed
     * @throws \Exception
     */
    public function recordAuthSucLog($authType, $roleId, $remark = '', $userId = false)
    {
        $checkCode = empty($this->params['checkCode']) ? "" : $this->params['checkCode'];
        $params = [
            'DeviceID' => $this->deviceId,
            'AuthType' => $authType,
            'subAuthType' => $this->params['subType'],
            'UserName' => trim($this->params['userName']),
            'RoleID' => $roleId,
            'Remark' => $remark,
            'InsertTime' => 'now()',
            'IP' => $this->deviceInfo['IP'],
            'MAC' => $this->deviceInfo['Mac'],
            'SwitchIP' => $this->deviceInfo['SwitchIP'] ?? '',
            'SwitchPort' => $this->deviceInfo['SwitchPort'] ?? '',
            'AuthCode' => $checkCode
        ];
        if ($this->params['factorAuth']) {
            $params['IsSuccess'] = STRING_FALSE;
            $params['FailReason'] = L(21120045);
        }
        if (!empty($this->params['UID'])) {
            $params['UID'] = $this->params['UID'];
        } else if (!empty($userId)) {
            $params['UID'] = $userId;
        }
        if (!$this->isHaveRepeatAuthLog()) {
            $lastLogid = \NacAuthLogModel::insert($params);
            cutil_php_log($params, $this->logFileName);
            $log_alert = L(21120009, $params);
            check_log_forward('UserAuthLog', $log_alert);
            $rparams = ['LastAuthID' => $lastLogid];
            \RelationComputerModel::update($this->deviceId, $rparams);
            cutil_php_log($rparams, $this->logFileName);

            cutil_php_log('AuthDeviceID is '.$this->deviceId, $this->logFileName);
        }
        if ($this->params['factorAuth']) {
            unset($params['IsSuccess'], $params['FailReason']);
            $params['RID'] = $lastLogid;
            cache_set_info('twoFactor', $this->deviceId, $params, 600);
        }
        return $lastLogid;
    }

    /**
     * 记录认证失败记录
     *
     * @param $authType
     * @param $message
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function recordAuthErrLog($authType, $message)
    {
        // 不记录认证失败记录
        if (!$this->params['isRecordErr']) {
            return;
        }

        $checkCode = empty($this->params['checkCode']) ? "" : $this->params['checkCode'];
        $params = [
            'DeviceID' => $this->deviceId,
            'AuthType' => $authType,
            'UserName' => trim($this->params['userName']),
            'RoleID' => 0,
            'OffLineTime' => 'now()',
            'InsertTime' => 'now()',
            'IP' => $this->deviceInfo['IP'],
            'MAC' => $this->deviceInfo['Mac'],
            'SwitchIP' => $this->deviceInfo['SwitchIP'],
            'SwitchPort' => $this->deviceInfo['SwitchPort'],
            'AuthCode' => $checkCode,
            'IsSuccess' => STRING_FALSE,
            'FailReason' => $message
        ];

        $isUpdate = $this->twoFactorAuthErrLog($params);
        if (!$isUpdate){
            \NacAuthLogModel::insert($params);
        }

        $log_alert = L(21120006, $params);
        cutil_php_log($params, $this->logFileName);
        check_log_forward('UserAuthLog', $log_alert);
    }

    /**
     * 双因子认证记录
     *
     * @param $params
     *
     * @return bool
     */
    public function twoFactorAuthErrLog(&$params)
    {
        if ($this->params['twoAuthType'] == 'TwoFactor') {
            $params['FailReason'] = L(21120043) . ": {$params['FailReason']}";
            $cache = cache_get_info('twoFactor', $this->deviceId);
            if (empty($cache['RID'])) {
                return false;
            }
            $lcond = ['DeviceID' => $this->deviceId, 'RID' => $cache['RID'], 'column' => 'twoFactor'];
            $successAuthLog = \NacAuthLogModel::getSingle($lcond);
            if (!empty($successAuthLog)) {
                \NacAuthLogModel::update($successAuthLog['RID'], $params);
                $cache['RID'] = '';
                cache_set_info('twoFactor', $this->deviceId, $cache, 600);
                return true;
            }
        }
        return false;
    }

    /**
     * 高级动态记录认证失败记录
     *
     * @param $AuthResult 1表示成功日志，0表示失败日志
     * @param $authType
     * @param $message
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function recordResourceAuthLog($AuthResult, $authType, $message = '')
    {
        $params = [
            'DeviceID' => $this->deviceId,
            'AuthType' => $authType,
            'UserName' => trim($this->params['userName']),
            'DevName' => $this->deviceInfo['DevName'],
            'ResourceAddr' => $this->params['resourceAddr'],
            'AuthResult' => $AuthResult,
            'IsQrLogin' => $this->params['isQrLogin']
        ];
        if ($AuthResult == '1') {
            $params['Remark'] = $message;
        } else {
            $params['FailReason'] = $message;
        }
        \ResourceAuthLogModel::insert($params);
        cutil_php_log($params, $this->logFileName);
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getUserTypeName(): string {
        return $this->userTypeName;
    }

    /**
     * @return array
     */
    public function getUserInfo(): array {
        return $this->userInfo;
    }

    /**
     * 获取设备信息
     * @return array
     */
    public function getBaseDeviceInfo() {
        return $this->deviceInfo;
    }

    /**
     * 设置设备信息
     * @param $deviceInfo
     */
    public function setBaseDeviceInfo($deviceInfo): void {
        $this->deviceInfo = $deviceInfo;
    }

    /**
     * 获取设备在线信息
     * @return array
     */
    public function getOnlineDeviceInfo() {
        return $this->onlineDeviceInfo;
    }

    /**
     * 设置设备在线信息
     * @param $onlineDeviceInfo
     */
    public function setOnlineDeviceInfo($onlineDeviceInfo): void {
        $this->onlineDeviceInfo = $onlineDeviceInfo;
    }
}
