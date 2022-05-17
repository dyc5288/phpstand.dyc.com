<?php

/**
 * Description: 语言配置
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: inc_lang.php 170454 2022-03-07 07:11:45Z huyf $
 */
/* 访问控制 */
!defined('IN_INIT') && exit('Access Denied');
/* 初始化语言配置 */

// 语言文件配置 1位级别编号（1为程序错误，2为业务错误），2位模块编号（web固定为11），2位业务编号
$GLOBALS['CONFIG']['LANG_FILE'] = array(
    '11100' => 'Program',   // 程序错误
    '21100' => 'Common',    // 公共提示
    '21101' => 'System',    // 系统
);
