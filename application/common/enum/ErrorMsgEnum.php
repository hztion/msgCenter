<?php

namespace app\common\enum;

/**
 * 响应错误信息
 */
abstract class ErrorMsgEnum
{
    const RESPONSE_SUCCESS_MSG = '响应成功';
    
    const RESPONSE_ERROR_MSG = '响应失败';

    const MISS_PARAMS_MSG = '缺少必要参数';
    
    const UNKNOWN_MSG_TYPE = '未知的消息类型';  // 未知的rabbitmq类型

    const ERROR_MQ_CONFIG = 'RabbitMQ的连接配置不正确';  // rabbitmq配置错误
}
