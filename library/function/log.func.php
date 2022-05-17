<?php
/**
 * Description: 日志记录公共函数库
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: log.func.php 161400 2021-11-10 15:33:46Z duanyc $
 */

/**
 *
 * 记录日志用.
 *
 * @param string|array $info 记录信息 ，
 * @param string $logPrefix 路径 debug
 * @param string $logType 日志类型 默认为INFO
 *                          "DEBUG" ‐ debug信息、细粒度信息事件
 *                          "INFO" ‐ 重要事件、强调应用程序的运行过程
 *                          "NOTICE" ‐ 一般重要性事件、执行过程中较INFO级别更为重要的信息
 *                          "WARNING" ‐ 出现了非错误性的异常信息、潜在异常信息、需要关注并且需要修复
 *                          "ERROR" ‐ 运行时出现的错误、不必要立即进行修复、不影响整个逻辑的运行、需要记录并做检测
 *                          "CRITICAL" ‐ 紧急情况、需要立刻进行修复、程序组件不可用
 *                          "ALERT" ‐ 必须立即采取行动的紧急事件、需要立即通知相关人员紧急修复
 *                          "EMERGENCY" ‐ 系统不可用
 * @return false|string
 */
function cutil_php_log($info, $logPrefix = 'debug', $logType = 'INFO')
{
    if (PHP_OS === 'WINNT') {
        return false;
    }
    static $first = null;
    static $ignore = null;
    if ($first === null) {
        $first = true;
        $ignore = ['log.func.php' => 1, 'route.func.php' => 1];
        if (PHP_SAPI !== 'cli') {
            $ignore['index.php'] = 1;
        }
    }
    try {
        $info = !is_string($info) ? var_export($info, true) : $info;
        $gbkInfo = @iconv('UTF-8', 'GBK', $info);
        $info = !empty($gbkInfo) ? $gbkInfo : $info;
        date_default_timezone_set('Asia/Shanghai');
        $logPrefix = PATH_LOG . '/access_' . str_replace(PATH_LOG . '/', '', $logPrefix);
        $logSuffix = (PHP_SAPI === 'cli') ? '_cli' : '';
        $logFile = $logPrefix . $logSuffix . '-' . date('Ymd') . '.log';
        $stack = debug_backtrace();
        $pid = posix_getpid();

        $files = '';
        $stack = array_reverse($stack);
        $stackLength = count($stack);
        // 系统中基于cutil_php_log封装记录日志的方法
        $logFunctions = ['thisClassLog', 'print_str', 'writeLog'];

        $logType = strtoupper($logType);
        $logTypeArray = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
        if (!\in_array($logType, $logTypeArray, true)) {
            $logType = 'INFO';
        }

        foreach ($stack as $k => $item) {
            if (!isset($item['file'])) {
                $files = (isset($item['class']) ? $item['class'] . '::' : '') . $item['function'] . '->';
            } else if (1 === $stackLength) {
                $files = basename($item['file']) . '(' . $item['line'] . ')->';
            } else {
                // 如果在同一个文件中多级调用，则只记录最后一次的记录
                if (isset($stack[$k + 1]['file']) && $item['file'] === $stack[$k + 1]['file']) {
                    continue;
                }

                $filename = basename($item['file']);
                // 如果是本函数文件、路由文件忽略不计
                if (!empty($ignore[$filename])) {
                    continue;
                }
                $files .= $filename . '(' . $item['line'] . ')->';

                // 如果当前function是封装的记录日志的函数，则下一步的trace记录只是记录日志没有实际意义，跳出循环
                if (in_array($item['function'], $logFunctions, true)) {
                    break;
                }
            }
        }
        $ip = get_client_ip();
        $files = substr($files, 0, -2) . ' ';
        $microTime = explode(' ', microtime());
        $content = date('H:i:s') . '(' . sprintf('%.4f', round($microTime[0], 4)) . ") [{$logType}] {$ip} {$pid} {$files}" . $info . PHP_EOL;

        // is_writable此函数的结果会被缓存 update by RC 2021/10/25
        // 在对同一个文件名进行多次操作并且需要该文件信息不被缓存时才需要调用 clearstatcache()
        if (is_file($logFile) && !is_writable($logFile)) {
            $cmd = "chown apache.apache $logFile";
            cutil_exec_wait($cmd);
        }

        file_put_contents($logFile, $content, FILE_APPEND);
    } catch (Exception $e) {
        // 日志写入异常
        error_log($e->getMessage());
    }
    return $logFile;
}

