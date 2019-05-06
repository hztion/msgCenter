<?php
// +----------------------------------------------------------------------
// | [验证层基类]
// +----------------------------------------------------------------------
// | Data 2018/9/11 14:24
// +----------------------------------------------------------------------
// | Author: yinchuanan
// +----------------------------------------------------------------------

namespace app\common\validate;

use think\Validate;
use think\facade\Request;
use app\common\exception\ApiException;

class BaseValidate extends Validate
{

    /**
     * 场景校验
     * @param $scene 场景值
     * @return mixed
     * @author yinchuanan
     */
    public function goCheck($scene)
    {
        //对参数做校验
        $params  = Request::param();
        if (!$this->scene($scene)->check($params)) {
            throw new ApiException($this->error, 200, 40000);
        } else {
            return $params;
        }
    }

    /**
     * 验证是否一个正整数
     * @param $value
     * @author yinchuanan
     */
    protected function isPositiveInteger($value)
    {
        if (is_numeric($value) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证是否一个参数是否不为空
     * @param $value
     * @author yinchuanan
     */
    protected function isNotEmpty($value)
    {
        return empty($value) ? false : true;
    }

    /**
     * 验证是否数字加字母
     */
    protected function isNumberAlphabet($value)
    {
        if (!preg_match('/^(?!\d+$)[\da-zA-Z_@_.]{8,20}$/', $value)) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否只能 第一位是字母，后面的数字和字母
     */
    protected function isNumberOrAlphabet($value)
    {
        if (!preg_match('/^[a-zA-Z]{1}[0-9a-zA-Z]{5,18}$/', $value)) {
            return false;
        }
        return true;
    }

    /**
     * 验证金额是否正确
     */
    protected function isMoney($value)
    {
        if (!preg_match('/(^[1-9](\d+)?(\.\d{1,2})?$)|(^(0){1}$)|(^\d\.\d{1,2}?$)/', $value)) {
            return false;
        }
        return true;
    }

    /**
     * 判断是否一个数组
     * @param $value
     * @return bool
     */
    protected function isArray($value)
    {
        if (!is_array($value)) {
            return false;
        }
        return true;
    }
}
