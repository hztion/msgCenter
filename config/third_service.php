<?php

// +----------------------------------------------------------------------
// | 第三方服务配置
// +----------------------------------------------------------------------

return [
    // 云片
    'yunpian' => [
        'apikey' => ''
    ],
    // 云片发送短信接口
    'yunpian_api' => 'https://sms.yunpian.com/v2/sms/single_send.json',
    // SendCloud
    'send_cloud' => [
        'api_user' => '',
        'api_key' => ''
    ],
    // SendCloud 发送邮件接口
    'send_cloud_api' => 'http://api.sendcloud.net/apiv2/mail/send',
    // SendCloud 发送模板邮件接口
    'send_cloud_template' => 'http://api.sendcloud.net/apiv2/mail/sendtemplate'
];
