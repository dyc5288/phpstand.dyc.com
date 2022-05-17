<?php
/**
 * Description: Ukey认证需要函数库 原cerauth.php
 * User: duanyc@infogo.com.cn
 * Date: 2021/05/14 16:55
 * Version: $Id: cer.func.php 157618 2021-09-23 15:01:55Z duanyc $
 */
// get from http://badpenguins.com/source/misc/isCertSigner.php?viewSource
/**
 * Is one pem encoded certificate the signer of another?
 *
 * The PHP openssl functionality is severely limited by the lack of a stable
 * api and documentation that might as well have been encrypted itself.
 * In particular the documention on openssl_verify() never explains where
 * to get the actual signature to verify.  The isCertSigner() function below
 * will accept two PEM encoded certs as arguments and will return true if
 * one certificate was used to sign the other.  It only relies on the
 * openssl_pkey_get_public() and openssl_public_decrypt() openssl functions,
 * which should stay fairly stable.  The ASN parsing code snippets were mostly
 * borrowed from the horde project's smime.php.
 *
 * @author Mike Green <mikey at badpenguins dot com>
 * @copyright Copyright (c) 2010, Mike Green
 * @license http://opensource.org/licenses/gpl-2.0.php GPLv2
 */

/**
 * Extract signature from der encoded cert.
 * Expects x509 der encoded certificate consisting of a section container
 * containing 2 sections and a bitstream.  The bitstream contains the
 * original encrypted signature, encrypted by the public key of the issuing
 * signer.
 * @param string $der
 * @return string on success
 * @return bool false on failures
 */
function extractSignature($der = false)
{
    if (strlen($der) < 5) {
        return false;
    }
    // skip container sequence
    $der = substr($der, 4);
    // now burn through two sequences and the return the final bitstream
    while (strlen($der) > 1) {
        $class = ord($der[0]);
        $classHex = dechex($class);
        switch ($class) {
            // BITSTREAM
            case 0x03:
                $len = ord($der[1]);
                $bytes = 0;
                if ($len & 0x80) {
                    $bytes = $len & 0x0f;
                    $len = 0;
                    for ($i = 0; $i < $bytes; $i++) {
                        $len = ($len << 8) | ord($der[$i + 2]);
                    }
                }
                return substr($der, 3 + $bytes, $len);
                break;
            // SEQUENCE
            case 0x30:
                $len = ord($der[1]);
                $bytes = 0;
                if ($len & 0x80) {
                    $bytes = $len & 0x0f;
                    $len = 0;
                    for ($i = 0; $i < $bytes; $i++) {
                        $len = ($len << 8) | ord($der[$i + 2]);
                    }
                }
                $contents = substr($der, 2 + $bytes, $len);
                $der = substr($der, 2 + $bytes + $len);
                break;
            default:
                return false;
                break;
        }
    }
    return false;
}

/**
 * Get signature algorithm oid from der encoded signature data.
 * Expects decrypted signature data from a certificate in der format.
 * This ASN1 data should contain the following structure:
 * SEQUENCE
 *    SEQUENCE
 *       OID    (signature algorithm)
 *       NULL
 * OCTET STRING (signature hash)
 * @return bool false on failures
 * @return string oid
 */
function getSignatureAlgorithmOid($der = null)
{
    // Validate this is the der we need...
    if (!is_string($der) or strlen($der) < 5) {
        return false;
    }
    $bit_seq1 = 0;
    $bit_seq2 = 2;
    $bit_oid = 4;
    if (ord($der[$bit_seq1]) !== 0x30) {
        die('Invalid DER passed to getSignatureAlgorithmOid()');
    }
    if (ord($der[$bit_seq2]) !== 0x30) {
        die('Invalid DER passed to getSignatureAlgorithmOid()');
    }
    if (ord($der[$bit_oid]) !== 0x06) {
        die('Invalid DER passed to getSignatureAlgorithmOid');
    }
    // strip out what we don't need and get the oid
    $der = substr($der, $bit_oid);
    // Get the oid
    $len = ord($der[1]);
    $bytes = 0;
    if ($len & 0x80) {
        $bytes = $len & 0x0f;
        $len = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $len = ($len << 8) | ord($der[$i + 2]);
        }
    }
    $oid_data = substr($der, 2 + $bytes, $len);
    // Unpack the OID
    $oid = floor(ord($oid_data[0]) / 40);
    $oid .= '.' . ord($oid_data[0]) % 40;
    $value = 0;
    $i = 1;
    while ($i < strlen($oid_data)) {
        $value = $value << 7;
        $value = $value | (ord($oid_data[$i]) & 0x7f);
        if (!(ord($oid_data[$i]) & 0x80)) {
            $oid .= '.' . $value;
            $value = 0;
        }
        $i++;
    }
    return $oid;
}

