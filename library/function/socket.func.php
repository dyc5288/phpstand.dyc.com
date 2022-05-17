<?php
/**
 * Description: Socket相关函数
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: socket.func.php 174717 2022-04-28 02:41:13Z duanyc $
 */

/**
 * 通过socket发送报文
 *
 * @param string $ip 目标IP
 * @param string $port 目标端口
 * @param string $buf 发送内容
 *
 * @return string $returnbuf 返回内容
 */

function sendSocketFunc($ip, $port, $buf)
{
    if (PHP_OS == 'WINNT') {
        return '';
    }
    if (strpos($buf, PATH_ASM . "sbin/set_client_one") !== false) {//20160713用于记录发送执行放开网络或断开网络命令的源文件
        $logFile = "sendSocketFunc-" . date("Ymd", time()) . ".log";
        $stack = debug_backtrace();
        $file = basename($stack [0] ['file']);
        $line = $stack [0] ['line'];
        file_put_contents($logFile, date("H:i:s") . "  $file($line) " . $buf . "\r\n", FILE_APPEND);
        $logFile = "";
        $file = "";
        $stack = "";
        $line = "";
    }
    if ($port == '36532') {
        $len = 4 + 32 + strlen($buf);

        $tlen = 32 - strlen("system_wait");

        $cmdtype = "system_wait" . sprintf("%${tlen}s", "");

        $head = pack("L", $len) . $cmdtype;

        $buf = $head . $buf;
    }
    $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$sock) {
        throw new Exception("socket create failure", -2);
    }

    $timeout = [
        'sec'  => 10,
        'usec' => 100000,
    ];
    socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, $timeout);


    if (!@socket_sendto($sock, $buf, strlen($buf), 0, $ip, $port)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        throw new Exception("socket sendto failure: [$errorcode] $errormsg IP:[$ip] Port:[$port] buf:[$buf]", -2);
    }
    $buf = socket_recvfrom($sock, $buf, 4, 0, $ip, $port);
    if ($buf === false) {
        $buf = "10000"; // timeout val
    }
    @socket_close($sock);
    return $buf;
}

/**
 * 通过socket发送并接收报文
 *
 * @param string $ip 目标IP
 * @param string $port 目标IP
 * @param string $buf 发送内容
 * @param string $timeOutSec 超时
 * @param string $bufOut 返回的内容
 *
 * @return string $bufOUt 返回的内容
 */
function UdpSendAndRecv($ip, $port, $buf, $timeOutSec, &$bufOut)
{
    $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$sock) {
        throw new Exception("socket create failure", -2);
    }
    socket_set_option(
        $sock,
        SOL_SOCKET,
        SO_RCVTIMEO,
        [
        "sec"  => $timeOutSec,
        "usec" => 0,
        ]
    );
    @socket_sendto($sock, $buf, strlen($buf), 0, $ip, $port);
    @socket_recvfrom($sock, $bufOut, 8096, 0, $Ip, $port);
    @socket_close($sock);
    return $bufOut;
}

/**
 * 通过socket发送报文至db Agent
 *
 * @param string $ip
 * @param string $port
 * @param string $buf
 *
 * @return string $bufout
 */
function AsmDbAgent($buf, &$bufOut = 0, $outLen = 0, $flag = 0, $ip = '127.0.0.1', $port = 38888)
{
    date_default_timezone_set("Asia/Shanghai");
    $stack = debug_backtrace();
    $file = basename($stack [0] ['file']);
    $line = $stack [0] ['line'];
    $pid = posix_getpid();

    $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$sock) {
        cutil_php_log("socket create failure", "php_db_agent_err.log");
        return $bufOut;
    }
    socket_set_option(
        $sock,
        SOL_SOCKET,
        SO_RCVTIMEO,
        [
        "sec"  => 1,
        "usec" => 0,
        ]
    );
    $tmpbuf = 'DB' . $flag . $pid . ' ' . $file . '(' . $line . ')' . $buf;
    if (!@socket_sendto($sock, $tmpbuf, strlen($tmpbuf), 0, $ip, $port)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        cutil_php_log("socket sendto failure: [$errorcode] $errormsg IP:[$ip] Port:[$port] buf:[$buf]", "php_db_agent_err.log");
        @socket_close($sock);
        return $bufOut;
    }
    if ($flag != 0) {
        @socket_recvfrom($sock, $bufOut, $outLen, 0, $Ip, $port);
    }
    @socket_close($sock);
    return $bufOut;
}

/**
 * 零信任下发消息
 *
 * @param $tradeType
 * @param $tradeCode
 * @param $data
 *
 * @throws Exception
 */
function WorkerUdpSend($tradeType, $tradeCode, $data)
{
    $udpData = ['TradeCode' => $tradeType, 'TradeType' => $tradeCode];
    $udpData['SerialNumber'] = microtime(true);
    $udpData['Data'] = $data;
    UdpSend('127.0.0.1', 20202, json_encode($udpData), 1);
}

