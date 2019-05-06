<?php

namespace app\common\tool;

use app\common\enum\CodeEnum;
use app\common\enum\ErrorMsgEnum;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQTool
{
    private $connect;    // 连接

    public $channel;   // 通道

    private $exchangeType = 'direct';   // 交换机类型

    private $routeKey; // 路由键

    public $autoAck = false;    // 是否自动Ack应答, false:响应ack

    public $config = [];   // 连接配置

    /**
     * RabbitMQTool constructor
     * @param $type 1.短信  2.日志  3.用户红包 
     */
    private function __construct($type, $exchangeType, $routeKey)
    {
        // 设置连接配置
        $this->setConfig($type);

        // 获取交换机类型、绑定键
        if (!empty($exchangeType)) {
            $this->exchangeType = $exchangeType;
        }
        $this->routeKey = empty($routeKey) ? $this->config['routing_key'] : $routeKey;

        // 创建mq连接
        $this->createConnect();

        // 创建通道
        $this->channel = $this->connect->channel($type);

        // 声明初始化交换机
        $this->channel->exchange_declare($this->config['exchange_name'], $this->exchangeType, false, true, false);

        // 声明初始化一条队列
        $this->channel->queue_declare($this->config['queue_name'], false, true, false, false, false, [
            'x-dead-letter-exchange' => ['S', $this->config['dlx_exchange']],
            'x-dead-letter-routing-key' => ['S', $this->config['dlx_routing_key']]
        ]);

        // 交换机队列绑定
        $this->channel->queue_bind($this->config['queue_name'], $this->config['exchange_name'], $this->routeKey);
    }

    /**
     * 返回当前实例
     * @param int $type
     * @param string $exchangeType
     * @param string $routeKey
     * @return RabbitMQTool
     */
    public static function instance($type = 1, $exchangeType = '', $routeKey = '')
    {
        return new self($type, $exchangeType, $routeKey);
    }

    /**
     * 设置MQ的连接配置
     * 
     * @param int $type
     * @return void
     */
    private function setConfig($type)
    {
        $config = config('rabbit_mq.');
        if (!isset($config['rabbit_mq_queue'][$type])) {
            json(['code' => CodeEnum::RESPONSE_ERROR, 'msg' => ErrorMsgEnum::UNKNOWN_MSG_TYPE])->send();
            die();
        }
        $rabbitConf = $config['rabbit_mq_queue'][$type];
        $config['exchange_name'] = $rabbitConf['exchange_name'];
        $config['queue_name'] = $rabbitConf['queue_name'];
        $config['routing_key'] = $rabbitConf['routing_key'];
        $config['dlx_routing_key'] = $rabbitConf['dlx_routing_key'];
        $config['dlx_exchange'] = $config['rabbit_mq_queue']['dlx']['exchange_name'];
        unset($config['rabbit_mq_queue']);

        $this->config = $config;
    }

    /**
     * 创建mq连接
     * 
     * @return mixed
     */
    private function createConnect()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $user = $this->config['user'];
        $password = $this->config['pwd'];
        $vhost = $this->config['vhost'];
        if (empty($host) || empty($port) || empty($user) || empty($password)) {
            json(['code' => CodeEnum::RESPONSE_ERROR, 'msg' => ErrorMsgEnum::ERROR_MQ_CONFIG])->send();
            die();
        }

        $this->connect = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
    }

    /**
     * 写入mq
     * @param array $data
     * @return bool
     * @throws \ErrorException
     */
    public function writeMq($data)
    {
        try {
            $msg = new AMQPMessage($data, ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
            $this->channel->basic_publish($msg, $this->config['exchange_name'], $this->routeKey);
        } catch (\Exception $e) {
            $this->closeConn();
            return false;
        }
        $this->closeConn();
        return true;
    }

    /**
     * 读取mq
     * @param int $num
     * @return array
     */
    public function readMq($num = 1, $flag = false)
    {
        $this->autoAck = $flag;
        static $rData = [];
        $callBack = function ($msg) use (&$rData) {
            $rData[] = json_decode($msg->body, true);

            if (!$this->autoAck) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        };
        for ($i = 0; $i < $num; $i++) {
            $this->channel->basic_qos(null, 1, null);
            $this->channel->basic_consume($this->config['queue_name'], '', false, $this->autoAck, false, false, $callBack);
        }
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->closeConn();
        return $rData;
    }

    /**
     * 关闭连接
     * 
     * @return void
     */
    public function closeConn()
    {
        $this->channel->close();
        $this->connect->close();
    }
}