/**
 * Get signature hash from der encoded signature data.
 * Expects decrypted signature data from a certificate in der format.
 * This ASN1 data should contain the following structure:
 * SEQUENCE
 *    SEQUENCE
 *       OID    (signature algorithm)
 *       NULL
 * OCTET STRING (signature hash)
 * @return bool false on failures
 * @return string hash
 */
function getSignatureHash($der = null)
{
    // Validate this is the der we need...
    if (!is_string($der) or strlen($der) < 5) {
        return false;
    }
    if (ord($der[0]) !== 0x30) {
        die('Invalid DER passed to getSignatureHash()');
    }
    // strip out the container sequence
    $der = substr($der, 2);
    if (ord($der[0]) !== 0x30) {
        die('Invalid DER passed to getSignatureHash()');
    }
    // Get the length of the first sequence so we can strip it out.
    $len = ord($der[1]);
    $bytes = 0;
    if ($len & 0x80) {
        $bytes = $len & 0x0f;
        $len = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $len = ($len << 8) | ord($der[$i + 2]);
        }
    }
    $der = substr($der, 2 + $bytes + $len);
    // Now we should have an octet string
    if (ord($der[0]) !== 0x04) {
        die('Invalid DER passed to getSignatureHash()');
    }
    $len = ord($der[1]);
    $bytes = 0;
    if ($len & 0x80) {
        $bytes = $len & 0x0f;
        $len = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $len = ($len << 8) | ord($der[$i + 2]);
        }
    }
    return bin2hex(substr($der, 2 + $bytes, $len));
}

/**
 * Determine if one cert was used to sign another
 * Note that more than one CA cert can give a positive result, some certs
 * re-issue signing certs after having only changed the expiration dates.
 * @param string $cert - PEM encoded cert
 * @param string $caCert - PEM encoded cert that possibly signed $cert
 * @return bool
 */
function isCertSigner($certPem = null, $caCertPem = null)
{
    if (empty($certPem) or empty($caCertPem)) {
        return false;
    }
    /* 先采用标准的openssl算法 然后再用国密 */
    $resRSA = RSACertSinger($certPem, $caCertPem);
    if (!$resRSA) {
        return SMCertSinger($certPem, $caCertPem);
    }
    return $resRSA;
}

/**
 * 产生bai随机字符串
 *
 * @param int $length 输出du长度
 * @param string $chars 可选的值 ，默认为 0123456789
 * @return string 字符串
 */
