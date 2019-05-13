<?php

namespace app\common\servant;

use Swoole\Process\Pool;
use app\common\tool\RabbitMQTool;

class AddLog implements BaseServant
{
    private $fileName = 'add_log';

    /**
     * 添加日志
     * 
     * @param string $content
     * @return bool
     */
    public function addLog($content)
    {
        try {
            mylog($content, $this->fileName);
            return true;
        } catch (\Exception $e) {
            mylog("操作失败.msg:{$e->getMessage()}", $this->fileName);
            return false;
        }
    }

    function workStart(Pool $pool, int $workerId)
    {
        $rabbitMq = RabbitMQTool::instance(2);

        $callBack = function ($msg) {
            $rData = json_decode($msg->body, true);
            $res = $this->addLog($rData);
            if ($res) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            } else {
                $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
            }
        };
        $rabbitMq->channel->basic_qos(null, 1, null);
        $rabbitMq->channel->basic_consume($rabbitMq->config['queue_name'], '', false, $rabbitMq->autoAck, false, false, $callBack);

        echo "AddLog Success workerId:{$workerId}\r\n";

        while (count($rabbitMq->channel->callbacks)) {
            $rabbitMq->channel->wait();
        }
        $rabbitMq->closeConn();
    }

    public static function getInstance(): BaseServant
    {
        return new self();
    }
}
