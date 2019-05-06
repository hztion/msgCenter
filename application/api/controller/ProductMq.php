<?php
namespace app\api\controller;

use think\Controller;
use think\facade\Request;
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
        if (Request::isPost()) {
            $params = (new ProductValidate())->goCheck('product');
            $res = RabbitMQTool::instance($params['type'])->writeMq($params['data']);
            if ($res) {
                $this->jsonSuccess();
            }
        }
        $this->jsonError();
    }
}