/**
 * 记录日志用 debug信息、细粒度信息事件.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 文件名 jsdebug
 */
function cutil_php_debug($info, $logPrefix = 'php_debug')
{
    cutil_php_log($info, $logPrefix, 'DEBUG');
}

/**
 * 记录日志用，重要事件、强调应用程序的运行过程.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 info
 */
function cutil_php_info($info, $logPrefix = 'info')
{
    cutil_php_log($info, $logPrefix, 'INFO');
}

/**
 * 记录日志用，一般重要性事件、执行过程中较INFO级别更为重要的信息.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 notice
 */
function cutil_php_notice($info, $logPrefix = 'notice')
{
    cutil_php_log($info, $logPrefix, 'NOTICE');
}

/**
 * 记录日志用，出现了非错误性的异常信息、潜在异常信息、需要关注并且需要修复.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 warning
 */
function cutil_php_warning($info, $logPrefix = 'warning')
{
    cutil_php_log($info, $logPrefix, 'WARNING');
}

/**
 * 记录日志用，运行时出现的错误、不必要立即进行修复、不影响整个逻辑的运行、需要记录并做检测.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 error
 */
function cutil_php_error($info, $logPrefix = 'error')
{
    cutil_php_log($info, $logPrefix, 'ERROR');
}

/**
 * 记录日志用，紧急情况、需要立刻进行修复、程序组件不可用.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 critical
 */
function cutil_php_critical($info, $logPrefix = 'critical')
{
    cutil_php_log($info, $logPrefix, 'CRITICAL');
}

/**
 * 记录日志用，必须立即采取行动的紧急事件、需要立即通知相关人员紧急修复.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 alert
 */
function cutil_php_alert($info, $logPrefix = 'alert')
{
    cutil_php_log($info, $logPrefix, 'ALERT');
}

/**
 * 记录日志用，系统不可用.
 *
 * @param string $info 记录信息
 * @param string $logPrefix 路径 emergency
 */
function cutil_php_emergency($info, $logPrefix = 'emergency')
{
    cutil_php_log($info, $logPrefix, 'EMERGENCY');
}

/**
 * 打印日志
 *
 * @param mixed $data
 * @param mixed $post
 *
 * @return void
 */
function debug($data, $post = false)
{
    $params = [];
    $params['data'] = $data;
    $params['url'] = get_cururl();
    $params['posts'] = $post ? $_REQUEST : '';
    cutil_php_log(var_export($params, true), 'debug');
}

/**
 * 错误接管函数
 *
 * @param string $errno
 * @param string $errmsg
 * @param string $filename
 * @param string $linenum
 * @param string $vars
 *
 * @return void
 */
function debug_error_handler($errno, $errmsg, $filename, $linenum, $vars)
{
    $return = [];
    $return['errno'] = $errno;
    $return['errmsg'] = $errmsg;
    $return['filename'] = $filename;
    $return['linenum'] = $linenum;
    $return['vars'] = $vars;
    $params = [];
    $params['data'] = json_encode($return);
    $params['url'] = get_cururl();
    $params['posts'] = lib_request::$requests;
    cutil_php_log($params, 'php_error');
}

/**
 * 日志转发用.
 *
 * @param string $type 日志类型
 * @param string $log_alert 转发消息
 *
 * @throws Exception
 */
function check_log_forward($type, $log_alert)
{
    $file_path = PATH_ETC . 'warnner.ini';
    if (file_exists($file_path)) {
        $fileData = read_inifile($file_path);
        if (!empty($fileData[$type]) && 1 === (int)$fileData[$type]) {
            $fileData['CharSet'] = $fileData['CharSet'] ?: 'GBK';
            $log_alert = iconv('UTF-8', $fileData['CharSet'], $log_alert);
            $cmd_alert = "logger -p local6.info \"{$log_alert}\"";
            cutil_exec_no_wait($cmd_alert, 10, "127.0.0.1", 0);
        }
    }
}
