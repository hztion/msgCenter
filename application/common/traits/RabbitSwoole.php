<?php

namespace app\common\traits;

use Swoole\Process\Pool;
use app\common\tool\RabbitMQCommand;

/**
 * rabbitMQ Swoole
 */
trait RabbitSwoole
{
    /**
     * 消费者
     *
     * @param integer $type
     * @param array $callable
     * @return void
     */
    public function runConsume(int $type = 1, array $callable)
    {
        $config = getRabbitConfig($type);
        $rabbitMq = new RabbitMQCommand($config, $config['exchangeName'], $config['queueName'], $config['routingKey']);

        $workerNum = config('swoole.worker_num');
        $pool = new Pool($workerNum);

        $pool->on("WorkerStart", function ($pool, $workerId) use ($rabbitMq, $callable) {
            mylog("WorkerStart: MasterPid=[{$pool->master_pid}] --- WorkerId=[{$workerId}] --- workerPid=[" . posix_getpid() . "]", $this->fileName);
            while (true) {
                go(function () use ($rabbitMq, $callable) {
                    $rabbitMq->run($callable, false);
                });
            }
        });

        $pool->on("WorkerStop", function ($pool, $workerId) {
            mylog("WorkerStop: MasterPid=[{$pool->master_pid}] --- WorkerId=[{$workerId}] --- workerPid=[" . posix_getpid() . "]", $this->fileName);
        });
        $pool->start();
    }
}
