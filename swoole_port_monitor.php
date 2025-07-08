<?php
if (!extension_loaded('swoole')) {
    die("Swoole extension is required.\n");
}

use Swoole\Timer;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

// Configuration for ports to monitor
$ports_to_monitor = [
    ['port' => 9501, 'name' => 'HTTP Server'],
    ['port' => 6379, 'name' => 'Redis'],
    ['port' => 3306, 'name' => 'MySQL'],
    // Add more ports as needed
];

// Store port states
$port_states = [];

// Initialize port states
foreach ($ports_to_monitor as $service) {
    $port_states[$service['port']] = false;
}

// Function to check if a port is open
function checkPort($host, $port) {
    try {
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    } catch (\Exception $e) {
        return false;
    }
}

// Function to log status with timestamp
function logStatus($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
}

// Start monitoring
logStatus("Starting port monitoring service...");

// Create timer to check ports every 5 seconds
Timer::tick(5000, function () use ($ports_to_monitor, &$port_states) {
    Coroutine\run(function() use ($ports_to_monitor, &$port_states) {
        foreach ($ports_to_monitor as $service) {
            $port = $service['port'];
            $name = $service['name'];
            $host = '127.0.0.1';

            // Check port in coroutine
            go(function() use ($host, $port, $name, &$port_states) {
                $is_open = checkPort($host, $port);
                
                // If state has changed, log it
                if ($port_states[$port] !== $is_open) {
                    $status = $is_open ? 'OPENED' : 'CLOSED';
                    logStatus("Port $port ($name) has changed state to: $status");
                    $port_states[$port] = $is_open;
                }
            });
        }
    });
});

// Keep the process running
Swoole\Event::wait();
