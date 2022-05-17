<?php
/**
 * Description: 双机操作类
 * User: duanyc@infogo.com.cn
 * Date: 2021/05/21 15:46
 * Version: $Id: ha.func.php 148815 2021-07-02 08:37:32Z duanyc $
 */

if (!function_exists('getManagerIp')) {

    /**
     * 获取管理口IP
     * @return mixed
     * @throws Exception
     */
    function getManagerIp()
    {
        $managerAddFile = shell_exec('find /etc/sysconfig/network-scripts/ -name "ifcfg-eth*" | xargs grep -ril "manager"');
        $managerAddFileArray = explode("\n", $managerAddFile);
        //只找出正在运行的端口 xiaoxj 20210306
        foreach ($managerAddFileArray as $item) {
            if (!empty($item)) {
                $managerAddEth = explode("ifcfg-", $item);
                $managerAddEth=$managerAddEth[1];
                if (!empty($managerAddEth)) {
                    $result = cutil_exec_wait("ifconfig $managerAddEth", 10, '127.0.0.1');
                    if (stripos($result, 'RUNNING') !== false) {
                        $managerAddFile = $item;
                        break;
                    }
                }
            }
        }
        $manager_add = parse_ini_file(trim($managerAddFile));
        $ha_dir = "/etc/ha.ini";
        $hainfo=parse_ini_file($ha_dir);
        if (isset($hainfo['Mode'])) {
            if ($hainfo['Mode'] == 'DUALHOST' && isset($hainfo['MANAGER_VIRTUAL_IP']) && filter_var($hainfo['MANAGER_VIRTUAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $hainfo['MANAGER_VIRTUAL_IP'];
            } else {
                if (isset($hainfo['MANAGER_REAL_IP']) && filter_var($hainfo['MANAGER_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $hainfo['MANAGER_REAL_IP'];
                } else {
                    return $manager_add['IPADDR'];
                }
            }
        }
        return $manager_add['IPADDR'];
    }
}
