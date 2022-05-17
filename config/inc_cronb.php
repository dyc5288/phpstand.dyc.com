<?php
/**
 * Description: 定时器
 * User: duanyc@infogo.com.cn
 * Date: 2021/9/13 15:54
 * Version: $Id$
 */

/* CROND 定时器 配置文件 */
$GLOBALS['CROND_TIMER'] = [
    /* 配置支持的格式 */
    'the_format' => [
        '*',        //每分钟
        '*:i',      //每小时 某分
        'H:i',      //每天 某时:某分
        '@-w H:i',  //每周-某天 某时:某分  0=周日
        '*-d H:i',  //每月-某天 某时:某分
        'm-d H:i',  //某月-某日 某时-某分
        'Y-m-d H:i',//某年-某月-某日 某时-某分
    ],
    /* 配置执行的文件 */
    'the_time'   => [
        /* 每分钟 */
        '*'     => [
            //'test.php'
        ],
        /* 每天 某时:某分 */
        '00:00' => [
        ],
    ],
    // 每隔几分钟
    'the_interval' => [
        /* 队列参考配置$GLOBALS['CONFIG']['queue']中，timeout除以num则为执行的间隔分钟数
           如：timeout为10，num为2时，则每隔5分钟执行，意思是10分钟再退出，每隔5分钟启动1个，保证有2个进程启动。*/
        /* 每隔5分钟 */
        1 => [
            'AUTH_SUCCESS'
        ]
    ]
];

foreach ($GLOBALS['CROND_TIMER']['the_interval'] as $time => $tasks)
{
    for ($i = 0; $i < 60; $i += $time)
    {
        $minute = str_pad($i, 2, '0', STR_PAD_LEFT);
        $GLOBALS['CROND_TIMER']['the_time']["*:{$minute}"] = $tasks;
    }
}