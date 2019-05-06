<?php

namespace app\common\servant;

use think\facade\Log;
use GuzzleHttp\Client;
use Swoole\Process\Pool;
use think\facade\Validate;
use app\common\tool\RabbitMQTool;
use app\common\traits\RabbitSwoole;

class SendMsg implements BaseServant
{
    use RabbitSwoole;

    private $fileName = 'send_msg';

    /**
     * 发送短信
     *
     * @param array $data
     * @return boolean
     */
    public function sendMessage(array $data): bool
    {
        if (!isset($data['phone']) || !Validate::isMobile($data['phone'])) {
            Log::mylog("该消息不包含手机号,内容:" . json_encode($data, JSON_UNESCAPED_UNICODE), $this->fileName);
            return true;
        }
        $config = config('third_service.');
        $apikey = $config['yunpian']['apikey'];

        $param = [
            'apikey' => $apikey,
            'mobile' => $data['phone'],
            'text' => $data['content']
        ];
        // 请求接口
        $uri = $config['yunpian_api'];

        try {
            $options = [
                'form_params' => $param
            ];
            // 跳过证书验证
            $curlConfig = ['verify' => false];
            $r = (new Client($curlConfig))->post($uri, $options);
            if ($r->getStatusCode() == 200) {
                $res = json_decode($r->getBody(), true);
                if ($res['code'] === 0) {
                    Log::mylog("短信发送成功,手机号:{$data['phone']}", $this->fileName);
                    return true;
                } else {
                    Log::mylog("短信发送失败,错误信息:{$r->getBody()}", $this->fileName);
                    return false;
                }
            }
            Log::mylog("短信发送失败,手机号:{$data['phone']}", $this->fileName);
            return false;
        } catch (\Exception $e) {
            Log::mylog("短信发送失败,手机号:{$data['phone']}.msg:{$e->getMessage()}", $this->fileName);
            return false;
        }
    }

    function workStart(Pool $pool, int $workerId)
    {
        $rabbitMq = RabbitMQTool::instance(1);

        $callBack = function ($msg) {
            $rData = json_decode($msg->body, true);
            $res = $this->sendMessage($rData);
            if ($res) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            } else {
                $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
            }
        };
        $rabbitMq->channel->basic_qos(null, 1, null);
        $rabbitMq->channel->basic_consume($rabbitMq->config['queue_name'], '', false, $rabbitMq->autoAck, false, false, $callBack);

        echo "SendMsg Success workerId:{$workerId}\r\n";

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
