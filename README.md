ThinkPHP 5.1 + swoole + rabbitMQ 高性能消息队列
===============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/top-think/framework/badges/quality-score.png?b=5.1)](https://scrutinizer-ci.com/g/top-think/framework/?branch=5.1)
[![Build Status](https://travis-ci.org/top-think/framework.svg?branch=master)](https://travis-ci.org/top-think/framework)
[![Total Downloads](https://poser.pugx.org/topthink/framework/downloads)](https://packagist.org/packages/topthink/framework)
[![Latest Stable Version](https://poser.pugx.org/topthink/framework/v/stable)](https://packagist.org/packages/topthink/framework)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D5.6-8892BF.svg)](http://www.php.net/)
[![License](https://poser.pugx.org/topthink/framework/license)](https://packagist.org/packages/topthink/framework)

## 安装

使用composer安装

~~~
composer install -vvv
~~~

启动服务
~~~
修改thinkphp/library/think/Request.php
function pathinfo() {
    // add
    if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '/') {
        return ltrim($_SERVER['PATH_INFO'], '/');
    }
    // delete 单行
    if (is_null($this->pathinfo)) {
}

function path() {
    // deleta 单行
    if (is_null($this->path)) {
}
~~~
~~~
进入项目根目录
php think server start
~~~

然后就可以在浏览器中访问

~~~
http://localhost:9501
~~~

## 版权信息

遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
