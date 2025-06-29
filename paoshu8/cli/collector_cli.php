<?php
#!/usr/bin/env php
<?php
/**
 * 多源采集器命令行工具
 * 提供便捷的命令行接口来管理采集任务
 */

require_once dirname(__DIR__) . '/../library/init.inc.php';
require_once dirname(__DIR__) . '/multi_source_collector.php';
require_once dirname(__DIR__) . '/utils/task_manager.php';
require_once dirname(__DIR__) . '/utils/progress_tracker.php';
require_once dirname(__DIR__) . '/utils/collector_logger.php';

class CollectorCLI {
    
    private $collector;
    private $taskManager;
    private $progressTracker;
    private $logger;
    private $config;
    
    public function __construct() {
        global $redis_data;
        
        $this->config = require dirname(__DIR__) . '/config/collector_config.php';
        $this->collector = new MultiSourceCollector();
        $this->taskManager = new TaskManager($redis_data);
        $this->progressTracker = new ProgressTracker($redis_data);
        $this->logger = new CollectorLogger();
    }
    
    /**
     * 运行CLI
     */
    public function run($argv) {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $argv[1];
        $args = array_slice($argv, 2);
        
        switch ($command) {
            case 'start':
                $this->startCollection($args);
                break;
                
            case 'status':
                $this->showStatus($args);
                break;
                
            case 'stop':
                $this->stopCollection($args);
                break;
                
            case 'resume':
                $this->resumeCollection($args);
                break;
                
            case 'stats':
                $this->showStats($args);
                break;
                
            case 'cleanup':
                $this->cleanup($args);
                break;
                
            case 'config':
                $this->showConfig($args);
                break;
                
            case 'sources':
                $this->listSources();
                break;
                
            case 'restart':
                $this->restartFailedTasks($args);
                break;
                
            default:
                echo "未知命令: {$command}\n";
                $this->showHelp();
                break;
        }
    }
    
    /**
     * 开始采集
     */
    private function startCollection($args) {
        $sources = [];
        $options = $this->config['global'];
        
        // 解析参数
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            
            if ($arg === '--sources' && isset($args[$i + 1])) {
                $sources = explode(',', $args[$i + 1]);
                $i++;
            } elseif ($arg === '--batch-size' && isset($args[$i + 1])) {
                $options['batch_size'] = intval($args[$i + 1]);
                $i++;
            } elseif ($arg === '--workers' && isset($args[$i + 1])) {
                $options['max_workers'] = intval($args[$i + 1]);
                $i++;
            } elseif ($arg === '--no-resume') {
                $options['resume_from_break'] = false;
            } elseif ($arg === '--log-level' && isset($args[$i + 1])) {
                $options['log_level'] = $args[$i + 1];
                $i++;
            }
        }
        
        // 验证源
        if (!empty($sources)) {
            $validSources = array_keys($this->config['sources']);
            $invalidSources = array_diff($sources, $validSources);
            
            if (!empty($invalidSources)) {
                echo "无效的采集源: " . implode(', ', $invalidSources) . "\n";
                echo "可用的采集源: " . implode(', ', $validSources) . "\n";
                return;
            }
            
            // 检查源是否启用
            foreach ($sources as $source) {
                if (!$this->config['sources'][$source]['enabled']) {
                    echo "采集源 {$source} 已禁用\n";
                    return;
                }
            }
        }
        
        echo "启动多源采集器...\n";
        echo "采集源: " . (empty($sources) ? "全部启用的源" : implode(', ', $sources)) . "\n";
        echo "批次大小: {$options['batch_size']}\n";
        echo "工作进程数: {$options['max_workers']}\n";
        echo "断点续传: " . ($options['resume_from_break'] ? '启用' : '禁用') . "\n";
        echo "日志级别: {$options['log_level']}\n";
        echo "----------------------------------------\n";
        
