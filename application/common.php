<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use app\common\exception\ApiException;

function getRabbitConfig($type = 1)
{
    $rConfig = config('rabbit_mq.');
    $config = [
        'host' => $rConfig['host'],
        'vhost' => $rConfig['vhost'],
        'port' => $rConfig['port'],
        'username' => $rConfig['user'],
        'password' => $rConfig['pwd']
    ];
    if (!isset($rConfig['rabbit_mq_queue'][$type])) {
        throw new ApiException('Configuration information error', 200, 40000);
    }
    $exchangeName = $rConfig['rabbit_mq_queue'][$type]['exchange_name'];
    $queueName = $rConfig['rabbit_mq_queue'][$type]['queue_name'];
    $routingKey = $rConfig['rabbit_mq_queue'][$type]['routing_key'];
    
    return array_merge($config, compact('exchangeName', 'queueName', 'routingKey'));
}
