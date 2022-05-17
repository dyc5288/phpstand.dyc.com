<?php
/**
 * Description: ��ʱ��
 * User: duanyc@infogo.com.cn
 * Date: 2021/9/13 15:54
 * Version: $Id$
 */

/* CROND ��ʱ�� �����ļ� */
$GLOBALS['CROND_TIMER'] = [
    /* ����֧�ֵĸ�ʽ */
    'the_format' => [
        '*',        //ÿ����
        '*:i',      //ÿСʱ ĳ��
        'H:i',      //ÿ�� ĳʱ:ĳ��
        '@-w H:i',  //ÿ��-ĳ�� ĳʱ:ĳ��  0=����
        '*-d H:i',  //ÿ��-ĳ�� ĳʱ:ĳ��
        'm-d H:i',  //ĳ��-ĳ�� ĳʱ-ĳ��
        'Y-m-d H:i',//ĳ��-ĳ��-ĳ�� ĳʱ-ĳ��
    ],
    /* ����ִ�е��ļ� */
    'the_time'   => [
        /* ÿ���� */
        '*'     => [
            //'test.php'
        ],
        /* ÿ�� ĳʱ:ĳ�� */
        '00:00' => [
        ],
    ],
    // ÿ��������
    'the_interval' => [
        /* ���вο�����$GLOBALS['CONFIG']['queue']�У�timeout����num��Ϊִ�еļ��������
           �磺timeoutΪ10��numΪ2ʱ����ÿ��5����ִ�У���˼��10�������˳���ÿ��5��������1������֤��2������������*/
        /* ÿ��5���� */
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