        try {
            $this->collector->startCollection($sources, $options);
            echo "采集任务完成!\n";
        } catch (Exception $e) {
            echo "采集任务失败: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * 显示状态
     */
    private function showStatus($args) {
        $taskId = $args[0] ?? null;
        
        if ($taskId) {
            $this->showTaskStatus($taskId);
        } else {
            $this->showOverallStatus();
        }
    }
    
    /**
     * 显示任务状态
     */
    private function showTaskStatus($taskId) {
        $mainTask = $this->taskManager->getMainTask($taskId);
        
        if (!$mainTask) {
            echo "任务不存在: {$taskId}\n";
            return;
        }
        
        $subTasks = $this->taskManager->getSubTasks($taskId);
        $stats = $this->taskManager->getTaskStats($taskId);
        
        echo "任务ID: {$taskId}\n";
        echo "状态: {$mainTask['status']}\n";
        echo "创建时间: " . date('Y-m-d H:i:s', $mainTask['created_at']) . "\n";
        
        if (isset($mainTask['completed_at'])) {
            echo "完成时间: " . date('Y-m-d H:i:s', $mainTask['completed_at']) . "\n";
        }
        
        echo "采集源: " . implode(', ', $mainTask['sources']) . "\n";
        echo "\n子任务统计:\n";
        echo "  总数: {$stats['total']}\n";
        echo "  等待中: {$stats['pending']}\n";
        echo "  进行中: {$stats['running']}\n";
        echo "  已完成: {$stats['completed']}\n";
        echo "  失败: {$stats['failed']}\n";
        
        if ($stats['total'] > 0) {
            $progress = round(($stats['completed'] + $stats['failed']) / $stats['total'] * 100, 2);
            echo "  进度: {$progress}%\n";
        }
    }
    
    /**
     * 显示整体状态
     */
    private function showOverallStatus() {
        $stats = $this->progressTracker->getCollectionStats();
        
        echo "采集器整体状态:\n";
        echo "  总小说数: {$stats['total']}\n";
        echo "  已完成: {$stats['completed']}\n";
        echo "  失败: {$stats['failed']}\n";
        echo "  进行中: {$stats['in_progress']}\n";
        echo "  总章节数: {$stats['total_chapters']}\n";
        
        if (!empty($stats['sources'])) {
            echo "\n按源统计:\n";
            foreach ($stats['sources'] as $source => $sourceStats) {
                echo "  {$source}:\n";
                echo "    总数: {$sourceStats['total']}\n";
                echo "    完成: {$sourceStats['completed']}\n";
                echo "    失败: {$sourceStats['failed']}\n";
            }
        }
    }
    
    /**
     * 停止采集
     */
    private function stopCollection($args) {
        $taskId = $args[0] ?? null;
        
        if (!$taskId) {
            echo "请指定任务ID\n";
            return;
        }
        
        // 这里可以实现停止逻辑
        echo "停止功能待实现\n";
    }
    
    /**
     * 恢复采集
     */
    private function resumeCollection($args) {
        $taskId = $args[0] ?? null;
        
        if (!$taskId) {
            echo "请指定任务ID\n";
            return;
        }
        
        $restarted = $this->taskManager->restartFailedTasks($taskId);
        echo "重启了 {$restarted} 个失败的任务\n";
    }
    
    /**
     * 显示统计信息
     */
    private function showStats($args) {
        $source = $args[0] ?? null;
        $timeRange = isset($args[1]) ? intval($args[1]) * 3600 : 86400; // 默认24小时
        
        $stats = $this->progressTracker->getCollectionStats($source, $timeRange);
        
        echo "采集统计信息 (最近 " . ($timeRange / 3600) . " 小时):\n";
        
        if ($source) {
            echo "采集源: {$source}\n";
        }
        
        echo "  总小说数: {$stats['total']}\n";
        echo "  已完成: {$stats['completed']}\n";
        echo "  失败: {$stats['failed']}\n";
        echo "  进行中: {$stats['in_progress']}\n";
        echo "  总章节数: {$stats['total_chapters']}\n";
        
        if ($stats['total'] > 0) {
            $successRate = round($stats['completed'] / $stats['total'] * 100, 2);
            echo "  成功率: {$successRate}%\n";
        }
    }
    
    /**
     * 清理数据
     */
    private function cleanup($args) {
        $type = $args[0] ?? 'all';
        
        switch ($type) {
            case 'logs':
                $this->logger->cleanupOldLogs($this->config['cleanup']['log_retention_days']);
                echo "清理旧日志完成\n";
                break;
                
            case 'progress':
                $this->progressTracker->cleanupExpiredProgress();
                echo "清理过期进度数据完成\n";
                break;
                
            case 'tasks':
                $this->taskManager->cleanupCompletedTasks($this->config['cleanup']['completed_task_retention_hours'] * 3600);
                echo "清理已完成任务完成\n";
                break;
                
            case 'all':
                $this->logger->cleanupOldLogs($this->config['cleanup']['log_retention_days']);
                $this->progressTracker->cleanupExpiredProgress();
                $this->taskManager->cleanupCompletedTasks($this->config['cleanup']['completed_task_retention_hours'] * 3600);
                echo "全部清理完成\n";
                break;
                
            default:
                echo "无效的清理类型: {$type}\n";
                echo "可用类型: logs, progress, tasks, all\n";
                break;
        }
    }
    
    /**
     * 显示配置
     */
    private function showConfig($args) {
        $section = $args[0] ?? null;
        
        if ($section && isset($this->config[$section])) {
            echo "配置节: {$section}\n";
            print_r($this->config[$section]);
        } else {
            echo "全部配置:\n";
            print_r($this->config);
        }
    }
    
    /**
     * 列出采集源
     */
    private function listSources() {
        echo "可用的采集源:\n";
        
        foreach ($this->config['sources'] as $key => $source) {
            $status = $source['enabled'] ? '启用' : '禁用';
            echo "  {$key}: {$source['name']} ({$status})\n";
            echo "    基础URL: {$source['base_url']}\n";
            echo "    并发限制: {$source['concurrent_limit']}\n";
            echo "    请求间隔: {$source['delay']}ms\n";
            echo "\n";
        }
    }
    
    /**
     * 重启失败的任务
     */
    private function restartFailedTasks($args) {
        $taskId = $args[0] ?? null;
        
        if (!$taskId) {
            echo "请指定任务ID\n";
            return;
        }
        
        $restarted = $this->taskManager->restartFailedTasks($taskId);
        echo "重启了 {$restarted} 个失败的任务\n";
    }
    
    /**
     * 显示帮助信息
     */
    private function showHelp() {
        echo "多源小说采集器 CLI 工具\n\n";
        echo "用法: php collector_cli.php <命令> [选项]\n\n";
        echo "命令:\n";
        echo "  start [选项]              开始采集任务\n";
        echo "    --sources <源列表>      指定采集源，用逗号分隔\n";
        echo "    --batch-size <数量>     每批处理的小说数量\n";
        echo "    --workers <数量>        工作进程数\n";
        echo "    --no-resume             禁用断点续传\n";
        echo "    --log-level <级别>      日志级别 (DEBUG, INFO, WARNING, ERROR)\n";
        echo "\n";
        echo "  status [任务ID]           显示状态信息\n";
        echo "  stop <任务ID>             停止指定任务\n";
        echo "  resume <任务ID>           恢复指定任务\n";
        echo "  restart <任务ID>          重启失败的任务\n";
        echo "  stats [源] [小时数]       显示统计信息\n";
        echo "  cleanup [类型]            清理数据 (logs, progress, tasks, all)\n";
        echo "  config [节名]             显示配置信息\n";
        echo "  sources                   列出所有采集源\n";
        echo "\n";
        echo "示例:\n";
        echo "  php collector_cli.php start --sources paoshu8,biquge --workers 5\n";
        echo "  php collector_cli.php status task_12345\n";
        echo "  php collector_cli.php stats paoshu8 24\n";
        echo "  php collector_cli.php cleanup logs\n";
    }
}

// 运行CLI
if (is_cli()) {
    $cli = new CollectorCLI();
    $cli->run($argv);
}
?>