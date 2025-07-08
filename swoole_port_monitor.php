<?php
/**
 * Swoole Port Monitor
 * 
 * 这是一个基于 Swoole 的端口监控服务，用于实时监控指定端口的状态变化
 * 当端口状态发生改变（开启/关闭）时，会记录日志并输出状态信息
 * 
 * 特性:
 * - 使用 Swoole 协程实现高并发监控
 * - 定时器每5秒检查一次端口状态
 * - 只在状态发生变化时记录日志，避免spam
 * - 支持监控多个端口和服务
 * 
 * @author Your Name
 * @version 1.0
 * @since 2025-01-08
 */

// 检查 Swoole 扩展是否已加载
if (!extension_loaded('swoole')) {
    die("Swoole extension is required.\n");
}

// 引入 Swoole 相关类和函数
use Swoole\Timer;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

/**
 * 端口监控配置
 * 定义需要监控的端口列表，包含端口号和服务名称
 * 可以根据实际需要添加或修改监控的端口
 */
$ports_to_monitor = [
    ['port' => 9501, 'name' => 'HTTP Server'],  // Swoole HTTP 服务器
    ['port' => 6379, 'name' => 'Redis'],        // Redis 数据库
    ['port' => 3306, 'name' => 'MySQL'],        // MySQL 数据库
    // 可以在此处添加更多需要监控的端口
];

/**
 * 端口状态存储
 * 用于记录每个端口的上一次状态，以便检测状态变化
 * key: 端口号, value: 布尔值表示端口是否开启
 */
$port_states = [];

/**
 * 初始化端口状态
 * 将所有需要监控的端口初始状态设置为 false（关闭）
 * 这样可以确保在首次检查时能正确显示端口状态
 */
foreach ($ports_to_monitor as $service) {
    $port_states[$service['port']] = false;
}

/**
 * 检查指定端口是否开启
 * 
 * 使用 fsockopen 函数尝试连接到指定的主机和端口
 * 如果连接成功则表示端口开启，否则表示端口关闭
 * 
 * @param string $host 主机地址（IP或域名）
 * @param int $port 端口号
 * @return bool 端口是否开启，true表示开启，false表示关闭
 */
function checkPort($host, $port) {
    try {
        // 尝试建立socket连接，超时时间为1秒
        // 使用@符号抑制可能的警告信息
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        
        // 检查是否成功建立连接
        if (is_resource($connection)) {
            fclose($connection);  // 关闭连接
            return true;          // 端口开启
        }
        return false;  // 连接失败，端口关闭
    } catch (\Exception $e) {
        // 捕获任何异常，返回 false
        return false;
    }
}

/**
 * 记录状态日志
 * 
 * 输出带有时间戳的状态信息，便于追踪和调试
 * 
 * @param string $message 要记录的消息内容
 */
function logStatus($message) {
    $timestamp = date('Y-m-d H:i:s');  // 获取当前时间戳
    echo "[$timestamp] $message\n";   // 输出格式化的日志信息
}

// 启动监控服务
logStatus("Starting port monitoring service...");

/**
 * 创建定时器，每5秒执行一次端口检查
 * 
 * 使用 Swoole Timer 创建定时任务
 * 在协程环境中并发检查所有配置的端口状态
 * 当检测到端口状态变化时，记录相应的日志信息
 */
Timer::tick(5000, function () use ($ports_to_monitor, &$port_states) {
    // 在协程环境中运行端口检查
    Coroutine\run(function() use ($ports_to_monitor, &$port_states) {
        // 遍历所有需要监控的端口
        foreach ($ports_to_monitor as $service) {
            $port = $service['port'];  // 端口号
            $name = $service['name'];  // 服务名称
            $host = '127.0.0.1';       // 监控本地主机

            /**
             * 为每个端口创建独立的协程进行检查
             * 这样可以实现并发检查，提高监控效率
             */
            go(function() use ($host, $port, $name, &$port_states) {
                // 检查当前端口状态
                $is_open = checkPort($host, $port);
                
                /**
                 * 状态变化检测
                 * 只有当端口状态发生改变时才记录日志
                 * 这样可以避免重复的状态信息flooding日志
                 */
                if ($port_states[$port] !== $is_open) {
                    // 确定状态描述
                    $status = $is_open ? 'OPENED' : 'CLOSED';
                    
                    // 记录状态变化日志
                    logStatus("Port $port ($name) has changed state to: $status");
                    
                    // 更新端口状态记录
                    $port_states[$port] = $is_open;
                }
            });
        }
    });
});

/**
 * 保持进程运行
 * 
 * 使用 Swoole Event 循环保持进程持续运行
 * 确保定时器能够持续执行端口监控任务
 */
Swoole\Event::wait();