/**
 * 通过socket发送 不接收报文
 *
 * @param string $ip
 * @param string $port
 * @param string $buf
 *
 * @return void
 */
function UdpSend($ip, $port, $buf, $timeOutSec)
{
    $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$sock) {
        throw new Exception("socket create failure", -2);
    }
    socket_set_option(
        $sock,
        SOL_SOCKET,
        SO_RCVTIMEO,
        [
        "sec"  => $timeOutSec,
        "usec" => 0,
        ]
    );
    @socket_sendto($sock, $buf, strlen($buf), 0, $ip, $port);
    @socket_close($sock);
    return;
}


/**
 * 发送报文到客户端
 *
 * @param string $tradecode 交易名称
 * @param string $req 要发送的报文
 * @param string $deviceid 设备ID
 * @param string $ip 目标IP
 * @param string $port 目标端口
 *
 * @return string 返回字符串xml
 */
function client_exchange_tcp($tradecode, $req, $deviceid, $ip = '127.0.0.1', $port = '36527')
{
    $key = "asmkey"; // 密匙
    $orglen = strlen($req);
    $req = gzcompress($req);
    $req = doXorEncrypt($req, $key);
    $len = strlen($req) + 60;
    $sn = rand();
    if ($sn == 0) {
        $sn = 0x1230;
    }
    $tradecode = $tradecode . pack("c", 0) . $deviceid . pack("c", 0);
    $tradecode = doXorEncrypt(sprintf("%-32s", $tradecode), $key);
    $head = pack("L", $len) . "ASM5" . pack("LLL", 5, 1, 1) . $tradecode . pack("LL", $sn, $orglen);
    $req = $head . $req;
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) {
        return false;
    }
    socket_set_option(
        $socket,
        SOL_SOCKET,
        SO_RCVTIMEO,
        [
        "sec"  => 60,
        "usec" => 1000,
        ]
    );
    if (!socket_connect($socket, $ip, $port)) {
        return false;
    }
    @socket_write($socket, $req);
    $bufOut = socket_read($socket, 1024 * 64);
    @socket_close($socket);
    if (strlen($bufOut) < 60) {
        return false;
    }

    $res = substr($bufOut, 60);
    $head = substr($bufOut, 0, 60);
    $AsmPacketHead = unpack("LnLen/LcVersion/LcType/LcZip/LcCrypt/L8tmp/LnSerial/LnOriSize", $head);
    if ($AsmPacketHead ['cCrypt']) {
        $res = doXorDecrypt($res, "infogo");
    }
    if ($AsmPacketHead ['cZip']) {
        $res = gzuncompress($res);
    }
    return $res;
}

// echo date("Y-m-d H:i:s", time()) . " 通过Socket给客户机发送报文...\r\n";

/*
 * 处理socket交互
 * @param string $tradecode 交易名称
 * @param string $req 要发送的报文
 * @param string $ip 目标IP
 * @param int $client_port 目标端口
 * @param string $net_timeout 超时时间
 * @param string $deviceid 设备ID
 * @return 字符串xml
 */
function client_exchange_udp($tradecode, $req, $ip, $client_port, $net_timeout, $deviceid = "123")
{
    $key = "asmkey"; // 密匙
    $orglen = strlen($req);
    $req = gzcompress($req);
    $req = doXorEncrypt($req, $key);
    $len = strlen($req) + 60;
    srand(time());
    $sn = rand();
    if ($sn == 0) {
        $sn = 123;
    }
    $tlen = 32 - strlen($tradecode) - 1 - 8 - 1;
    $agentip = sprintf("%08X%${tlen}s", $deviceid, "");
    $head = pack("LLLLL", $len, 3, 5, 1, 1) . $tradecode . pack("c", 0) . $agentip . pack("cLL", 0, $sn, $orglen);
    $socket = @socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$socket) {
        return false;
    }
    socket_set_option(
        $socket,
        SOL_SOCKET,
        SO_RCVTIMEO,
        [
        "sec"  => $net_timeout,
        "usec" => 1000,
        ]
    );

    $req = $head . $req;

    @socket_sendto($socket, $req, $len, 0, $ip, $client_port);

    $bufOut = "";
    @socket_recvfrom($socket, $bufOut, 1024 * 64, 0, $Ip, $port);

    @socket_close($socket);

    $res = substr($bufOut, 60);
    $head = substr($bufOut, 0, 60);

    $first = unpack("Llen/Lver/Ltype/Lzip/Lcrypt", $head);
    $second = trim(substr($head, 20, 32));
    $third = unpack("Lsn/Lorg", substr($head, 52, 8));

    $len = $first ['len'] - 60;

    if ($first ['crypt']) {
        $res = doXorDecrypt($res, $key);
    }
    if ($first ['zip']) {
        $res = gzuncompress($res);
    }

    return $res;
}