function random($length, $chars = '0123456789')
{
    $hash = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * 采用gmssl校验
 * @param null $certPem
 * @param null $caCertPem
 * @return bool
 */
function SMCertSinger($certPem = null, $caCertPem = null)
{
    $random = random(10, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
    $userCerTmpFile = PATH_TMP . '/infogoASMUser_'.$random.'.cer';
    $caCerTmpFile = PATH_TMP . '/infogoASMCA_'.$random.'.cer';
    file_put_contents($userCerTmpFile, $certPem);
    file_put_contents($caCerTmpFile, $caCertPem);
    $resStr = shell_exec(PATH_LOCAL . '/gmssl/bin/gmssl verify -CAfile ' . $caCerTmpFile . ' ' . $userCerTmpFile);
    shell_exec('rm -f ' . $userCerTmpFile);
    shell_exec('rm -f ' . $caCerTmpFile);
    if (strpos($resStr, $userCerTmpFile.": verification failed") !== false) {
        return false;
    }
    if (strpos($resStr, $userCerTmpFile.": OK") !== false) {
        return true;
    }
    return false;
}

/**
 *
 * @param null $certPem
 * @param null $caCertPem
 * @return bool
 *
 */
function RSACertSinger($certPem = null, $caCertPem = null)
{
    // Convert the cert to der for feeding to extractSignature.
    $certDer = pemToDer($certPem);
    if (!is_string($certDer)) {
        return false;
    }
    // Grab the encrypted signature from the der encoded cert.
    $encryptedSig = extractSignature($certDer);
    if (!is_string($encryptedSig)) {
        return false;
    }
    // Extract the public key from the ca cert, which is what has
    // been used to encrypt the signature in the cert.
    $pubKey = openssl_pkey_get_public($caCertPem);
    if ($pubKey === false) {
        return false;
    }
    // Attempt to decrypt the encrypted signature using the CA's public
    // key, returning the decrypted signature in $decryptedSig.  If
    // it can't be decrypted, this ca was not used to sign it for sure...
    $rc = openssl_public_decrypt($encryptedSig, $decryptedSig, $pubKey);
    if ($rc === false) {
        return false;
    }
    // We now have the decrypted signature, which is der encoded
    // asn1 data containing the signature algorithm and signature hash.
    // Now we need what was originally hashed by the issuer, which is
    // the original DER encoded certificate without the issuer and
    // signature information.
    $origCert = stripSignerAsn($certDer);
    if ($origCert === false) {
        return false;
    }
    // Get the oid of the signature hash algorithm, which is required
    // to generate our own hash of the original cert.  This hash is
    // what will be compared to the issuers hash.
    $oid = getSignatureAlgorithmOid($decryptedSig);
    if ($oid === false) {
        return false;
    }
    switch ($oid) {
        case '1.2.840.113549.2.2':
            $algo = 'md2';
            break;
        case '1.2.840.113549.2.4':
            $algo = 'md4';
            break;
        case '1.2.840.113549.2.5':
            $algo = 'md5';
            break;
        case '1.3.14.3.2.18':
            $algo = 'sha';
            break;
        case '1.3.14.3.2.26':
            $algo = 'sha1';
            break;
        case '2.16.840.1.101.3.4.2.1':
            $algo = 'sha256';
            break;
        case '2.16.840.1.101.3.4.2.2':
            $algo = 'sha384';
            break;
        case '2.16.840.1.101.3.4.2.3':
            $algo = 'sha512';
            break;
        default:
            die('Unknown signature hash algorithm oid: ' . $oid);
            break;
    }
    // Get the issuer generated hash from the decrypted signature.
    $decryptedHash = getSignatureHash($decryptedSig);
    // Ok, hash the original unsigned cert with the same algorithm
    // and if it matches $decryptedHash we have a winner.
    $certHash = hash($algo, $origCert);
    return ($decryptedHash === $certHash);
}

/**
 * Convert pem encoded certificate to DER encoding
 * @return string $derEncoded on success
 * @return bool false on failures
 */
function pemToDer($pem = null)
{
    if (!is_string($pem)) {
        return false;
    }
    $cert_split = preg_split('/(-----((BEGIN)|(END)) CERTIFICATE-----)/', $pem);
    if (!isset($cert_split[1])) {
        return false;
    }
    return base64_decode($cert_split[1]);
}

/**
 * Obtain der cert with issuer and signature sections stripped.
 * @param string $der - der encoded certificate
 * @return string $der on success
 * @return bool false on failures.
 */
function stripSignerAsn($der = null)
{
    if (!is_string($der) or strlen($der) < 8) {
        return false;
    }
    $bit = 4;
    $len = ord($der[($bit + 1)]);
    $bytes = 0;
    if ($len & 0x80) {
        $bytes = $len & 0x0f;
        $len = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $len = ($len << 8) | ord($der[$bit + $i + 2]);
        }
    }
    return substr($der, 4, $len + 4);
}

/**
 * array(array("SN", "DATE"))
 * use the openssl command to parse the crl file
 * @param $crlfile:the Certificate Revocation List file(PEM/DER)
 * @param $openssl
 * @return array|bool
 */
function getCrlInfo($crlfile, $openssl = "openssl")
{
    $tmpfile = tempnam(sys_get_temp_dir(), "tmp");
    $content = file_get_contents($crlfile);
    if (strstr($content, "-----BEGIN X509 CRL")) {
        $cmd = "openssl crl -inform PEM -text -in $crlfile -text -noout -out $tmpfile";
    } else {
        $cmd = "openssl crl -inform DER -text -in $crlfile  -text -noout -out $tmpfile";
    }
    @unlink($tmpfile);
    system($cmd);
    $content = file_get_contents($tmpfile);
    @unlink($tmpfile);
    $info = explode("\n", $content);
    $count = count($info);

    for ($i = 0; $i < $count; $i ++) {
        if ((strstr($info[$i], "Serial Number:")) && (strstr($info[$i + 1], "Revocation Date:"))) {
            $SN = explode(":", $info[$i]);
            $DATE = substr($info[$i + 1], strpos($info[$i + 1], "Revocation Date:") + strlen("Revocation Date:"));
            $data['SN'] = trim($SN[1]);
            $data['DATE'] = trim($DATE);
            $result[] = $data;
            $i ++;
        }
    }
    return isset($result) ? $result : false;
}

/**
 * sum1818 2012-09-12
 * use the openssl command to get the cert validto
 * @certcontent:the Certificate Revocation List file(PEM/DER)
 * @param $x509cert
 * @return boolean false表示当前证书未过有效期 true表示过了有效期
 * @throws Exception
 */
function isCertValid($x509cert)
{
    if (!$x509cert) {
        T(21132001);
    }
    //转化为标准openssl der格式
    $cert_split = preg_split('/(-----((BEGIN)|(END)) CERTIFICATE-----)/', $x509cert);
    $x509cert = "-----BEGIN CERTIFICATE-----\r\n" . wordwrap($cert_split[1], 64, "\r\n", true) . "\r\n-----END CERTIFICATE-----";
    //转化结束
    $cert_data = openssl_x509_parse($x509cert);
    if (!$cert_data['validTo']) {
        return false; //false表示当前证书未过有效期
    }
    //处理证书截止日期
    if (strlen($cert_data['validTo']) < 15) {
        $cert_data['validTo'] = substr("20" . $cert_data['validTo'], 0, -1);
    }
    //比较和当前时间大小
    if (strcmp(gmdate("YmdHis"), $cert_data['validTo']) <= 0) {
        return false;
    } else {
        return true;
    }
    //throw new Exception("证书内容为s！".strcmp(gmdate("YmdHis"),$cert_data['validTo']), -2);
}

/**
 * @author SUM1818
 * 验证Ukey的O是否包含单位名
 * @param $x509cert
 * @param $OName
 * @param $UKeyField
 * @throws Exception
 */
function isContainOName($x509cert, $OName, $UKeyField)
{
    if (!$x509cert) {
        T(21132001);
    }
    //转化为标准openssl pem格式
    $cert_split = preg_split('/(-----((BEGIN)|(END)) CERTIFICATE-----)/', $x509cert);
    $x509cert = "-----BEGIN CERTIFICATE-----\r\n" . wordwrap($cert_split[1], 64, "\r\n", true) . "\r\n-----END CERTIFICATE-----";
    //转化结束
    $cert_data = openssl_x509_parse($x509cert);
    unset($cert_data['purposes']); //过滤无用数据
    //file_put_contents("/tmp/logs/ukey.log",var_export($cert_data,true)."x\n",FILE_APPEND);
    foreach ($cert_data as $key => $item) {
        $temp_arr = array();
        if (!is_array($item)) {
            $temp_arr[$key] = $item;
        } else {
            $temp_arr = $item;
        }
        //不区分大小写
        $fieldVal = $temp_arr[strtolower($UKeyField)] ? $temp_arr[strtolower($UKeyField)] : $temp_arr[strtoupper($UKeyField)];
        if ($fieldVal != "") {
            $value2 = utf8ToGbk($fieldVal);
            if (strpos(strtolower($value2), strtolower($OName)) !== false) {
                return true; //如果包含了组织名
            }
        }
    }
    return false;
}
