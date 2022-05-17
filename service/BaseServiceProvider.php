<?php
/**
 * Description: 服务类预留
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: BaseServiceProvider.php 153494 2021-08-19 01:25:07Z duanyc $
 */
class BaseServiceProvider
{
    /**
     * 服务层日志
     * @var string
     */
    public static $logFile = 'service';

    /**
     * 写日志.
     *
     * @param string $content 日志内容
     * @param string $logType 日志级别
     *                        "DEBUG" ‐ debug信息、细粒度信息事件
     *                        "INFO" ‐ 重要事件、强调应用程序的运行过程
     *                        "NOTICE" ‐ 一般重要性事件、执行过程中较INFO级别更为重要的信息
     *                        "WARNING" ‐ 出现了非错误性的异常信息、潜在异常信息、需要关注并且需要修复
     *                        "ERROR" ‐ 运行时出现的错误、不必要立即进行修复、不影响整个逻辑的运行、需要记录并做检测
     *                        "CRITICAL" ‐ 紧急情况、需要立刻进行修复、程序组件不可用
     *                        "ALERT" ‐ 必须立即采取行动的紧急事件、需要立即通知相关人员紧急修复
     *                        "EMERGENCY" ‐ 系统不可用
     */
    public static function log(string $content, $logType = 'INFO'):void
    {
        cutil_php_log($content, static::$logFile, $logType);
    }

    /**
     * 获取控制器配置 是否使用全局配置
     *
     * @param string $ascid
     *
     * @return array
     * @throws Exception
     */
    public static function getControllerConfig($ascid = '11:11:11:11:11:11')
    {
        $c_path = PATH_ETC . 'asm/asc/etc/tbridge_comm.ini';
        $aData = DevASCInfoModel::getSingle(['AscID' => $ascid]);
        if ($aData['IsUseRemote'] == '1') {
            $c_path = PATH_ETC . "asm/tbridge_comm.ini";
        }
        return parse_initfile($c_path);
    }

    /**
     * 获取mac地址
     * @return string
     * @throws Exception
     */
    public static function getMacAddr()
    {
        $config_file = read_inifile(PATH_ETC . "devinfo.ini");
        if (isset($config_file ["devtype"]) && ($config_file ["devtype"] === "asm" || $config_file ["devtype"] === "dasm")) {
            return "11:11:11:11:11:11";
        } else {
            $address_file = PATH_SYS . 'class/net/eth0/address';
            $file_value = '';
            if (self::checkIsAscha()) {
                $address_file = PATH_ETC . 'ha_ascid';
            }

            if (file_exists($address_file)) {
                $file_value = file_get_contents($address_file);
            }

            return trim($file_value);
        }
    }

    /**
     * 检查是否asc、ha
     * @return int
     * @throws Exception
     */
    public static function checkIsAscha()
    {
        $ha_file = read_inifile(PATH_ETC . "ha.ini");
        $type_file = read_inifile(PATH_ETC . "devinfo.ini");
        if ($ha_file["Mode"] == "DUALHOST" && isset($type_file["devtype"]) && ($type_file["devtype"] == "asc" || $type_file["devtype"] == "dasc")) {
            return 1;
        }

        return 0;
    }
}
