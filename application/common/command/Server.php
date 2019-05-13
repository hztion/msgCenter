<?php

namespace app\common\command;

use Throwable;
use Swoole\Process;
use think\console\Input;
use think\console\Output;
use think\console\Command;
use think\console\input\Option;
use think\console\input\Argument;

class Server extends Command
{
    protected function configure()
    {
        $this->setName('swoole')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('param', null, Option::VALUE_REQUIRED, 'option')
            ->setDescription('Swoole HTTP Server and process pool for ThinkPHP');
    }

    protected function execute(Input $input, Output $output)
    {
        $action = $this->input->getArgument('action');
        $param = $this->input->getOption('param');

        if (in_array($action, ['start', 'stop', 'reload', 'restart'])) {
            if ($action == 'start') {
                $this->start($param);
            } else {
                $this->$action();
            }
        } else {
            $this->output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload .</error>");
        }
    }

    /**
     * 启动http server 和 process pool
     * @access protected
     * @return void
     */
    protected function start($param = '')
    {
        $pid = $this->getMasterPid();

        if (isset($pid[0]) && $this->isRunning($pid[0])) {
            $this->output->writeln('<error>swoole http server process is already running.</error>');
            return;
        }

        $this->output->writeln('Starting swoole http server...');

        $this->output->writeln("Swoole http server started");

        $log = env('root_path') . '/data/log/server.log';
        // 守护进程模式
        if ($param == 'd') {
            $shell1 = "nohup php think swoole_http >{$log} 2>&1 &";
            $shell2 = "nohup php think swoole_pool >{$log} 2>&1 &";
        } else {
            $shell1 = "php think swoole_http >{$log} 2>&1 &";
            $shell2 = "php think swoole_pool >{$log} 2>&1 &";
        }
        shell_exec($shell1);
        shell_exec($shell2);
    }

    /**
     * 停止server
     * @access protected
     * @return void
     */
    protected function stop()
    {
        $pids = $this->getMasterPid();

        if (!isset($pids[0]) || !$this->isRunning($pids[0])) {
            $this->output->writeln('<error>no swoole http server process running.</error>');
            return;
        }

        $this->output->writeln('Stopping swoole http server...');

        foreach ($pids as $pid) {
            $isRunning = $this->killProcess($pid, SIGTERM, 15);

            if ($isRunning) {
                $this->output->error('Unable to stop the swoole_http_server process.');
                return;
            }
        }
        $this->removePid();

        $this->output->writeln('> success');
    }

    /**
     * 重启server
     * @access protected
     * @return void
     */
    protected function restart()
    {
        $pid = $this->getMasterPid();

        if (isset($pid[0]) && $this->isRunning($pid[0])) {
            $this->stop();
        }

        $this->start();
    }

    /**
     * 柔性重启server (未实现)
     * @access protected
     * @return void
     */
    protected function reload()
    {
        $pids = $this->getMasterPid();

        if (!isset($pids[0]) || !$this->isRunning($pids[0])) {
            $this->output->writeln('<error>no swoole http server process running.</error>');
            return;
        }

        $this->output->writeln('Reloading swoole http server...');

        foreach ($pids as $pid) {
            $isRunning = $this->killProcess($pid, SIGUSR1);
            if (!$isRunning) {
                $this->output->error('> failure');
                return;
            }
        }

        $this->output->writeln('> success');
    }

    /**
     * 获取主进程PID
     * @access protected
     * @return int
     */
    protected function getMasterPid()
    {
        $pidFile = $this->getPidPath();

        if (is_file($pidFile)) {
            $masterPid = file_get_contents($pidFile);
            $masterPid = array_values(array_unique(array_filter(explode('|', $masterPid))));
        } else {
            $masterPid = 0;
        }

        return $masterPid;
    }

    /**
     * Get Pid file path.
     *
     * @return string
     */
    protected function getPidPath()
    {
        return config('swoole.options.pid_file');
    }

    /**
     * 判断PID是否在运行
     * @access protected
     * @param int $pid
     * @return bool
     */
    protected function isRunning($pid)
    {
        if (empty($pid)) {
            return false;
        }

        try {
            return Process::kill($pid, 0);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * 杀死进程
     * @param     $pid
     * @param     $sig
     * @param int $wait
     * @return bool
     */
    protected function killProcess($pid, $sig, $wait = 0)
    {
        Process::kill($pid, $sig);

        if ($wait) {
            $start = time();

            do {
                if (!$this->isRunning($pid)) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return $this->isRunning($pid);
    }

    /**
     * 删除PID文件
     * @access protected
     * @return void
     */
    protected function removePid()
    {
        $masterPid = $this->getPidPath();

        if (file_exists($masterPid)) {
            unlink($masterPid);
        }
    }
}
