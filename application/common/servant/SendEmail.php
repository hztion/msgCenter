<?php

namespace app\common\servant;

use GuzzleHttp\Client;
use Swoole\Process\Pool;
use think\facade\Validate;
use app\common\tool\RabbitMQTool;

class SendEmail implements BaseServant
{
    private $fileName = 'send_email';

    /**
     * 发送邮件信息
     *
     * @param array $data
     * @return boolean
     */
    public function sendMessage(array $data): bool
    {
        if (!isset($data['to_email']) || !Validate::isEmail($data['to_email'])) {
            mylog("该消息不包含邮箱信息,内容:" . json_encode($data, JSON_UNESCAPED_UNICODE), $this->fileName);
            return true;
        }
        if (!isset($data['from_email']) || !isset($data['template_name']) || !isset($data['content'])) {
            mylog("缺少必要参数,内容:" . json_encode($data, JSON_UNESCAPED_UNICODE), $this->fileName);
            return true;
        }
        $config = config('third_service.send_cloud');
        $apiUser = $config['api_user'];
        $apiKey = $config['api_key'];

        $param = [
            'apiUser' => $apiUser,
            'apiKey' => $apiKey,
            'from' => $data['from_email'],  // 发件人邮箱
            'fromName' => isset($data['from_name']) ? $data['from_name'] : '',      // 发件人名称
            'xsmtpapi' => json_encode([
                'to' => [$data['to_email']],
                'sub' => [
                    '%content%' => [$data['content']],
                    '%date%' => [date('Y-m-d H:i:s')]
                ]
            ]),
            'templateInvokeName' => $data['template_name'],
            'respEmailId' => 'true'
        ];
        // 请求接口
        $uri = config('third_service.send_cloud_template');

        try {
            $r = (new Client())->request('POST', $uri, ['form_params' => $param]);
            if ($r->getStatusCode() == 200) {
                $res = json_decode($r->getBody(), true);
                if ($res['result'] && $res['statusCode'] == 200) {
                    mylog("邮件发送成功,邮箱账号:{$data['to_email']}", $this->fileName);
                    return true;
                } else {
                    mylog("邮件发送失败,错误信息:{$r->getBody()}", $this->fileName);
                    return false;
                }
            }
            mylog("邮件发送失败,邮箱账号:{$data['to_email']}", $this->fileName);
            return false;
        } catch (\Exception $e) {
            mylog("邮件发送失败,邮箱账号:{$data['to_email']}.msg:{$e->getMessage()}", $this->fileName);
            return false;
        }
    }

    function workStart(Pool $pool, int $workerId)
    {
        $rabbitMq = RabbitMQTool::instance(3);

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

        echo "SendEmail Success workerId:{$workerId}\r\n";

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
