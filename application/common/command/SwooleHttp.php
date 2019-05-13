<?php

namespace app\common\command;

use think\Container;
use swoole\http\Server;
use think\console\Input;
use think\console\Output;
use think\console\Command;

class SwooleHttp extends Command
{
    private $http;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $config = config('swoole.');

        // http服务
        $this->http = new Server($config['host'], $config['port']);
        $this->http->set(['worker_num' => $config['http_worker_num']]);
        $this->http->on("workerstart", [$this, 'httpWorkerStart']);
        $this->http->on("request", [$this, 'httpRequest']);
        // $this->http->on("close", [$this, 'httpClose']);
    }

    protected function configure()
    {
        $this->setName('SwooleMainProcess')->setDescription('swoole http 服务器');
    }

    protected function execute(Input $input, Output $output)
    {
        // 启用http服务
        $this->http->start();
    }

    /**
     * http服务start回调
     *
     * @param [type] $server
     * @param [type] $worker_id
     * @return void
     */
    public function httpWorkerStart($server, $worker_id)
    {
        // 定义应用目录
        define('APP_PATH', dirname(__DIR__) . '/../application/');
        // 加载框架里面的文件
        require_once dirname(__DIR__) . '/../../thinkphp/base.php';
        // 写入进程id
        $pidFile = config('swoole.options.pid_file');
        file_put_contents($pidFile, "|{$server->master_pid}|{$server->manager_pid}", FILE_APPEND);
    }

    /**
     * http服务请求回调
     *
     * @param [type] $request
     * @param [type] $response
     * @return void
     */
    public function httpRequest($request, $response)
    {
        // 阻止favicon.ico进入
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $request->end;
            return;
        }
        $_SERVER  =  [];
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }
        $_POST['http_server'] = $this->http;
        // 将获得到的数据写到缓存区，避免内存泄漏
        ob_start();
        try {
            Container::get('app', [APP_PATH])->run()->send();
        } catch (\Exception $e) {
            // TODO
        }
        $res = ob_get_contents();
        ob_end_clean();
        $response->end($res);
    }
}
