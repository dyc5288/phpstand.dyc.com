<?php

/**
 * Description: RPC操作
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: lib_yar.php 158033 2021-09-27 15:28:51Z renchen $
 */
!defined('IN_INIT') && exit('Access Denied');

class lib_yar
{
    private static $exception;

    /**
     * 获取rpc URL
     *
     * @param string $rpc_key rpc键
     * @param array $rpc_params rpc参数
     * @param string $rpc_ip RPC服务器IP
     *
     * @return string|boolean
     * @throws Exception
     */
    private static function geRpcUrl($rpc_key, $rpc_params, $rpc_ip)
    {
        if (empty($rpc_key) || !isset($GLOBALS['CONFIG']['yar_server'][$rpc_key])) {
            return false;
        }

        $rpc = $GLOBALS['CONFIG']['yar_server'][$rpc_key];

        switch ($rpc_key) {
            case 'net':
            case 'dasm':
            case 'duser':
                $rpc_url = "http://{$rpc_ip}" . $rpc['url'];
                break;
            default:
                $rpc_url = "http://127.0.0.1" . $rpc['url'];
                break;
        }

        if (!empty($rpc['key'])) {
            $time = (int)(microtime(true) * 1000000);
            $sign = self::makeSign($rpc_params, $rpc['key'], $time);
            $rpc_url .= (strpos($rpc_url, '?') ? '&' : '?') . "sign={$sign}&time={$time}";
        }

        return $rpc_url;
    }

    /**
     * 根据密钥,请求参数和时间计算签名.
     *
     * @param   array $rpc_params 请求参数
     * @param   string $app_key 密钥
     * @param   int $time 时间
     *
     * @return  string      签名
     */
    public static function makeSign($rpc_params, $app_key, $time)
    {
        $str = sha1($time . serialize(array_values($rpc_params)) . $time);
        $key = sha1($time . sha1(sha1($time . $app_key) . $time) . $time);
        $sign = '';
        for ($i = 0; $i < 40; $i++) {
            $sign .= $str[$i] . $key[39 - $i];
        }
        return substr(sha1(strrev(sha1($sign))), 5, 32);
    }

    /**
     * yar调用 带重试
     *
     * @param string $rpc_key RPC应用
     * @param string $rpc_function RPC方法
     * @param array|string $rpc_params RPC参数
     * @param string $rpc_ip RPC服务器IP
     * @param int $rpc_timout RPC超时时间
     * @param int $times 失败次数
     *
     * @return boolean|array
     */
    public static function clients($rpc_key, $rpc_function, $rpc_params, $rpc_ip = '127.0.0.1', $rpc_timout = 3000, $times = 3)
    {
        if (!empty($GLOBALS['CONFIG']['yar_server'][$rpc_key]['gbk'])) {
            $rpc_params['local_lguage_set'] = defined('LANG') ? $GLOBALS['CONFIG']['LANG_MAP'][LANG] : 'zh';
            $rpc_params = [utf8ToGbk($rpc_params)];
        } else {
            $rpc_params = [$rpc_params];
        }
        for ($i = 0; $i < $times; $i++) {
            $result = self::client($rpc_key, $rpc_function, $rpc_params, $rpc_ip, $rpc_timout);

            if ($result !== null) {
                if (!empty($GLOBALS['CONFIG']['yar_server'][$rpc_key]['gbk'])) {
                    return gbkToUtf8($result);
                }
                return $result;
            }
        }

        $data = [];
        $data['rpc_key'] = $rpc_key;
        $data['rpc_function'] = $rpc_function;
        $data['rpc_params'] = $rpc_params;
        self::setError(self::$exception, "RPC run {$rpc_key}-{$rpc_function}-" . json_encode($rpc_params));
        return false;
    }

    /**
     * yar调用
     *
     * @param string $rpc_key RPC应用
     * @param string $rpc_function RPC方法
     * @param array|string $rpc_params RPC参数
     * @param string $rpc_ip RPC服务器IP
     * @param int $rpc_timout RPC超时时间
     *
     * @return boolean|array
     */
    private static function client($rpc_key, $rpc_function, $rpc_params, $rpc_ip, $rpc_timout = 1000)
    {
        if (empty($rpc_key) || empty($rpc_function) || empty($rpc_params)) {
            return false;
        }

        try {
            $rpc_url = self::geRpcUrl($rpc_key, $rpc_params, $rpc_ip);

            if (empty($rpc_url)) {
                throw new Exception("RPC_KEY:{$rpc_key} server not exist, please check config!");
            }

            $rpc_client = new Yar_Client($rpc_url);
            $rpc_client->SetOpt(YAR_OPT_PACKAGER, YAR_PACKAGER_PHP);
            $rpc_client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, $rpc_timout);
            return call_user_func_array([$rpc_client, $rpc_function], $rpc_params);
        } catch (Yar_Client_Exception $ex) {
            self::$exception = $ex;
            self::setError($ex, "{$rpc_url} RPC Client");
            return null;
        } catch (Yar_Server_Exception $ex) {
            self::$exception = $ex;
            self::setError($ex, "{$rpc_url} RPC Server");
            return null;
        } catch (Exception $ex) {
            self::$exception = $ex;
            self::setError($ex, "{$rpc_url} RPC Exception");
            return null;
        }
    }

    /**
     * 并行调用 调用完毕记得调用yar_concurrent_loop 进行发送
     *
     * @param string $rpc_key RPC key
     * @param string $rpc_function RPC方法
     * @param string|array $rpc_params RPC参数
     * @param string $rpc_call_back RPC回调函数
     *
     * @throws Exception
     * @return boolean
     */
    public static function concurrentClient($rpc_key, $rpc_function, $rpc_params, $rpc_ip = '127.0.0.1', $rpc_callback = '')
    {
        try {
            $rpc_url = self::geRpcUrl($rpc_key, $rpc_params, $rpc_ip);

            if (empty($rpc_url)) {
                throw new Exception("RPC_SERVER:{$rpc_key} server not exist, please check config!");
            }

            return Yar_Concurrent_Client::call($rpc_url, $rpc_function, $rpc_params, $rpc_callback);
        } catch (Yar_Client_Exception $ex) {
            self::setError($ex, "RPC many add");
            return null;
        }
    }

    /**
     * 发送并行处理
     *
     * @param string $rpc_callback 正确时候处理方法
     * @param string $rpc_error_callback 错误处理回调方法
     *
     * @return boolean
     */
    public static function concurrentLoop($rpc_callback, $rpc_error_callback)
    {
        try {
            return Yar_Concurrent_Client::loop($rpc_callback, $rpc_error_callback);
        } catch (Yar_Client_Exception $ex) {
            self::setError($ex, "RPC many send");
            return null;
        }
    }

    /**
     * 开启yar serverice
     *
     * @param string $service_name 服务名字
     *
     * @return boolean
     */
    public static function server($service_name)
    {
        try {
            $service = new Yar_Server(new $service_name());
            return $service->handle();
        } catch (Yar_Server_Exception $ex) {
            self::setError($ex, "RPC{$service_name} server start");
            return null;
        }
    }

    /**
     * 错误处理
     *
     * @param Exception $ex
     * @param string $err_msg
     *
     * @return bool
     */
    public static function setError($ex, $err_msg)
    {
        $ip = getRemoteAddress();
        $err = $ex ? $ex->getMessage() : "";
        cutil_php_log($err_msg . ":{$ip}: " . $err, "yar");
        return true;
    }
}
