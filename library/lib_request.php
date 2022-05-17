<?php

/**
 * Description: 输入参数处理
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: lib_request.php 157727 2021-09-24 14:31:13Z duanyc $
 */

!defined('IN_INIT') && exit('Access Denied');

class lib_request
{
    //用户的cookie
    public static $cookies = [];
    //把GET、POST的变量合并一块，相当于 _REQUEST
    public static $requests = [];
    //_GET 变量
    public static $gets = [];
    //_POST 变量
    public static $posts = [];
    //文件变量
    public static $files = [];
    //路由配置数组
    public static $routes = [];
    //严禁保存的文件名
    public static $filter_filename = '/\.(php|pl|sh|js)$/i';
    // 语言
    public static $lang = 'zh_CN';
    // 方法
    public static $method = '';
    // csrf正则替换
    public static $pattern = array('/[^0-9a-zA-Z\=\&\(\)\/\.\?\_\'\"<>]/');
    public static $replacement = array('');

    /**
     * 初始化用户请求
     * 对于 post、get 的数据，会转到 selfrequests 数组， 并删除原来数组
     * 对于 cookie 的数据，会转到 cookies 数组，但不删除原来数组
     */
    public static function init()
    {
        self::getParams();
        self::$method = strtoupper($_SERVER['REQUEST_METHOD']);
        $gstr = empty($_SERVER['REQUEST_URI']) ? '' : str_ireplace('index.php', '', $_SERVER['REQUEST_URI']);

        if (strpos($gstr, '?') !== false) {
            list($gstr, $qstr) = explode('?', $gstr);

            if (!empty($qstr)) {
                parse_str($qstr, $_requests);
                //替换合并
                self::$gets = array_merge($_requests, self::$gets);
                self::$requests = array_merge($_requests, self::$requests);
            }
        }
        $gstr = empty($gstr) ? '' : trim(preg_replace(['#/+#', '#\.s?html?$#'], ['/', ''], $gstr), '/');

        //不处理以ct=xx 或 ac=xx开始的URI
        if ((stripos($gstr, 'ct=') !== 0 && stripos($gstr, 'ac=') !== 0)) {
            self::initRoute($gstr);
        }

        //默认ac和ct
        self::$requests['ct'] = !empty(self::$requests['ct']) ? self::$requests['ct'] : 'index';
        self::$requests['ac'] = !empty(self::$requests['ac']) ? self::$requests['ac'] : 'index';

        //处理cookie
        if (count($_COOKIE) > 0) {
            foreach ($_COOKIE as $k => $v) {
                if (preg_match('/^config/i', $k)) {
                    continue;
                }
                self::$cookies[$k] = $v;
            }
            unset($_COOKIE);
        }

        if (!empty($_FILES)) {
            self::$files = lib_upload::filter_files($_FILES);
        }

        self::$lang = self::getLang();
    }

    /***
     * 路由处理
     *
     * @param $gstr
     */
    private static function initRoute($gstr)
    {
        //初始化路由配置
        if (empty(self::$routes)) {
            include PATH_CONFIG . '/inc_route.php';
            self::$routes = $GLOBALS['ROUTE']['routes'];
        }

        if (!empty(self::$routes)) {
            //查找配置
            $pattern = '';
            $nstr = '';
            foreach (self::$routes as $regex => $val) {
                if (preg_match('#^' . $regex . '$#iu', $gstr)) {
                    $pattern = $regex;
                    $nstr = $val;
                    break;
                }
            }

            if (!empty($nstr)) {
                if (!is_array($nstr)) {
                    //替换URI
                    if (strpos($pattern, '(') !== false && strpos($nstr, '$') !== false) {
                        $nstr = preg_replace('#^' . $pattern . '$#iu', $nstr, $gstr);
                    }
                    parse_str($nstr, $requests);
                } else {
                    $requests = $nstr;
                }
                //替换合并
                self::$gets = array_merge(self::$gets, $requests);
                self::$requests = array_merge(self::$requests, $requests);
            }
        }
    }

