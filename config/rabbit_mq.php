<?php

// +----------------------------------------------------------------------
// | rabbitMq设置
// +----------------------------------------------------------------------

return [
    'host' => 'localhost',    // host
    'port' => 5672,    // 端口
    'user' => 'guest',    // 用户名
    'pwd' => 'guest',    // 密码
    'vhost' => '/',    // 虚拟host
    'rabbit_mq_queue' => [
        // 死信队列
        'dlx' => [
            'exchange_name' => 'dead.letter'    // 死信交换机，初始化时绑定下列死信队列
        ],
        // 短信队列
        '1' => [
            'exchange_name' => 'short.msg',    // 交换机名称
            'queue_name' => 'queue.short.msg',    // 队列名称
            'routing_key' => 'short.msg.key',    // 默认绑定键
            'dlx_queue_name' => 'dlx.queue.short.msg',    // 死信队列名
            'dlx_routing_key' => 'dlx.short.msg.key'    // 死信队列路由键
        ],
        // 日志队列
        '2' => [
            'exchange_name' => 'journal',
            'queue_name' => 'queue.journal',
            'routing_key' => 'journal.key',
            'dlx_queue_name' => 'dlx.queue.journal',
            'dlx_routing_key' => 'dlx.journal.key'
        ],
        // 邮件队列
        '3' => [
            'exchange_name' => 'email',
            'queue_name' => 'queue.email',
            'routing_key' => 'email.key',
            'dlx_queue_name' => 'dlx.queue.email',
            'dlx_routing_key' => 'dlx.email.key'
        ],
        // 国际短信
        '4' => [
            'exchange_name' => 'international.msg',
            'queue_name' => 'queue.international.msg',
            'routing_key' => 'international.msg.key',
            'dlx_queue_name' => 'dlx.queue.international.msg',
            'dlx_routing_key' => 'dlx.international.msg.key'
        ]
    ]
];
