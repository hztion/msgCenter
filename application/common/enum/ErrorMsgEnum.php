<?php

namespace app\common\enum;

/**
 * 响应错误信息
 */
abstract class ErrorMsgEnum
{
    public static $errMsg = [
        CodeEnum::RESPONSE_SUCCESS => '响应成功',
        CodeEnum::RESPONSE_ERROR => '响应失败',
        CodeEnum::MISS_PARAMS => '缺少必要参数',
        CodeEnum::ERROR_MQ_CONFIG => 'RabbitMQ的连接配置不正确',
        CodeEnum::UNKNOWN_MSG_TYPE => '缺少必要参数',
    ];

    /**
     * 获取错误描述信息
     * @param $errCode
     * @return string
     */
    public static function getErrMsg($errCode)
    {
        return isset(self::$errMsg[$errCode]) ? self::$errMsg[$errCode] : self::getDefaultMsg();
    }

    /**
     * 获取默认的错误描述信息
     * @return string
     */
    public static function getDefaultMsg()
    {
        return '没有定义相关提示';
    }
}