    /**
     * 获取参数
     */
    private static function getParams()
    {
        //处理post、get
        $formarr = ['g' => $_GET, 'p' => $_POST];
        foreach ($formarr as $_k => $_r) {
            if (count($_r) > 0) {
                foreach ($_r as $k => $v) {
                    if (preg_match('/^config/i', $k)) {
                        continue;
                    }
                    self::$requests[$k] = $v;
                    if ($_k == 'p') {
                        self::$posts[$k] = $v;
                    } else {
                        self::$gets[$k] = $v;
                    }
                }
            }
        }
        unset($_POST);
        unset($_GET);
        unset($_REQUEST);
    }

    /**
     * 接收语言
     *
     * @return string
     */
    public static function getLang()
    {
        $default_lan = 'zh_CN';
        $lans = ['zh_CN', 'en_US'];
        $config = [
            'zh' => 'zh_CN',
            'en' => 'en_US',
        ];
        $lang = isset($_COOKIE['local_lguage_set']) ? trim($_COOKIE['local_lguage_set']) : 'zh';
        $lang = isset(self::$requests['local_lguage_set']) ? trim(self::$requests['local_lguage_set']) : $lang;
        $lan = isset($config[$lang]) ? $config[$lang] : $default_lan;
        $lan = in_array($lan, $lans) ? $lan : $default_lan;
        putenv('LANG=' . $lan);
        setlocale(LC_ALL, $lan . '.utf8');  //指定要用的语系，如：en_US、zh_CN、zh_TW
        return $lan;
    }

    /**
     * 获得指定表单值
     *
     * @param string $formname
     * @param string $defaultvalue
     * @param string $field
     * @param string $dataType
     */
    public static function item($formname, $defaultvalue = '', $field = '', $dataType = 'char')
    {
        $field = empty($field) ? 'requests' : trim($field) . 's';
        if (isset(self::$$field)) {
            $field = self::$$field;
            $return = $field[$formname] ?? $defaultvalue;
            switch ($dataType) {
                case 'int':
                    return (int)$return;
                case 'string':
                    return trim(clean_xss($return));
                case 'char':
                    return trim($return);
                case 'array':
                    return $return;
                default:
                    return clean_xss($return);
            }
        }
        return $defaultvalue;
    }

    /**
     * csrf校验
     * @throws Exception
     */
    public static function csrfCheck()
    {
        // 便于服务端本地调用
        if ($_SERVER['SERVER_ADDR'] === '127.0.0.1' && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
            return;
        }
        $cfgPath = PATH_ETC . 'csrf.ini';
        if (!file_exists($cfgPath)) {
            return;
        }
        $cfgarr = parse_ini_file($cfgPath);
        $enableCsrf = (int)$cfgarr['enable'] === 1;
        $isWriteLog = (int)$cfgarr['islog'] === 1;
        if (!$enableCsrf) {
            return;
        }
        $notrades = explode(',', $cfgarr['notrade']);
        $notradeConfig = [];
        foreach ($notrades as $trade) {
            if (strpos($trade, '/') !== false) {
                $ctconfig = explode('/', $trade);
                $notradeConfig[$ctconfig[0]][$ctconfig[1]] = true;
            }
        }
        if (!empty($notradeConfig[$GLOBALS['CT']][$GLOBALS['AC']])) {
            return;
        }
        self::checkRefer();
        if (self::checkToken($isWriteLog)) {
            if ($isWriteLog) {
                cutil_php_log('success from:' . get_cururl(), 'csrf');
            }
            return;
        }
        header('HTTP/1.1 401 CSRF Blocked! no pass', true, 401);
        cutil_php_log('CSRF Blocked! from:' . get_cururl(), 'csrf');
        T(21100006, ['message' => 'no pass']);
    }

    /**
     * 检查refer
     * @throws Exception
     */
    private static function checkRefer()
    {
        if (empty($_SERVER['HTTP_REFERER']) ) {
            header('HTTP/1.1 401 CSRF Blocked! no refer', true, 401);
            cutil_php_log('CSRF Blocked! from:' . get_cururl(), 'csrf');
            T(21100006, ['message' => 'no refer']);
        }
        $data = parse_url($_SERVER['HTTP_REFERER']);
        if (!self::isTrustHost($data['host'])) {
            header('HTTP/1.1 401 CSRF Blocked! refer error', true, 401);
            cutil_php_log('CSRF Blocked! from:' . get_cururl(), 'csrf');
            T(21100006, ['message' => 'refer error']);
        }
    }

