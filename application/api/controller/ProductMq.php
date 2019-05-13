<?php
namespace app\api\controller;

use think\Controller;
use think\facade\Request;
use app\common\enum\CodeEnum;
use app\common\tool\RabbitMQTool;
use app\common\validate\ProductValidate;

class ProductMq extends Controller
{
    /**
     * 生产消息队列接口
     * 
     * @param int $type
     * @param string $data
     * @return mixed
     */
    public function producing()
    {
        // if (Request::isPost()) {
        // $params = (new ProductValidate())->goCheck('product');
        $type = isset($_POST['type']) ? (int)$_POST['type'] : 0;
        $data = isset($_POST['data']) ? $_POST['data'] : '';
        if (!$type || !$data) {
            return show(['status' => CodeEnum::MISS_PARAMS]);
        }
        $res = RabbitMQTool::instance($type)->writeMq($data);
        if ($res) {
            return show();
        }
        // }
        return show(['status' => 10001]);
    }
}
