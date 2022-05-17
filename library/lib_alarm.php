<?php

/**
 * Description: 报警相关
 * User: duanyc@infogo.com.cn
 * Date: 2021/06/25 8:53
 * Version: $Id: lib_alarm.php 156825 2021-09-15 10:08:13Z duanyc $
 */
!defined('IN_INIT') && exit('Access Denied');

class lib_alarm
{
    /**
     * 待审核报警 原CreateAlarm方法
     * PC和手机共用
     *
     * @param $aDeviceInfo
     *
     * @throws Exception
     */
    public static function createAlarm($aDeviceInfo)
    {
        $desc = L(21105001);
        cutil_exec_wait(PATH_ASM . 'sbin/warnner -l 4 -s 308 -d ' . $aDeviceInfo['DeviceID'] . ' -i ' . $aDeviceInfo['IP'] ." -x '<WARN><USERNAME>".$aDeviceInfo['UserName']."</USERNAME></WARN>'". ' -n "' . $aDeviceInfo['DevName'] . '" "' . "[" . $aDeviceInfo['DevName'] . "][" . $aDeviceInfo['IP'] . "][".$aDeviceInfo['UserName']."]：&nbsp;&nbsp;" . $desc . '"');
    }

    /**
     * 注册账号报警
     *
     * @param $aDeviceInfo
     *
     * @throws Exception
     */
    public static function createRegisterAlarm($aDeviceInfo)
    {
        //产生告警
        $warnnerArr = array(
            'userName' =>  $aDeviceInfo['UserName'],
            'trueNames' => $aDeviceInfo['TrueNames']
        );
        $alarmInfo = " [{$aDeviceInfo['DevName']}][{$aDeviceInfo['IP']}]".L(21137015, $warnnerArr);
        $cmd =  PATH_ASM . "sbin/warnner -l 1 -s 300 -d  {$aDeviceInfo['DeviceID']}  -i \"{$aDeviceInfo['IP']}\"  -x '<WARN><USERNAME>".$aDeviceInfo['UserName']."</USERNAME></WARN>' -n \"{$aDeviceInfo['DevName']}\"  \"{$alarmInfo}\"   ";
        cutil_exec_wait( $cmd);//报警
    }
}
