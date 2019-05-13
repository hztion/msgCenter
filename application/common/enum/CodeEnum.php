<?php

namespace app\common\enum;

/**
 * 响应状态码
 */
abstract class CodeEnum
{
    const RESPONSE_SUCCESS = 10000;    // 响应成功

    const RESPONSE_ERROR = 10001;    // 响应失败

    const MISS_PARAMS = 20001;    // 缺少必要参数

    const ERROR_MQ_CONFIG = 30001;      // RabbitMQ的连接配置不正确

    const UNKNOWN_MSG_TYPE = 30002;     // 未知rabbitMQ消息类型
}
