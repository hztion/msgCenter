<?php

// +----------------------------------------------------------------------
// | swoole设置
// +----------------------------------------------------------------------

return [
    'host' => '127.0.0.1', // 监听地址
    'port' => 9501, // 监听端口
    'options' => [
        'pid_file'              => env('runtime_path') . 'swoole.pid',
        'log_file'              => env('runtime_path') . 'swoole.log',
        'daemonize'             => false,
        'enable_static_handler' => true,
        'document_root'         => env('root_path') . '/public',
        'package_max_length'    => 20 * 1024 * 1024,
        'buffer_output_size'    => 10 * 1024 * 1024,
        'socket_buffer_size'    => 128 * 1024 * 1024,
        'max_request'           => 3000,
        'send_yield'            => true,
    ],
    'http_worker_num' => 4,
    'national_msg_worker_num' => 2,
    'add_log_worker_num' => 2,
    'send_email_worker_num' => 2,
    'send_msg_worker_num' => 2
];
