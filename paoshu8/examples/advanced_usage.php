<?php
/**
 * 高级使用示例
 * 演示多源采集器的高级功能，包括自定义配置、监控、错误处理等
 */

require_once dirname(__DIR__) . '/../library/init.inc.php';
require_once dirname(__DIR__) . '/multi_source_collector.php';
require_once dirname(__DIR__) . '/utils/task_manager.php';
require_once dirname(__DIR__) . '/utils/progress_tracker.php';
require_once dirname(__DIR__) . '/utils/collector_logger.php';

echo "=== 多源小说采集器 - 高级使用示例 ===\n\n";

class AdvancedCollectorDemo {
    
    private $collector;
    private $taskManager;
    private $progressTracker;
    private $logger;
    
    public function __construct() {
        global $redis_data;
        
        $this->collector = new MultiSourceCollector();
        $this->taskManager = new TaskManager($redis_data);
        $this->progressTracker = new ProgressTracker($redis_data);
        $this->logger = new CollectorLogger();
    }
    
    /**
     * 演示高级配置使用
     */
    public function demonstrateAdvancedConfig() {
        echo "1. 高级配置示例\n";
        echo "================\n";
        
        // 自定义配置
        $customConfig = [
            'batch_size' => 50,
            'max_workers' => 8,
            'resume_from_break' => true,
            'save_progress_interval' => 10,
            'log_level' => 'DEBUG'
        ];
        
        echo "自定义配置:\n";
        foreach ($customConfig as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
        
        // 多源配置
        $sources = ['paoshu8', 'biquge'];
        echo "采集源配置:\n";
        foreach ($sources as $source) {
            echo "  - {$source}: 启用\n";
        }
        echo "\n";
    }
    
    /**
     * 演示任务管理功能
     */
    public function demonstrateTaskManagement() {
        echo "2. 任务管理示例\n";
        echo "================\n";
        
        try {
            // 创建演示任务
            echo "创建演示任务...\n";
            $mainTaskId = $this->taskManager->createMainTask(['paoshu8'], [
                'batch_size' => 10,
                'max_workers' => 2
            ]);
            echo "主任务ID: {$mainTaskId}\n";
            
            // 创建子任务
            for ($i = 1; $i <= 3; $i++) {
                $subTaskId = $this->taskManager->createSubTask($mainTaskId, [
                    'source' => 'paoshu8',
                    'batch_index' => $i,
                    'novels' => $this->generateDemoNovels(5)
                ]);
                echo "子任务 {$i} ID: {$subTaskId}\n";
            }
            
            // 获取任务统计
            $stats = $this->taskManager->getTaskStats($mainTaskId);
            echo "\n任务统计:\n";
            echo "  总任务数: {$stats['total']}\n";
            echo "  等待中: {$stats['pending']}\n";
            echo "  运行中: {$stats['running']}\n";
            echo "  已完成: {$stats['completed']}\n";
            echo "  失败: {$stats['failed']}\n";
            
            // 清理演示任务
            $this->cleanupDemoTasks($mainTaskId);
            echo "\n✓ 演示任务已清理\n\n";
            
        } catch (Exception $e) {
            echo "❌ 任务管理演示失败: " . $e->getMessage() . "\n\n";
        }
    }
    
    /**
     * 演示进度跟踪功能
     */
    public function demonstrateProgressTracking() {
        echo "3. 进度跟踪示例\n";
        echo "================\n";
        
        // 模拟进度跟踪
        $demoStoreIds = [12345, 12346, 12347];
        
        foreach ($demoStoreIds as $storeId) {
            // 标记开始
            $this->progressTracker->markNovelStarted($storeId, 'paoshu8', "演示小说{$storeId}");
            echo "开始采集小说 {$storeId}\n";
            
            // 模拟进度更新
            $this->progressTracker->updateNovelProgress($storeId, [
                'current_chapter' => 50,
                'total_chapters' => 100,
                'progress_percent' => 50
            ]);
            echo "  进度: 50/100 章节 (50%)\n";
            
            // 标记完成
            $this->progressTracker->markNovelCompleted($storeId, 100);
            echo "  ✓ 采集完成: 100章节\n";
        }
        
        // 获取统计信息
        $stats = $this->progressTracker->getCollectionStats('paoshu8', 3600);
        echo "\n采集统计 (最近1小时):\n";
        echo "  总小说数: {$stats['total']}\n";
        echo "  已完成: {$stats['completed']}\n";
        echo "  失败: {$stats['failed']}\n";
        echo "  总章节数: {$stats['total_chapters']}\n";
        
        // 清理演示数据
        foreach ($demoStoreIds as $storeId) {
            $this->progressTracker->updateNovelProgress($storeId, ['demo' => true, 'cleanup' => time()]);
        }
        echo "\n✓ 演示进度数据已标记清理\n\n";
    }
    
    /**
     * 演示日志功能
     */
    public function demonstrateLogging() {
        echo "4. 日志系统示例\n";
        echo "================\n";
        
        // 不同级别的日志
        $this->logger->debug("这是调试信息", ['demo' => true, 'level' => 'debug']);
        $this->logger->info("这是一般信息", ['demo' => true, 'level' => 'info']);
        $this->logger->warning("这是警告信息", ['demo' => true, 'level' => 'warning']);
        $this->logger->error("这是错误信息", ['demo' => true, 'level' => 'error']);
        
        // 性能日志
        $startTime = microtime(true);
        usleep(100000); // 模拟100ms操作
        $duration = microtime(true) - $startTime;
        $this->logger->logPerformance("demo_operation", $duration, ['demo' => true]);
        
        // HTTP请求日志
        $this->logger->logRequest("https://example.com/demo", "GET", 200, 0.5);
        
        // 数据库操作日志
        $this->logger->logDatabase("SELECT", "demo_table", 0.01, 100);
        
        // 采集统计日志
        $this->logger->logCollectionStats("paoshu8", [
            'novels_processed' => 10,
            'chapters_collected' => 1000,
            'success_rate' => 95.5
        ]);
        
        echo "✓ 各种类型的日志已记录\n";
        echo "  查看日志文件: paoshu8/logs/collector_" . date('Y-m-d') . ".log\n\n";
    }
    
    /**
     * 演示错误处理和恢复
     */
    public function demonstrateErrorHandling() {
        echo "5. 错误处理示例\n";
        echo "================\n";
        
        // 模拟网络错误
        echo "模拟网络超时错误...\n";
        try {
            throw new Exception("Connection timeout after 30 seconds");
        } catch (Exception $e) {
            $this->logger->warning("网络请求失败，准备重试", [
                'error' => $e->getMessage(),
                'retry_count' => 1,
                'max_retries' => 3
            ]);
            echo "  ⚠️  网络错误已记录，系统将自动重试\n";
        }
        
        // 模拟解析错误
        echo "模拟HTML解析错误...\n";
        try {
            throw new Exception("Failed to parse novel title from HTML");
        } catch (Exception $e) {
            $this->logger->error("HTML解析失败", [
                'error' => $e->getMessage(),
                'url' => 'https://example.com/novel/123',
                'html_length' => 1024
            ]);
            echo "  ❌ 解析错误已记录，将跳过此小说\n";
        }
        
        // 模拟数据库错误
        echo "模拟数据库连接错误...\n";
        try {
            throw new Exception("MySQL server has gone away");
        } catch (Exception $e) {
            $this->logger->critical("数据库连接丢失", [
                'error' => $e->getMessage(),
                'connection_id' => 12345
            ]);
            echo "  🔥 严重错误已记录，系统将尝试重连\n";
        }
        
        echo "\n✓ 错误处理演示完成\n";
        echo "  所有错误都已记录到日志文件中\n\n";
    }
    
    /**
     * 演示监控功能
     */
    public function demonstrateMonitoring() {
        echo "6. 监控功能示例\n";
        echo "================\n";
        
        // 系统资源监控
        echo "系统资源状态:\n";
        echo "  内存使用: " . $this->formatBytes(memory_get_usage(true)) . "\n";
        echo "  峰值内存: " . $this->formatBytes(memory_get_peak_usage(true)) . "\n";
        
        // 模拟性能指标
        $metrics = [
            'requests_per_second' => rand(10, 50),
            'average_response_time' => rand(100, 500) / 1000,
            'error_rate' => rand(1, 5) / 100,
            'active_workers' => rand(3, 8)
        ];
        
        echo "\n性能指标:\n";
        foreach ($metrics as $metric => $value) {
            echo "  {$metric}: {$value}\n";
        }
        
        // 模拟告警检查
        echo "\n告警检查:\n";
        if ($metrics['error_rate'] > 0.05) {
            echo "  ⚠️  错误率过高: " . ($metrics['error_rate'] * 100) . "%\n";
        } else {
            echo "  ✓ 错误率正常: " . ($metrics['error_rate'] * 100) . "%\n";
        }
        
        if ($metrics['average_response_time'] > 0.3) {
            echo "  ⚠️  响应时间过长: " . $metrics['average_response_time'] . "s\n";
        } else {
            echo "  ✓ 响应时间正常: " . $metrics['average_response_time'] . "s\n";
        }
        
        echo "\n✓ 监控功能演示完成\n\n";
    }
    
    /**
     * 演示自定义采集器
     */
    public function demonstrateCustomCollector() {
        echo "7. 自定义采集器示例\n";
        echo "====================\n";
        
        echo "自定义采集器的实现步骤:\n";
        echo "1. 继承 BaseCollector 类\n";
        echo "2. 实现 collectNovel() 方法\n";
        echo "3. 定义解析规则\n";
        echo "4. 处理特殊逻辑\n";
        echo "5. 注册到采集器工厂\n\n";
        
        echo "示例代码:\n";
        echo "```php\n";
        echo "class CustomCollector extends BaseCollector {\n";
        echo "    public function collectNovel(\$novel, \$progress = null) {\n";
        echo "        // 1. 获取HTML内容\n";
        echo "        \$html = \$this->getNovelHtml(\$novel['story_link'], \$novel['story_id']);\n";
        echo "        \n";
        echo "        // 2. 解析小说信息\n";
        echo "        \$storeData = \$this->parseNovelInfo(\$html, \$this->getParseRules());\n";
        echo "        \n";
        echo "        // 3. 处理数据\n";
        echo "        \$storeData = \$this->processNovelData(\$storeData, \$novel);\n";
        echo "        \n";
        echo "        // 4. 返回结果\n";
        echo "        return ['success' => true, 'chapters_count' => 100];\n";
        echo "    }\n";
        echo "}\n";
        echo "```\n\n";
        
        echo "✓ 自定义采集器示例完成\n\n";
    }
    
    /**
     * 运行所有演示
     */
    public function runAllDemos() {
        $this->demonstrateAdvancedConfig();
        $this->demonstrateTaskManagement();
        $this->demonstrateProgressTracking();
        $this->demonstrateLogging();
        $this->demonstrateErrorHandling();
        $this->demonstrateMonitoring();
        $this->demonstrateCustomCollector();
        
        echo "=== 高级使用示例完成 ===\n";
        echo "提示: 查看日志文件了解详细的执行信息\n";
        echo "日志位置: paoshu8/logs/collector_" . date('Y-m-d') . ".log\n";
    }
    
    /**
     * 生成演示小说数据
     */
    private function generateDemoNovels($count) {
        $novels = [];
        for ($i = 1; $i <= $count; $i++) {
            $novels[] = [
                'store_id' => 10000 + $i,
                'story_id' => 'demo_' . $i,
                'title' => "演示小说{$i}",
                'author' => "演示作者{$i}",
                'story_link' => "https://example.com/novel/demo_{$i}",
                'is_async' => 0
            ];
        }
        return $novels;
    }
    
    /**
     * 清理演示任务
     */
    private function cleanupDemoTasks($mainTaskId) {
        // 在实际环境中，这里会清理Redis中的任务数据
        // 演示模式下只是标记清理
        $this->logger->info("清理演示任务", ['main_task_id' => $mainTaskId, 'demo' => true]);
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// 运行演示
try {
    $demo = new AdvancedCollectorDemo();
    $demo->runAllDemos();
} catch (Exception $e) {
    echo "❌ 演示过程中发生错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
?>