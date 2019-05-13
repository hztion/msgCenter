<?php

namespace app\common\command;

use swoole\Process\Pool;
use think\console\Input;
use think\console\Output;
use think\console\Command;
use app\common\servant\AddLog;
use app\common\servant\SendMsg;
use app\common\servant\SendEmail;
use app\common\servant\InternationalMsg;

class SwoolePool extends Command
{
    private $pool;

    //工作者实例对象
    private $servantInstanceList = [];

    private static $config = [];

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $config = config('swoole.');
        // 初始化线程数
        self::$config = [
            InternationalMsg::class => $config['national_msg_worker_num'],
            AddLog::class => $config['add_log_worker_num'],
            SendEmail::class => $config['send_email_worker_num'],
            SendMsg::class => $config['send_msg_worker_num'],
        ];
        //初始化swoole线程
        $this->pool = new Pool(self::getCount());
        //绑定方法
        $this->pool->on("WorkerStart", [$this, 'swooleWork']);
        $this->pool->on("WorkerStop", [$this, 'swooleStop']);
    }

    protected function configure()
    {
        $this->setName('SwooleMainProcess')->setDescription('swoole消费者进程池');
    }

    protected function execute(Input $input, Output $output)
    {
        // 启用进程池
        $this->pool->start();
    }

    /**
     * 线程具体工作内容
     * @param $pool 线程池对象
     * @param $workerId 线程id
     */
    public function swooleWork(Pool $pool, int $workerId)
    {
        $servant = self::getServant($workerId);
        //实例队列
        $this->servantInstanceList[$workerId] = $servant;
        // 写入进程id
        $pidFile = config('swoole.options.pid_file');
        file_put_contents($pidFile, '|' . $pool->master_pid, FILE_APPEND);
        // file_put_contents($pidFile, '|' . posix_getpid(), FILE_APPEND);

        $servant->workStart($pool, $workerId);
    }

    /**
     * 线程停止工作
     * @param $pool 线程池对象
     * @param $workerId 线程id
     */
    public function swooleStop(Pool $pool, int $workerId)
    {
        $this->output->writeln("Worker#{$workerId} is stopped\n");
        //删除内存里面的对象
        unset($this->servantInstanceList[$workerId]);
    }

    /**
     * @param int $workerId 工作者下标
     * @return void
     */
    public static function getServant($workerId)
    {
        foreach (self::$config as $k => $v) {
            if ($workerId < $v) {
                return call_user_func([$k, 'getInstance']);
            }
            $workerId -= $v;
        }
        return null;
    }

    /**
     * 获取工作者总数量
     */
    public static function getCount(): int
    {
        $count = 0;
        foreach (self::$config as $k => $v) {
            $count += $v;
        }
        return $count;
    }
}
