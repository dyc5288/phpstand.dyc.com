<?php

/**
 * Description: 加密相关函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: crypt.func.php 175039 2022-05-05 07:33:41Z duanyc $
 */

/**
 * 异或加密
 *
 * @param string $packet
 *            加密报文
 * @param string $key
 *            密匙
 *
 * @return string
 */
function doXorEncrypt($packet, $key)
{
    $plen = strlen($packet);
    $klen = strlen($key);
    $j = 0;
    for ($i = 0; $i < $plen; $i++) {
        $s = ord($packet [$i]) ^ ord($key [$j]);
        $packet [$i] = chr((($s << 4) % 256 + ($s >> 4)));
        $j = ($j + 1) % $klen;
    }
    return $packet;
}

/**
 * 异或解密
 *
 * @param string $packet
 *            加密报文
 * @param string $key
 *            密匙
 *
 * @return string
 */
function doXorDecrypt($packet, $key)
{
    $plen = strlen($packet);
    $klen = strlen($key);
    $j = 0;
    for ($i = 0; $i < $plen; $i++) {
        $s = ord($packet [$i]) >> 4;
        $packet [$i] = chr(((ord($packet [$i]) << 4) % 256 + $s) ^ ord($key [$j]));
        $j = ($j + 1) % $klen;
    }
    return $packet;
}

/**
 * rsa公钥加密
 * 公钥地址 默认用敲门的私钥
 *
 * @param $data
 * @param string $path
 *
 * @return null
 */
function pubEncrypt($data, $path = "")
{
    if (!is_string($data)) {
        return null;
    }
    if ($path === "") {
        $path = PATH_ETC . "fwknop/public_key.pem";
    }
    $pub_key = file_get_contents($path);
    $pub_key = openssl_pkey_get_public($pub_key);
    return openssl_public_encrypt($data, $decode_result, $pub_key) ? Base64EnExt($decode_result) : null;
}

/**
 * rsa公钥解密
 * 公钥地址 默认用敲门的私钥
 *
 * @param $data
 * @param string $path
 *
 * @return null
 */
function pubDncrypt($data, $path = "")
{
    if (!is_string($data)) {
        return null;
    }
    if ($path === "") {
        $path = PATH_ETC . "fwknop/private_key.pem";
    }
    $pub_key = file_get_contents($path);
    $pub_key = openssl_pkey_get_private($pub_key);
    return openssl_private_decrypt(Base64DeExt($data), $decode_result, $pub_key) ? ($decode_result) : null;
}

/**
 * 生成rsa公钥
 *
 * @param $key
 * @param string $path
 *
 * @return bool|string
 * @throws Exception
 */
function getRsaPubKey($key, $path = PATH_DATA . "/rsa/")
{
    if (!file_exists($path . $key . '_pub.pem')) {
        $pubKey = '';
        $cmd = 'openssl rsa -pubout -in ' . $path . $key . '_priv.pem -out ' . $path . $key . '_pub.pem';
        $res = cutil_exec_wait($cmd);
        if ($res) {
            if (file_exists($path . $key . '_pub.pem')) {
                $pubKey = file_get_contents($path . $key . '_pub.pem');
            }
        }
        return $pubKey;
    }
    return file_get_contents($path . $key . '_pub.pem');
}

/**
 * 生成rsa秘钥
 *
 * @param $key
 * @param string $path
 *
 * @return bool|string
 * @throws Exception
 */
function getRsaPriKey($key, $path = PATH_DATA . "/rsa/")
{
    $priKey = '';
    if (!file_exists($path)) {
        mkdir($path, 0775);
    }
    if (file_exists($path . $key . '_priv.pem')) {
        $priKey = file_get_contents($path . $key . '_priv.pem');
        return $priKey;
    }
    $cmd = 'openssl genrsa -out ' . $path . $key . '_priv.pem 1024';
    $res = cutil_exec_wait($cmd);
    if ($res) {
        if (file_exists($path . $key . '_priv.pem')) {
            $priKey = file_get_contents($path . $key . '_priv.pem');
        }
    }
    return $priKey;
}

/**
 * md5加密封装
 *
 * @param $str
 * @param $type
 * @param $key
 *
 * @return string
 */
function dataEncrypt($str, $type = 'md5', $key = '')
{
    switch ($type) {
        case 'md5':
            return md5($str);
        case 'xor':
            return doXorEncrypt($str, $key);
    }
    return false;
}
