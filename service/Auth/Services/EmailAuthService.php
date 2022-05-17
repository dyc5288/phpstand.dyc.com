<?php
/**
 * Description: Email服务器认证服务
 * User: duanyc@infogo.com.cn
 * Date: 2021/2/18
 * Version: $Id: EmailAuthService.php 161644 2021-11-12 13:26:30Z duanyc $
 */

namespace Services\Auth\Services;

use Services\Auth\Interfaces\AuthServiceInterface;

class EmailAuthService extends BaseAuthService implements AuthServiceInterface
{
    /**
     * 常见邮箱POP3/SMTP服务器地址与端口
     *   gmail(google.com)
     *   POP3服务器地址:pop.gmail.com（SSL启用 端口：995）
     *   SMTP服务器地址:smtp.gmail.com（SSL启用 端口：587）
     *   21cn.com:
     *   POP3服务器地址:pop.21cn.com（端口：110）
     *   SMTP服务器地址:smtp.21cn.com（端口：25）
     *   sina.com:
     *   POP3服务器地址:pop3.sina.com.cn（端口：110）
     *   SMTP服务器地址:smtp.sina.com.cn（端口：25）
     *   tom.com:
     *   POP3服务器地址:pop.tom.com（端口：110）
     *   SMTP服务器地址:smtp.tom.com（端口：25）
     *   163.com:
     *   POP3服务器地址:pop.163.com（端口：110）
     *   SMTP服务器地址:smtp.163.com（端口：25）
     *   263.net:
     *   POP3服务器地址:pop3.263.net（端口：110）
     *   SMTP服务器地址:smtp.263.net（端口：25）
     *   yahoo.com:
     *   POP3服务器地址:pop.mail.yahoo.com
     *   SMTP服务器地址:smtp.mail.yahoo.com
     *   263.net.cn:
     *   POP3服务器地址:pop.263.net.cn（端口：110）
     *   SMTP服务器地址:smtp.263.net.cn（端口：25）
     *   Foxmail：
     *   POP3服务器地址:POP.foxmail.com（端口：110）
     *   SMTP服务器地址:SMTP.foxmail.com（端口：25）
     *   sinaVIP
     *   POP3服务器:pop3.vip.sina.com （端口：110）
     *   SMTP服务器:smtp.vip.sina.com （端口：25）
     *   sohu.com:
     *   POP3服务器地址:pop3.sohu.com（端口：110）
     *   SMTP服务器地址:smtp.sohu.com（端口：25）
     *   etang.com:
     *   POP3服务器地址:pop.etang.com
     *   SMTP服务器地址:smtp.etang.com
     *   x263.net:
     *   POP3服务器地址:pop.x263.net（端口：110）
     *   SMTP服务器地址:smtp.x263.net（端口：25）
     *   yahoo.com.cn:
     *   POP3服务器地址:pop.mail.yahoo.com.cn（端口：995）
     *   SMTP服务器地址:smtp.mail.yahoo.com.cn（端口：587）
     *   雅虎邮箱POP3的SSL不启用端口为110，POP3的SSL启用端口995；SMTP的SSL不启用端口为25，SMTP的SSL启用端口为465
     *   QQ邮箱 QQ企业邮箱
     *   POP3服务器地址：pop.qq.com（端口：110）
     *   POP3服务器地址：pop.exmail.qq.com （SSL启用 端口：995）
     *   SMTP服务器地址：smtp.qq.com （端口：25）
     *   SMTP服务器地址：smtp.exmail.qq.com（SSL启用 端口：587/465）
     *   SMTP服务器需要身份验证
     *   126邮箱 HotMail
     *   POP3服务器地址:pop.126.com（端口：110）
     *   POP3服务器地址：pop.live.com （端口：995）
     *   SMTP服务器地址:smtp.126.com（端口：25）
     *   SMTP服务器地址：smtp.live.com （端口：587）
     *   china.com: 139邮箱
     *   POP3服务器地址:pop.china.com（端口：110）
     *   POP3服务器地址：POP.139.com（端口：110）
     *   SMTP服务器地址:smtp.china.com（端口：25）
     *   SMTP服务器地址：SMTP.139.com(端口：25)
     */

    /**
     * 用户类型/认证方式
     *
     * @var string
     */
    protected $userType = 'Email';

    /**
     * 初始化
     *
     * @param $params
     * @throws \Exception
     */
    public function __construct($params)
    {
        $this->userTypeName = L(21120048);
        parent::__construct($params);
    }

    /**
     * 认证
     * @return array|bool
     * @throws \Exception
     */
    public function auth()
    {
        $this->checkAllowAuthType('User');
        $aAuthEmail = \DictModel::getAll("AuthEmail");
        if (!empty($aAuthEmail['DomainValidate'])) {
            $domain = '';
            $emailArr = explode('@', $this->params['userName']);
            if (is_array($emailArr) && !empty($emailArr[1])) {
                $domain = $emailArr[1];
            }
            $validateStr = $aAuthEmail['DomainValidate'];
            if (stripos($domain, $validateStr) === false) {
                T(21124001);
            }
        }
        $port = strlen($aAuthEmail['Port']) > 0 ? $aAuthEmail['Port'] : 25;
        $mailType = $aAuthEmail['MailType'] ?? 'smtp';
        $isSSL = $aAuthEmail['IsSSL'] ?? '0';
        $res = \lib_phpmail::login($aAuthEmail['Host'], $port, $this->params['userName'], $this->params['password'], $mailType, $isSSL);
        if (!$res) {
            T(21124002);
        }
        // 判断系统中用户是否存在
        $aResult = $this->UserIsExist($this->userType, $this->params['userName'], [], L(21124003));
        $factorAuths = $this->getFactorAuthServer();
        $this->params['factorAuth'] = in_array('Email', $factorAuths);
        $aResult['BeforeAuthType'] = $this->userType;
        return $aResult;
    }
}
