<?php
/**
 * Description: mosquitto操作
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: lib_mosquitto.php 147146 2021-06-17 02:04:51Z duanyc $
 */

!defined('IN_INIT') && exit('Access Denied');

class lib_mosquitto
{
    /**
     * 当前从mqtt连接标识
     *
     * @access protected
     * @var Mosquitto\Client
     */
    protected static $mqtt = null;
    protected static $qos = 0;
    /**
     * 错误代码
     * @var int
     */
    public static $errno = 0;
    /**
     * 错误消息
     * @var string
     */
    public static $error = '';

    /**
     * 初始化redis对象
     * @access protected
     *
     * @param string $clientId
     * @param string $queue_name
     *
     * @return Mosquitto\Client
     */
    protected static function init($clientId = null, $queue_name = '')
    {
        if (empty(self::$mqtt)) {
            try {
                $mqttConfig = $GLOBALS['CONFIG']['mqtt'];
                //self::$qos = $mqttConfig['qos'];
                $mqtt = new Mosquitto\Client(intval($clientId), $mqttConfig['clean_session']);
                $mqtt->setTlsCertificates($mqttConfig['ca_file'], $mqttConfig['cert_file'], $mqttConfig['key_file'], $mqttConfig['password']);
                $mqtt->setTlsOptions(Mosquitto\Client::SSL_VERIFY_NONE, null, null);
                //$mqtt->setTlsInsecure(true);
                $mqtt->setCredentials('root', $mqttConfig['password']);
                $mqtt->connect($mqttConfig['host'], $mqttConfig['port'], $mqttConfig['keepAlive']);
                //$mqtt->onConnect(array('lib_mosquitto', 'handConnect'));
                //$mqtt->onDisconnect(array('lib_mosquitto', 'handleDisconnect'));
                self::$mqtt = $mqtt;
            } catch (Exception $e) {
                self::$errno = $e->getCode();
                self::$error = $e->getMessage();
                self::setError('connect', $e);
                return null;
            }
        }

        return self::$mqtt;
    }

    /**
     * 开始监听
     *
     * @param int $timeout
     */
    public static function start($timeout = 0)
    {
        try {
            self::$mqtt->loop($timeout);
        } catch (\Throwable $e) {
            $mqttConfig = $GLOBALS['CONFIG']['mqtt'];
            self::$mqtt->connect($mqttConfig['host'], $mqttConfig['port'], $mqttConfig['keepAlive']);
        } catch (Exception $e) {
            self::setError('start', $e);
        }
    }

    /**
     * 连接成功
     *
     * @param $rc
     */
    public static function handConnect($rc)
    {
        self::setInfo("connect success.");
    }

    /**
     * 连接断开
     *
     * @param $rc
     */
    public static function handleDisconnect($rc)
    {
        self::setInfo("connect down.");
    }

    /**
     * 往主题队列发布消息
     *
     * @param $queue_name
     * @param $message
     *
     * @return null
     */
    public static function publish($queue_name, $message)
    {
        try {
            $message = json_encode($message);
            $queue_name = strtoupper($queue_name);
            $clientId = $GLOBALS['CONFIG']['mqtt']['queue_name'][$queue_name];
            $mqtt = self::init("{$clientId}1");
            self::start(0);
            $res = $mqtt->publish($queue_name, $message, self::$qos, false);
            self::start(0);
            return $res;
        } catch (Exception $e) {
            self::$errno = $e->getCode();
            self::$error = $e->getMessage();
            self::setError('publish', $e);
            return null;
        }
    }

    /**
     * 处理消息
     *
     * @param $message
     */
    public static function handleMessage($message)
    {
        try {
            $queue_name = $message->topic;
            $params = @json_decode($message->payload, true);
            $func_file = PATH_ROOT . "/worker/QUEUE_{$queue_name}.php";
            $queue_obj = new cls_queue($func_file, $params);
            $result = $queue_obj->execute();
            self::setInfo(">> memory usage: " . memory_get_usage(true) . " res:" . json_encode($result));
        } catch (Exception $e) {
            self::setError('error:', $e);
        }
    }

    /**
     * 订阅主题队列
     *
     * @param $queue_name
     *
     * @return null
     */
    public static function subscribe($queue_name)
    {
        try {
            $queue_name = strtoupper($queue_name);
            $clientId = $GLOBALS['CONFIG']['mqtt']['queue_name'][$queue_name];
            $mqtt = self::init($clientId);
            $mqtt->onMessage(['lib_mosquitto', 'handleMessage']);
            $res = $mqtt->subscribe($queue_name, self::$qos);
            while (true) {
                self::start(0);
            }
            return $res;
        } catch (Exception $e) {
            self::$errno = $e->getCode();
            self::$error = $e->getMessage();
            self::setError('subscribe', $e);
            return null;
        }
    }

    /**
     * 设置信息
     *
     * @param $msg string
     */
    private static function setInfo($msg)
    {
        printf($msg);
        cutil_php_log($msg, 'mqttQueue');
    }

    /**
     * 设置错误
     *
     * @param $msg string
     * @param $e Exception
     */
    private static function setError($msg, $e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        cutil_php_log("{$msg} error:{$message}, code:{$code}", 'mqttQueue');
    }
}
