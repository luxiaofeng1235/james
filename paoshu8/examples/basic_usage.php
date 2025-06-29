<?php
/**
 * 基础使用示例
 * 演示如何使用多源采集器进行基本的小说采集
 */

require_once dirname(__DIR__) . '/../library/init.inc.php';
require_once dirname(__DIR__) . '/multi_source_collector.php';

echo "=== 多源小说采集器 - 基础使用示例 ===\n\n";

try {
    // 1. 创建采集器实例
    echo "1. 初始化采集器...\n";
    $collector = new MultiSourceCollector();
    echo "   ✓ 采集器初始化完成\n\n";
    
    // 2. 基础配置
    echo "2. 配置采集参数...\n";
    $sources = ['paoshu8'];  // 只使用泡书吧作为示例
    $options = [
        'batch_size' => 10,      // 小批量测试
        'max_workers' => 2,      // 少量工作进程
        'resume_from_break' => true,
        'save_progress_interval' => 5,
        'log_level' => 'INFO'
    ];
    
    echo "   采集源: " . implode(', ', $sources) . "\n";
    echo "   批次大小: {$options['batch_size']}\n";
    echo "   工作进程: {$options['max_workers']}\n";
    echo "   断点续传: " . ($options['resume_from_break'] ? '启用' : '禁用') . "\n";
    echo "   ✓ 参数配置完成\n\n";
    
    // 3. 启动采集
    echo "3. 启动采集任务...\n";
    echo "   注意: 这是一个演示，实际采集可能需要较长时间\n";
    echo "   您可以随时按 Ctrl+C 中断程序\n\n";
    
    // 模拟启动采集（实际环境中取消注释下面的代码）
    /*
    $collector->startCollection($sources, $options);
    echo "   ✓ 采集任务完成\n\n";
    */
    
    // 演示模式：显示会执行的操作
    echo "   [演示模式] 将执行以下操作:\n";
    echo "   - 从数据库获取待采集的小说列表\n";
    echo "   - 创建主任务和子任务\n";
    echo "   - 启动 {$options['max_workers']} 个工作进程\n";
    echo "   - 每个进程处理 {$options['batch_size']} 本小说\n";
    echo "   - 实时保存进度，支持断点续传\n";
    echo "   - 记录详细的采集日志\n\n";
    
    // 4. 演示其他功能
    echo "4. 其他可用功能:\n";
    
    // 获取采集源列表
    echo "   可用的采集源:\n";
    $config = require dirname(__DIR__) . '/config/collector_config.php';
    foreach ($config['sources'] as $sourceKey => $sourceConfig) {
        $status = $sourceConfig['enabled'] ? '启用' : '禁用';
        echo "   - {$sourceKey}: {$sourceConfig['name']} ({$status})\n";
    }
    echo "\n";
    
    // 演示统计功能
    echo "   统计功能示例:\n";
    echo "   - 查看整体采集状态\n";
    echo "   - 按采集源查看统计\n";
    echo "   - 查看性能指标\n";
    echo "   - 查看错误报告\n\n";
    
    echo "5. 命令行使用示例:\n";
    echo "   # 启动采集\n";
    echo "   php paoshu8/cli/collector_cli.php start --sources paoshu8 --workers 2\n\n";
    echo "   # 查看状态\n";
    echo "   php paoshu8/cli/collector_cli.php status\n\n";
    echo "   # 查看统计\n";
    echo "   php paoshu8/cli/collector_cli.php stats paoshu8\n\n";
    
    echo "=== 基础使用示例完成 ===\n";
    echo "提示: 要运行实际的采集任务，请取消注释第39行的代码\n";
    
} catch (Exception $e) {
    echo "❌ 发生错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
?>