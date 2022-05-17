<?php

/**
 * Description: 设备TChangeIPreReg表
 * User: duanyc@infogo.com.cn
 * Date: 2021/06/25 8:53
 * Version: $Id: ChangeIPreRegModel.php 158181 2021-09-28 11:28:13Z duanyc $
 */

class ChangeIPreRegModel extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    public const TABLE_NAME = 'TChangeIPreReg';
    public const PRIMARY_KEY = 'DeviceID';
    protected static $columns = [
        '*'    => '*',
    ];

    /**
     * 获取条件
     *
     * @param array $cond
     *
     * @return string
     */
    protected static function getWhere($cond = []):string
    {
        $where = "";

        if (isset($cond['DeviceID'])) {
            $where .= "AND DeviceID = ".self::setData($cond['DeviceID']);
        }

        return !empty($where) ? "WHERE 1 = 1 {$where}" : "";
    }
}
