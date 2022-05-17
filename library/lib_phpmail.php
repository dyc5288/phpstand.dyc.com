<?php

/**
 * Description: 邮件操作
 * User: duanyc@infogo.com.cn
 * Date: 2021/05/18 09:46
 * Version: $Id: lib_phpmail.php 166906 2022-01-11 07:48:17Z duanyc $
 */
!defined('IN_INIT') && exit('Access Denied');

use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\POP3;
use PHPMailer\PHPMailer\Exception;

class lib_phpmail
{
    /**
     * 验证
     *
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @param $mailType
     * @param $isSSL
     *
     * @return bool
     * @throws \Exception
     */
    public static function login($host, $port, $username, $password, $mailType = 'smtp', $isSSL = '0')
    {
        if (!in_array($mailType, ['imap', 'pop3', 'smtp'])) {
            return false;
        }

        if (strspn($host, '.0123456789') != strlen($host) && strstr($host, '/') === false) {
            $host = @gethostbyname($host);
            if (!isHavIP($host)) {
                T(21124004);
            }
        }

        $port = $port % 65536;
        $pingResult = self::ping($host, $port, 10);
        if ($pingResult == 'down') {
            T(21124004);
        }

        if (!\hlp_check::checkRuleResult('Email', $username)) {
            T(21124002);
        }
        try {
            cutil_php_log(['login', $host, $port, $username, $password, $mailType, $isSSL, $pingResult], 'mail');
            switch ($mailType) {
                case 'smtp':
                    self::smtpLogin($host, $port, $username, $password);
                    break;
                case 'imap':
                    self::imapLogin($host, $port, $username, $password);
                    break;
                case 'pop3':
                    self::pop3Login($host, $port, $username, $password);
                    break;
                default:
            }
            return true;
        } catch (\Exception $e) {
            self::setError($e, 'login');
            return false;
        }
    }

    /**
     * ping服务器
     *
     * @param $host
     * @param $port
     * @param $timeout
     *
     * @return string
     */
    public static function ping($host, $port, $timeout)
    {
        // 缓存ping的结果1天
        $key = "{$host}_{$port}";
        $cache = cache_get_info('emailServer', $key);
        if (!empty($cache['time'])) {
            return $cache['time'];
        }
        $errstr = '';
        $errno = '';
        $tB = microtime(true);
        $fP = fSockOpen($host, $port, $errno, $errstr, $timeout);
        if (!$fP) { return "down"; }
        $tA = microtime(true);
        $return = round((($tA - $tB) * 1000), 0)." ms";
        cache_set_info('emailServer', $key, ['time' => $return], 86400);
        return $return;
    }

    /**
     * smtp登录
     *
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @throws \Exception
     */
    public static function smtpLogin($host, $port, $username, $password)
    {
        $smtp = new SMTP();
        try {
            $smtp->setDebugLevel(SMTP::DEBUG_CONNECTION);
            $smtp->setDebugOutput('error_log');
            $smtp->setTimeout(10);
            //Connect to an SMTP server
            if (!$smtp->connect($host, $port, 10, ['ssl' => ['verify_peer_name' => FALSE]])) {
                throw new \Exception('Connect failed');
            }
            //Say hello
            if (!$smtp->hello(gethostname())) {
                throw new \Exception('EHLO failed: ' . $smtp->getError()['error']);
            }
            //Get the list of ESMTP services the server offers
            $e = $smtp->getServerExtList();
            //If server can do TLS encryption, use it
            if (is_array($e) && array_key_exists('STARTTLS', $e)) {
                $tlsok = $smtp->startTLS();
                if (!$tlsok) {
                    throw new \Exception('Failed to start encryption: ' . $smtp->getError()['error']);
                }
                //Repeat EHLO after STARTTLS
                if (!$smtp->hello(gethostname())) {
                    throw new \Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
                }
                //Get new capabilities list, which will usually now include AUTH if it didn't before
                $e = $smtp->getServerExtList();
            }
            //If server supports authentication, do it (even if no encryption)
            if (is_array($e) && array_key_exists('AUTH', $e)) {
                if ($smtp->authenticate($username, $password)) {
                    cutil_php_log("{$username}: {$password} Connected ok!", "mail");
                } else {
                    throw new \Exception('Authentication failed: ' . $smtp->getError()['error']);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('SMTP error: ' . $e->getMessage());
        }
        //Whatever happened, close the connection.
        $smtp->quit();
    }

    /**
     * imap登录
     *
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @throws \Exception
     */
    public static function imapLogin($host, $port, $username, $password)
    {
        try {
            $mailLink="{{$host}:{$port}}INBOX" ; //imagp连接地址：不同主机地址不同
            $mbox = imap_open($mailLink, $username, $password); //开启信箱imap_open
            imap_num_msg($mbox); //取得信件数
        } catch (\Exception $e) {
            throw new \Exception('IMAP error: ' . $e->getMessage());
        }
    }

    /**
     * pop3登录
     *
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @throws \Exception
     */
    public static function pop3Login($host, $port, $username, $password)
    {
        $pop3 = new POP3();
        try {
            // $pop3->do_debug = POP3::DEBUG_CLIENT; debug模式会echo输出错误信息
            //Connect to an SMTP server
            if (!$pop3->connect($host, $port, 10)) {
                throw new \Exception('Connect failed');
            }
            //If server supports authentication, do it (even if no encryption)
            if ($pop3->login($username, $password)) {
                cutil_php_log("{$username}: {$password} Connected ok!", "mail");
            } else {
                throw new \Exception('Authentication failed: ' . var_export($pop3->getErrors(), true));
            }
        } catch (\Exception $e) {
            throw new \Exception('POP3 error: ' . $e->getMessage());
        }
        //Whatever happened, close the connection.
        $pop3->disconnect();
    }

    /**
     * 错误处理
     *
     * @param \Exception $ex
     * @param string $err_msg
     *
     * @return bool
     */
    public static function setError($ex, $err_msg)
    {
        $ip = getRemoteAddress();
        $err = $ex ? $ex->getMessage() : "";
        cutil_php_log($err_msg . ":{$ip}: " . $err, "mail");
        return true;
    }
}
