<?php

/**
 * Description: 安装脚本
 * User: duanyc@infogo.com.cn
 * Date: 2020/11/29 23:32
 * Version: $Id: install.php 164888 2021-12-15 01:48:08Z duanyc $
 */
/* 定义关键常量 */
define('PATH_CUR', __DIR__);

/** 初始化 */
require PATH_CUR . '/../init.php';

class Install
{
    /**
     * 入网模块升级2722脚本
     * @param $flag
     * @throws Exception
     */
    public static function upgrade_2722($flag)
    {
        cutil_php_log('upgrade_2722 start:', 'upgrade');
        $yes = isset($flag['yes']) ? intval($flag['yes']) : 0;
        // 初始化分布式ID最大值
        $sql = "SELECT MAX(ID) as MID FROM TAuthUser";
        $user = lib_database::getOne($sql);
        $sql = "SELECT MAX(DepartID) as MID FROM TDepart";
        $depart = lib_database::getOne($sql);
        $maxId = max($user['MID'], $depart['MID']);
        if (!empty($maxId)) {
            $sql = "alter table TSequenceID auto_increment={$maxId}";
            $res = $yes ? lib_database::query($sql) : false;
            printf("TSequenceID Init {$maxId}: ".json_encode($res).PHP_EOL);
        }

        // 修复设备子类型数据
        cutil_php_log('upgrade_2722 start subtype:', 'upgrade');
        $sql = "select DeviceID,OSName from TComputer";
        $computerList = lib_database::getAll($sql);
        $table = hlp_common::getSplitTable(null, 'TDevice');
        if (!empty($computerList)) {
            foreach ($computerList as $computer) {
                $sql = "select SubType, `Type` from TDevice where DeviceID = ?";
                $osType = hlp_common::getOsTypeByOsName($computer['OSName']);
                $subType = hlp_common::getSubType($osType);
                $device = lib_database::getOne($sql, $table['index'], false, 1, [$computer['DeviceID']]);
                if (empty($device) || !in_array($device['Type'], [101, 103]) || $device['SubType'] == $subType) {
                    printf("SubType Good Or Other {$computer['DeviceID']} ".PHP_EOL);
                    continue;
                }
                $sql = "update TDevice set SubType = ? where DeviceID = ?";
                $res = $yes ? lib_database::query($sql, $table['index'], false, [$subType, $computer['DeviceID']]) : false;
                printf("Repair SubType {$computer['DeviceID']} {$subType}: ".json_encode($res).PHP_EOL);
            }
        }

        //修复TTaskManage的deviceType字段
        cutil_php_log('upgrade_2722 start deviceType:', 'upgrade');
        $sql = "select TaskID,TaskType,deviceType from TTaskManage";
        $taskTypeList = lib_database::getAll($sql);
        if (!empty($taskTypeList)) {
            foreach($taskTypeList as $task) {
                //如果该条目已经拥有设备类型，跳过--
                if (!empty($task['deviceType'])) {
                    printf("deviceType Good Or Other {$task['TaskID']} ".PHP_EOL);
                    continue;
                } else if ($task['TaskType'] == '3') {
                    //如果是灰度任务，将设备类型设为1,2,3
                    $sql = "update TTaskManage set deviceType = '1,2,3' where TaskID = ?";
                    $res = $yes ? lib_database::query($sql, $table['index'], false, [$task['TaskID']]) : false;
                    printf("Repair deviceType {$task['TaskID']} 1,2,3: ".json_encode($res).PHP_EOL);
                } else if(in_array($task['TaskType'], [1, 2])) {
                    //如果是其他任务，将设备类型设为1
                    $sql = "update TTaskManage set deviceType = '1' where TaskID = ?";
                    $res = $yes ? lib_database::query($sql, $table['index'], false, [$task['TaskID']]) : false;
                    printf("Repair deviceType {$task['TaskID']} 1: ".json_encode($res).PHP_EOL);
                }
            }
        }

        //修复TTaskToDevice表的错误数据
        cutil_php_log('upgrade_2722 start TTaskToDevice:', 'upgrade');
        $sql = "SELECT TTaskToDevice.ID, TDevice.DeviceID AS DeviceID, TDevice.SubType, TTaskToDevice.TaskID, TTaskManage.deviceType FROM TTaskToDevice LEFT JOIN TDevice ON TDevice.DeviceID = TTaskToDevice.DeviceID LEFT JOIN TTaskManage ON TTaskToDevice.TaskID = TTaskManage.TaskID WHERE TDevice.SubType != 1 AND TTaskManage.TaskType IN(?,?) ORDER BY TaskID;";
        //左连接TTaskToDevice、TDevice、TTaskManage查询出TDevice表中SubType非1且TTaskManage表中TaskType为1的数据
        $taskTypeList = lib_database::getAll($sql, $table['index'], false, [1, 2]);
        $ErrID_Arr = [];
        if (!empty($taskTypeList)) {
            foreach($taskTypeList as $item) {
                $devType_Arr = explode(",", $item['deviceType']);
                //如果任务下发的设备类型和该设备自身的设备类型是否匹配
                if(!in_array($item['SubType'], $devType_Arr)){
                    //若不匹配记录该ID
                    $ErrID_Arr[] = $item['ID'];
                }
            }
            if(!empty($ErrID_Arr)){
                $inErrID = [];
                foreach ($ErrID_Arr as $errId) {
                    $inErrID[] = "?";
                }
                $inErrID = implode(',', $inErrID);
                $ErrID_Str = implode(",", $ErrID_Arr);
                $sql = "DELETE FROM TTaskToDevice WHERE ID IN (".$inErrID.")";
                $deleteRes = $yes ? lib_database::query($sql, $table['index'], false, $ErrID_Arr) : false;
                printf("Repair TTaskToDevice ID: $ErrID_Str ".PHP_EOL);
            }else{
                printf("Don't Need To Repair TTaskToDevice".PHP_EOL);
            }
        }else{
            printf("Don't Need To Repair TTaskToDevice".PHP_EOL);
        }

        //修复1698版本Bug导致的错误分发路径
        cutil_php_log('upgrade_2722 start TTaskSoft:', 'upgrade');
        $sql = "SELECT TaskID,SavePath FROM TTaskSoft";
        $savePathList = lib_database::getAll($sql);
        $pattern = '/[\\\\]+/';
        if (!empty($savePathList)) {
            foreach ($savePathList as $item) {
                //修复SavePath
                $repairPath = addslashes(preg_replace($pattern, '\\', $item['SavePath']));
                $sql = "update TTaskSoft set SavePath = '". $repairPath ."' where TaskID = ?";
                $res = $yes ? lib_database::query($sql, $table['index'], false, [$item['TaskID']]) : false;
                printf("Repair SavePath {$item['TaskID']} : ".json_encode($res).PHP_EOL);
            }
        }

        //修复安全规范的CheckSubType数据
        $deviceSubTypes = [
            OSTYPE_WINDOWS => 1,
            OSTYPE_LINUX   => 2,
            OSTYPE_MAC     => 3,
            OSTYPE_ANDROID => 4,
            OSTYPE_IOS     => 5,
        ];
        $sql = "SELECT PolicyBody, CheckSubType, PolicyID FROM TNacPolicyList";
        $data = lib_database::getAll($sql);
        if (!empty($data)) {
            foreach ($data as $row) {
                $CheckSubType = [];
                foreach (hlp_compatible::$ostypes as $ostype) {
                    $prefix = \hlp_common::firstUpper($ostype);
                    $checkItems = GetSubStr($row['PolicyBody'], "<{$prefix}CheckItems>", "</{$prefix}CheckItems>");
                    $row['PolicyBody'] = str_replace("<{$prefix}CheckItems>" . $checkItems . "</{$prefix}CheckItems>", '', $row['PolicyBody']);
                    if (!empty($checkItems)) {
                        $CheckSubType[] = $deviceSubTypes[$ostype];
                    }
                }
                $checkItems = GetSubStr($row['PolicyBody'], "<CheckItems>", "</CheckItems>");
                if (!empty($checkItems)) {
                    $CheckSubType[] = $deviceSubTypes[OSTYPE_WINDOWS];
                }
                $CheckSubType = implode(',', $CheckSubType);
                if (!empty($CheckSubType !== $row['CheckSubType'])) {
                    $data = ['CheckSubType' => $CheckSubType];
                    $res = $yes ? NacPolicyListModel::update($row['PolicyID'], $data) : false;
                    printf("Repair CheckSubType {$row['PolicyID']} : ".json_encode($res).PHP_EOL);
                }
            }
        }
        printf("upgrade_2722 finish".PHP_EOL);
        cutil_php_log('upgrade_2722 finish.', 'upgrade');
    }
}

try
{
    $serv = new cls_cmdserv(new Install());
    $serv->handle();
}
catch (Exception $e)
{
    echo "code: " . $e->getCode() . ", message: ". $e->getMessage();
}