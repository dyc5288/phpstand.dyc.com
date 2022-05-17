<?php
/**
 * Description: yar����
 * User: duanyc@infogo.com.cn
 * Date: 2021/07/5 15:53
 * Version: $Id: AsmYarServer.php 157495 2021-09-22 15:16:46Z renchen $
 */

//abstract class
//protected required for subclass
abstract class AsmYarServer
{
    /**
     * ��Կ
     * @var mixed
     */
    private $_app_key = '';
    private $_sign = '';
    private $_time = 0;

    /**
     * cls_rpc constructor.
     */
    public function __construct()
    {
        $this->_app_key = $GLOBALS['CONFIG']['yar_server']['dasm']['key'];
        $this->_sign = request('sign', 'request');
        $this->_time = request('time', 'request', 0, 'int');
    }

    /**
     * ���ݲ�������ǩ��
     * @param array $rpc_params
     * @return string
     */
    private function _makeSign($rpc_params)
    {
        $str = sha1($this->_time . serialize(array_values($rpc_params)) . $this->_time);
        $key = sha1($this->_time . sha1(sha1($this->_time . $this->_app_key) . $this->_time) . $this->_time);
        $sign = '';
        for ($i = 0; $i < 40; $i++) {
            $sign .= $str[$i] . $key[39 - $i];
        }
        return substr(sha1(strrev(sha1($sign))), 5, 32);
    }

    /**
     * ������ǩ��
     * ��rpc��urlͨ��get��ʽ����÷�����ǰ2����������У�����
     * @param array $args
     * @return void
     * @throws Exception
     */
    private function _checkSign(&$args)
    {
        if (empty($this->_sign) && empty($this->_time)) {
            $this->_sign = isset($args[0]) && is_string($args[0])  ? $args[0] : '';
            $this->_time = isset($args[1]) && is_numeric($args[1]) ? (int)$args[1] : 0;
            $args = array_slice($args, 2);
        }
        $sign = $this->_makeSign($args);
        if (empty($this->_sign) || empty($this->_time) || $this->_sign !== $sign) {
            cutil_php_log(var_export([$args, $this->_sign, $this->_time, $sign, $this->_app_key], true), "yar_server");
            header('HTTP/1.1 403 File Not Exist', true, 403);
            json_print(['errcode' => '403', 'errmsg' => 'Yar File Not Exist']);
        }
    }

    /**
     * �������ǩ����������Ӧ����
     * @param string $fun
     * @param array $args
     * @return bool|mixed
     * @throws Exception
     */
    public function __call(string $fun, array $args)
    {
        $return = array('state' => true, 'message' => '', 'code' => 0);
        try {
            $this->_checkSign($args);
            cutil_php_log(var_export([$fun, $args], true), "yar_server");
            $result = call_user_func_array(array($this, $fun), $args);
            $return['message'] = $result['message'] ?? '';
            $return['code'] = $result['code'] ?? 0;
            $return['data'] = $result['data'] ?? $result;
        } catch (Exception $e) {
            $return['state'] = false;
            $return['message'] = $e->getMessage();
            $return['code'] = $e->getCode();
        }

        return $return;
    }
}