/**
 * 向服务端通过udp方式发送xml报文.
 * ps: 不同协议头处理方式不一样
 *
 * @param string $sendXml 要发送的报文
 * @param string $tradeCode 交易名称
 * @param int $serialNumber 序列号
 * @param int $timeOut 超时时间,单位：微秒
 * @param string $ip 目标IP
 * @param int $port 目标端口
 *
 * @return array
 */
function udp_send_xml($sendXml, $tradeCode, $timeOut = 2000, $serialNumber = 0, $ip = '127.0.0.1', $port = 37528)
{
    $returnData = [
        'code'    => 1,
        'message' => 'success',
        'data'    => [],
    ];
    try {
        $startTime = microtime(true);
        $key = "infogo"; // 密匙
        $orglen = strlen($sendXml);
        $sendXml = gzcompress($sendXml);
        $sendXml = doXorEncrypt($sendXml, $key);
        $len = strlen($sendXml) + 60;

        if ($serialNumber === 0) {
            $serialNumber = random_int(100000, 999999);
        }
        $originalTradeCode = $tradeCode;
        // 报文总长度,内部版本号(ASM5),协议类型(1001),压缩标志位(0),加密标志位(0),交易名(),序列号,报文真实长度
        $tradeCode .= pack('cc', 0, 0);
        $tradeCode = doXorEncrypt(sprintf("%-32s", $tradeCode), $key);
        $head = pack("L", $len) . "ASM5" . pack("LLL", 1001, 1, 1) . $tradeCode . pack("LL", $serialNumber, $orglen);
        $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$socket) {
            $errorCode = socket_last_error();
            $errorMsg = socket_strerror($errorCode);
            throw new Exception($errorMsg, $errorCode);
        }

        socket_set_option(
            $socket,
            SOL_SOCKET,
            SO_RCVTIMEO,
            [
            "sec"  => 0,
            "usec" => $timeOut,
            ]
        );

        $sendXml = $head . $sendXml;

        @socket_sendto($socket, $sendXml, $len, 0, $ip, $port);

        $bufOut = "";
        // UDP不是可靠的传输协议，当一次接收出现错误时，继续接收,直到超过5秒(Resource temporarily unavailable,资源暂时不可用,错误码11)
        while (true) {
            $res = @socket_recvfrom($socket, $bufOut, 1024 * 64, 0, $Ip, $Port);
            if ($res) {
                break;
            }
            $errorCode = socket_last_error();
            // 当错误码为11 资源暂时不可用时继续接收，其他错误直接抛出异常
            if ($errorCode === 11) {
                continue;
            }
            $errorMsg = socket_strerror($errorCode);
            throw new Exception($errorMsg, $errorCode);
        }

        @socket_close($socket);

        $res = substr($bufOut, 60);
        $head = substr($bufOut, 0, 60);

        // 报文解析错误
        if (!$res) {
            throw new Exception('Received message is empty');
        }

        $first = unpack("Llen/Lver/Ltype/Lzip/Lcrypt", $head);
        $second = trim(substr($head, 20, 32));
        $third = unpack("Lsn/Lorg", substr($head, 52, 8));
        $len = $first ['len'] - 60;

        if ($first ['crypt']) {
            $res = doXorDecrypt($res, $key);
        }
        if ($first ['zip']) {
            $res = gzuncompress($res);
        }
        $returnData['data']['receiveXml'] = $res;

        $endTime = microtime(true);
        cutil_php_log($originalTradeCode . 'Transaction processing time:' . round($endTime - $startTime, 8), 'device_operation', 'INFO');
    } catch (\Exception $e) {
        $returnData['code'] = $e->getCode();
        $returnData['message'] = $e->getMessage();
    }

    return $returnData;
}

/**
 * 通过IP向MVG进程查询MAC地址, 向MVG模块
 *
 * @param string $DevIp IP地址
 * @return string $ascip
 * @throws Exception
 */
function QueryMacFromMvg($DevIp, $ascip = "127.0.0.1")
{
    $bufRecv = "";
    $ascip = strlen(trim($ascip)) > 0 ? $ascip : "127.0.0.1";
    $bufSend = "<SocketTelnetCmd><CmdName>GetDevInfo</CmdName><Ip>" . $DevIp . "</Ip><Mac></Mac></SocketTelnetCmd>";
    UdpSendAndRecv($ascip, 36540, $bufSend, 2, $bufRecv);
    /*
     * <SocketTelnetCmd><Ip>192.168.54.60</Ip><Mac>AA:BB:CC:DD:EE:FF</Mac><Switch>192.1
     * 68.54.253</Switch><SwitchPort>Fa0/8</SwitchPort><VlanId>154</VlanId><IsHub>0</Is
     * Hub></SocketTelnetCmd>
     */
    // echo $bufRecv;
    $nStart = strrpos($bufRecv, "<Mac>") + strlen("<Mac>");
    $nStop = strrpos($bufRecv, "</Mac>");
    return substr($bufRecv, $nStart, $nStop - $nStart);
}
