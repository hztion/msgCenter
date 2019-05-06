<?php

namespace app\common\servant;

use think\facade\App;
use Swoole\Process\Pool;

class HttpServant implements BaseServant
{
    private $root, $config;

    public function __construct()
    {
        $this->root = App::getRootPath() . 'public';
        $this->config = config('http_server.');
    }


    function workStart(Pool $pool, int $workerId)
    {
        $command = sprintf(
            'php -S %s:%d -t %s %s',
            $this->config['ip'],
            $this->config['port'],
            escapeshellarg($this->root),
            escapeshellarg($this->root . DIRECTORY_SEPARATOR . 'router.php')
        );
        print_r(sprintf(
            'ThinkPHP Development server is started On <http://%s:%s/>' . "\n\r",
            $this->config['ip'],
            $this->config['port']
        ));
        print_r(sprintf('Document root is: %s' . "\n\r", $this->root));
        passthru($command);
    }

    static function getInstance(): BaseServant
    {
        return new self();
    }
}
