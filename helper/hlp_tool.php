<?php
/**
 * Description: 工具
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: hlp_tool.php 147146 2021-06-17 02:04:51Z duanyc $
 */

/**
 * 工具函数
 *
 * @author duanyunchao
 * @version $Id: hlp_tool.php 147146 2021-06-17 02:04:51Z duanyc $
 */
class hlp_tool
{
    /**
     * 建表
     *
     * @param string $table_name
     * @param string $sql
     * @param int $number 表数量
     *
     * @return boolean
     */
    public static function createTable($table_name, $sql, $number = 1)
    {
        if ($number <= 0 || $number > 10000) {
            return false;
        }

        for ($i = 0; $i < $number; $i++) {
            $table = hlp_common::getSplitTable($i, $table_name);
            $tsql = sprintf($sql, $table['name']);
            $res = lib_database::query($tsql, $table['index']);
            $msg = "Create table {$table['name']}";

            if ($res) {
                printf(sprintf("{$msg} success! \n", $i));
            } else {
                printf(sprintf("{$msg} fail! \n", $i));
                break;
            }
        }

        return true;
    }

    /**
     * 修改表
     *
     * @param string $table_name
     * @param string $sql
     * @param int $number
     *
     * @return boolean
     */
    public static function alterTable($table_name, $sql, $number = 1)
    {
        if ($number <= 0 || $number > 10000) {
            return false;
        }

        for ($i = 0; $i < $number; $i++) {
            $table = hlp_common::getSplitTable($i, $table_name);
            $tsql = sprintf($sql, $table['name']);
            $res = lib_database::query($tsql, $table['index']);
            $msg = "Alter table {$table['name']}";

            if ($res) {
                printf(sprintf("{$msg} success! \n", $i));
            } else {
                printf(sprintf("{$msg} fail! \n", $i));
                break;
            }
        }

        return true;
    }

    /**
     * 删除表
     *
     * @param string $table_name
     * @param int $number
     *
     * @return mixed
     */
    public static function dropTable($table_name, $number = 1)
    {
        if ($number <= 0 || $number > 10000) {
            return false;
        }

        $sql = "DROP TABLE IF EXISTS `%s`";

        for ($i = 0; $i < $number; $i++) {
            $table = hlp_common::getSplitTable($i, $table_name);
            $tsql = sprintf($sql, $table['name']);
            $res = lib_database::query($tsql, $table['index']);
            $msg = "DROP table {$table['name']}";

            if ($res) {
                printf(sprintf("{$msg} success! \n", $i));
            } else {
                printf(sprintf("{$msg} fail! \n", $i));
                break;
            }
        }

        return true;
    }

    /**
     * 显示表结构
     *
     * @param string $table_name
     * @param int $i
     */
    public static function showTable($table_name, $i)
    {
        $table = hlp_common::getSplitTable($i, $table_name);
        $sql = "show create table {$table['name']};";
        $res = lib_database::getOne($sql, $table['index']);
        var_dump($res);
    }

    /**
     * 获取表后缀
     *
     * @param int $number
     *
     * @return string
     */
    public static function getTableSuffix($number)
    {
        if ($number == 1) {
            $suffix = '';
        } else if ($number <= 10) {
            $suffix = "_%01d";
        } else if ($number <= 100) {
            $suffix = "_%02d";
        } else if ($number <= 1000) {
            $suffix = "_%03d";
        } else {
            $suffix = "_%04d";
        }

        return $suffix;
    }
}
