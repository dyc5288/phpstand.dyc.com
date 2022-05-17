<?php

/**
 * Description: 命令执行
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: exec.func.php 157619 2021-09-23 15:19:20Z duanyc $
 */

/**
 * 重启系统服务
 *
 * @param string $servicename
 *
 * @throws Exception
 */
function restartService($servicename)
{
    $result = cutil_exec_wait("service $servicename restart", 10, '127.0.0.1'); // 重启服务
    return $result;
}

/**
 * @brief cutil_exec_wait
 *
 * @Param: $cmd
 * @Param: $TimeOut
 * @Param: $ip
 * @throws Exception
 */
function cutil_exec_cmd($cmd, $flag = false, $ip = "127.0.0.1", $TimeOut = 10)
{
    $cmd = RemoveSpecialStr($cmd);
    $head_flag = 'system_no_wait';
    if ($flag) {
        $head_flag = 'system_wait';
    }
    $len = 4 + 32 + strlen($cmd);
    $tlen = 32 - strlen($head_flag);
    $cmdtype = $head_flag . sprintf("%${tlen}s", "");
    $head = pack("L", $len) . $cmdtype;
    $bufOut = "";
    $buf = $head . $cmd;
    $port = 36532;
    $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);

    if (!$sock) {
        cutil_php_log("socket create failure", "/cutil_exec_cmd");
        return false;
    }

    $timeout = [
        'sec'  => (int)$TimeOut,
        'usec' => 100000,
    ];
    socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, $timeout);
    //socket_set_option($sock,SOL_SOCKET,SO_SNDTIMEO,$timeout);
    cutil_php_log("begin socket sendto:  IP:[$ip] Port:[$port] buf:[$buf]\n", "cutil_exec_cmd");
    if (!@socket_sendto($sock, $buf, $len, 0, $ip, $port)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        @socket_close($sock);
        cutil_php_log("socket sendto failure: [$errorcode] $errormsg IP:[$ip] Port:[$port] buf:[$buf]", "cutil_exec_cmd");
        return false;
        //throw new Exception("socket sendto failure: [$errorcode] $errormsg IP:[$ip] Port:[$port] buf:[$buf]", -2);
    }

    if ($flag) {
        $recret = socket_recvfrom($sock, $buf, 64 * 1024, MSG_WAITALL, $ip, $port);
        if (!$recret) {
            $buf = $cmd . "run timeout!"; // timeout val
            //echo "$recret\n";
            cutil_php_log($buf, "cutil_exec_cmd");
            //          return false;
        }
    }

    @socket_close($sock);
    return $buf;
}

/**
 * 系统级别的shell_exec
 * 不等待命令$cmd执行完毕, 立即返回
 * cutil_exec_no_wait("service httpd restart")
 * $cmd后面结尾如果不是以&符号结尾的, 强制加上'&'符号,
 *
 * @param $cmd
 * @param int $TimeOut
 * @param string $ip
 * @param int $encode
 *
 * @return bool|string
 * @throws Exception
 */
function cutil_exec_no_wait($cmd, $TimeOut = 10, $ip = "127.0.0.1", $encode = 1)
{
    if (strpos($cmd, PATH_ASM . "sbin/set_client_one") !== false) {//20160713用于记录发送执行放开网络或断开网络命令的源文件
        cutil_php_log($cmd, 'cutil_exec_no_wait');
    }
    if (!empty($encode)) {
        $cmd = utf8ToGbk($cmd);
    }
    return cutil_exec_cmd($cmd, false, $ip, $TimeOut);
}


/**
 * 系统级别的shell_exec
 * 默认超时为10秒
 * 等待命令$cmd执行完毕后返回
 * cutil_exec_wait("service httpd restart")
 * @param $cmd
 * @param int $TimeOut
 * @param string $ip
 * @param int $encode
 * @throws Exception
 */
function cutil_exec_wait($cmd, $TimeOut = 10, $ip = "127.0.0.1", $encode = 1)
{
    if (strpos($cmd, PATH_ASM . "sbin/set_client_one") !== false) {//20160713用于记录发送执行放开网络或断开网络命令的源文件
        cutil_php_log($cmd, 'cutil_exec_wait');
    }
    if (!empty($encode)) {
        $cmd = utf8ToGbk($cmd);
    }
    return cutil_exec_cmd($cmd, true, $ip, $TimeOut);
}

/**
 * apache 进程权限级别的shell_exec
 * 主要用于shell_exec传递参数
 * 禁止使用的字符
 * 如果没有传递的参数 ,建议使用原始的shell_exec
 *
 * @param string $cmd
 *
 * @throws Exception
 */
function cutil_shell_exec($cmd)
{
    $cmd = RemoveSpecialStr($cmd);
    return shell_exec($cmd);
}

/**
 * @param $remoteIP  string 需同步的远端IP
 * @param $remoteFile  string  需同步的文件名
 * @param $localFolder  string  本地目录
 *
 * @throws Exception
 */
function rsync_file($remoteIP, $remoteFile, $localFolder)
{
    $interval = 1;
    $socTime = 0;
    do {
        sleep($interval);
        $socTime += $interval;
        if ($remoteIP !== '127.0.0.1') {
            $rsyncComd = PATH_ASM . "sbin/rsync -azSv --delete --timeout=5 --contimeout=5 --port=36589 --password-file=".PATH_ETC."rsyncd/rsyncd.pass  asm@$remoteIP::asm$remoteFile $localFolder >> /dev/null 2>&1";
            cutil_exec_wait($rsyncComd);
        }
    } while ($socTime < 30 && !is_file($remoteFile));
    if ($remoteIP !== '127.0.0.1') {
        $cmd = "rm -rf $remoteFile";
        cutil_exec_no_wait($cmd, 10, $remoteIP);
    }
}

/**
 * MQ JSON数据发布程序
 *
 * @param $topic      string 发布主题
 * @param $messageArr array 发布消息
 * @param $isWait     bool 是否等待
 * @param $qos        int 消息的服务质量，默认2(取值 0/1/2)
 * @param $host       string 指定IP
 *
 * @return string     命令执行是否成功，使用wait 才有意义
 * @explain
 * QoS 0 —— 最多1次.最小的等级就是 0。并且它保证一次信息尽力交付。一个消息不会被接收端应答，也不会被发送者存储并再发送。这个也被叫做“即发即弃”。并且在TCP协议下也是会有相同的担保。
 * QoS 1 ——最少1次.当使用QoS 等级1 时， 它保证信息将会被至少发送一次给接受者。但是消息也可能被发送两次甚至更多。
 * QoS 2 ——最高的QoS就是2.它会确保每个消息都只被接收到的一次，他是最安全也是最慢的服务等级。
 *
 */
function pub_mq_jsonmsg($topic, $messageArr, $isWait, $qos = 2, $host = 'localhost')
{
    $messageTmp = str_replace('\\/', '/', json_encode($messageArr));
    $message = base64_encode($messageTmp);
    $length = strlen($message);
    if ($length >= 102400) {
        $md5File = '' . dataEncrypt($message) . '-' . date('Ymd') . '.log';
        file_put_contents($md5File, $length . "\n" . $message);
        $cmd = PATH_ASM . "sbin/DascPubMssage -t '$topic' -f '$md5File' -q $qos -H $host";
    } else {
        $cmd = PATH_ASM . "sbin/DascPubMssage -t '$topic' -m '$message' -q $qos -H $host";
    }
    cutil_php_log($cmd, 'pub_mq_jsonmsg');
    if ($isWait) {
        return shell_exec($cmd);
    }
    return shell_exec($cmd . ' &');
}
