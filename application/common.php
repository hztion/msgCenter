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
use app\common\enum\ErrorMsgEnum;
use app\common\exception\ApiException;

/**
 * 通用化API接口数据输出
 *
 * @param array $result
 * @param integer $httpCode
 * @return void
 */
if (!function_exists('show')) {
    function show($result = [], $httpCode = 200)
    {
        if (!isset($result['status'])) $result['status'] = 10000;
        if ($result['status'] == 10000 || $result['status'] == 99000) {
            $result['message'] = empty($result['message']) ? '成功' : $result['message'];
        } else {
            $result['message'] = ErrorMsgEnum::getErrMsg($result['status']);
        }
        return json($result, $httpCode);
    }
}

/**
 * 获取rabbitMQ配置
 *
 * @param integer $type
 * @return array
 */
if (!function_exists('getRabbitConfig')) {
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
}

/**
 * 写入日志操作
 *
 * @param string $content
 * @param string $fileName
 * @return void
 */
if (!function_exists('mylog')) {
    function mylog($content, $fileName = '')
    {
        $fileSize = '2097152';
        $logPath = \think\facade\App::getRootPath() . '/data/log/';

        $fileName = $fileName ?: date('d');
        $destination = $logPath . date('Ym') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . $fileName . '.log';

        if (is_file($destination) && floor($fileSize) <= filesize($destination)) {
            try {
                rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . time() . '-' . basename($destination));
            } catch (\Exception $e) { }
        }

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $now = date('c');
        $message = "[{$now}]   " . $content . "\r\n";
        file_put_contents($destination, $message, FILE_APPEND);
    }
}
