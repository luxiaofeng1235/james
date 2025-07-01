<?php
/**
 * 简单的环境变量获取脚本
 * 快速获取RUN_ENV系统环境变量
 */

/**
 * 获取RUN_ENV环境变量
 * @param string $default 默认值
 * @return string 环境变量值
 */
function getRunEnv($default = '') {
    // 方法1: getenv()
    $env = getenv('RUN_ENV');
    if ($env !== false) {
        return $env;
    }
    
    // 方法2: $_ENV
    if (isset($_ENV['RUN_ENV'])) {
        return $_ENV['RUN_ENV'];
    }
    
    // 方法3: $_SERVER
    if (isset($_SERVER['RUN_ENV'])) {
        return $_SERVER['RUN_ENV'];
    }
    
    return $default;
}

// 获取环境变量
$runEnv = getRunEnv();

// 输出结果
echo "RUN_ENV: $runEnv\n";

// 如果需要详细信息，可以添加 -v 参数
if (in_array('-v', $argv ?? []) || in_array('--verbose', $argv ?? [])) {
    echo "\n详细信息:\n";
    echo "- getenv('RUN_ENV'): " . (getenv('RUN_ENV') ?: '(未设置)') . "\n";
    echo "- \$_ENV['RUN_ENV']: " . ($_ENV['RUN_ENV'] ?? '(未设置)') . "\n";
    echo "- \$_SERVER['RUN_ENV']: " . ($_SERVER['RUN_ENV'] ?? '(未设置)') . "\n";
    echo "- 运行方式: " . php_sapi_name() . "\n";
}

// 如果作为函数库被包含，不执行输出
if (basename(__FILE__) !== basename($_SERVER['SCRIPT_NAME'] ?? '')) {
    return $runEnv;
}
?>