    /**
     * 是否可信host
     *
     * @param $host
     *
     * @return bool
     */
    private static function isTrustHost($host)
    {
        if (empty($host)) {
            return false;
        }
        $serverAddr = getServerAddr();
        if (strpos($serverAddr, $host) !== false) {
            return true;
        }
        if (strpos($host, $serverAddr) !== false) {
            return true;
        }
        cutil_php_log("serverAddr：{$serverAddr}，host：{$host}", 'csrf');
        return false;
    }

    /**
     * 写日志
     *
     * @param $isWriteLog
     * @param $msg
     */
    private static function writeLog($isWriteLog, $msg)
    {
        if ($isWriteLog) {
            cutil_php_log($msg, 'csrf');
        }
    }

    /**
     * 检查token
     * @param $isWriteLog
     * @return bool
     * @throws Exception
     */
    private static function checkToken($isWriteLog)
    {
        if (self::notCheckToken()) {
            return true;
        }
        self::writeLog($isWriteLog, $_SERVER);
        if (!isset(self::$requests['cache'])) {
            header('HTTP/1.1 401 CSRF Blocked! no cache', true, 401);
            cutil_php_log('CSRF Blocked! no cache', 'csrf');
            T(21100006, ['message' => 'no cache']);
        }
        $cache = self::$requests['cache'];
        if (strlen($cache) === 13) {
            $cache = substr($cache, 0, 10);
        }
        $cache = (int)$cache;
        if (abs($cache - time()) > 3600) {
            // 暂时不拦截时间过期，由安检项去引导。
            //header('HTTP/1.1 401 CSRF Blocked! expire cache', true, 401);
            cutil_php_log('CSRF Blocked! expire cache: ' . abs($cache - time()), 'csrf');
            //T(21100007);
        }
        $ru = urldecode($_SERVER['REQUEST_URI']);
        $ru = (substr($ru, 0, 2) == '//') ? str_replace($ru, '//', '/') : $ru;
        self::postSetUri($isWriteLog, $ru);
        $ru = preg_replace(self::$pattern, self::$replacement, $ru);
        self::writeLog($isWriteLog, 'uri: '. $ru);
        $md5_uri = dataEncrypt($ru);
        $hea_uri = isset($_SERVER['HTTP_SESSION_NUM']) ? $_SERVER['HTTP_SESSION_NUM'] : '';
        self::writeLog($isWriteLog, 'md5_uri: '. $md5_uri);
        self::writeLog($isWriteLog, 'hea_uri: '. $hea_uri);
        if ($md5_uri === $hea_uri) {
            return true;
        }
        return false;
    }

    /**
     * post方式组装串
     *
     * @param $isWriteLog
     * @param $ru
     */
    private static function postSetUri($isWriteLog, &$ru)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::writeLog($isWriteLog, ['post' => self::$posts]);
            $newArrStr = array();
            foreach (self::$posts as $postkey => $postitem) {
                if ($postkey == 'GET_SESSION_NUM') {
                    continue;
                }
                //处理类似复选框提交的数组内容，如name[]
                if (is_array($postitem)) {
                    foreach ($postitem as $postchilditem) {
                        $postchilditem = urldecode(stripslashes($postchilditem));
                        $newArrStr[] = urldecode($postkey . '[]') . '=' . $postchilditem;
                    }
                } else {
                    $postitem = urldecode(stripslashes($postitem));
                    $newArrStr[] = urldecode($postkey) . '=' . $postitem;
                }
            }
            $newstr = implode('&', $newArrStr);
            $ru .= !empty($newstr) ? '&' . $newstr : '';
        }
    }

    /**
     * 不检查token
     * @return bool
     */
    private static function notCheckToken()
    {
        //排除内部访问
        if ($_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR']) {
            return true;
        }
        return false;
    }
}
