<?php

namespace app\common\enum;

/**
 * 响应状态码
 */
abstract class CodeEnum
{
    const RESPONSE_SUCCESS = 200;    // 响应成功
    
    const RESPONSE_ERROR = 400;    // 响应失败

    const MISS_PARAMS = 401;    // 缺少必要参数
}
