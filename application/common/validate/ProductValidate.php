<?php

namespace app\common\validate;

class ProductValidate extends BaseValidate
{

    /**
     * 定义验证的字段
     * @var array
     */
    protected $rule = [
        'type' => 'require',
        'data' => 'require'
    ];

    /**
     * 定义场景验证
     * @var array
     */
    protected $scene = [
        'product' => ['type', 'data']
    ];
}
