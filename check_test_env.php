<?php
echo "<h2>环境变量诊断</h2>\n";

// 1. 测试各种获取方法
echo "<h3>1. PHP 获取方法测试</h3>\n";
$tests = [
    'getenv("RUN_ENV")' => getenv('RUN_ENV'),
    '$_ENV["RUN_ENV"]' => $_ENV['RUN_ENV'] ?? 'undefined',
    '$_SERVER["RUN_ENV"]' => $_SERVER['RUN_ENV'] ?? 'undefined'
];

foreach ($tests as $method => $result) {
    $status = $result ? "✅" : "❌";
    echo "{$status} {$method}: " . ($result ?: '(空)') . "<br>\n";
}

// 2. 检查 PHP 配置
echo "<h3>2. PHP 配置检查</h3>\n";
echo "variables_order: " . ini_get('variables_order') . "<br>\n";
echo "PHP SAPI: " . php_sapi_name() . "<br>\n";
echo "PHP 版本: " . PHP_VERSION . "<br>\n";

// 3. 显示所有环境变量
echo "<h3>3. 所有环境变量</h3>\n";
echo "<details><summary>点击查看所有 \$_SERVER 变量</summary>\n";
echo "<pre>";
foreach ($_SERVER as $key => $value) {
    if (is_string($value)) {
        echo htmlspecialchars("{$key} = {$value}") . "\n";
    }
}
echo "</pre></details>\n";

// 4. 搜索包含 RUN 的变量
echo "<h3>4. 包含 'RUN' 的变量</h3>\n";
$found = false;
foreach ($_SERVER as $key => $value) {
    if (stripos($key, 'RUN') !== false && is_string($value)) {
        echo "🔍 {$key} = {$value}<br>\n";
        $found = true;
    }
}
if (!$found) {
    echo "❌ 未找到包含 'RUN' 的变量<br>\n";
}
?>