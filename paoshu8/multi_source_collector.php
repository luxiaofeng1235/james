<?php
/**
 * 多源小说采集器 - 支持多线程并发和断点续传
 * 
 * 功能特性：
 * 1. 多源网站支持
 * 2. 多线程并发采集
 * 3. 断点续传机制
 * 4. 任务队列管理
 * 5. 进度监控和日志记录
 * 
 * @author xiaofeng.lu
 * @version 2.0
 */

ini_set("memory_limit", "8000M");
set_time_limit(0);

require_once dirname(__DIR__).'/library/init.inc.php';
require_once dirname(__DIR__).'/library/file_factory.php';
require_once dirname(__DIR__).'/library/process_url.php';

class MultiSourceCollector {
    
    private $mysql_obj;
    private $redis_data;
    private $config;
    private $logger;
    private $taskManager;
    private $progressTracker;
    
    // 支持的采集源配置
    private $supportedSources = [
        'paoshu8' => [
            'name' => '泡书吧',
            'base_url' => 'https://www.paoshu8.com',
            'rules_key' => 'paoshu8',
            'concurrent_limit' => 5,
            'delay' => 1000, // 毫秒
        ],
        'biquge' => [
            'name' => '笔趣阁',
            'base_url' => 'https://www.biquge.com',
            'rules_key' => 'biquge',
            'concurrent_limit' => 3,
            'delay' => 1500,
        ],
        'qidian' => [
            'name' => '起点中文网',
            'base_url' => 'https://www.qidian.com',
            'rules_key' => 'qidian',
            'concurrent_limit' => 2,
            'delay' => 2000,
        ],
        'zongheng' => [
            'name' => '纵横中文网',
            'base_url' => 'http://www.zongheng.com',
            'rules_key' => 'zongheng',
            'concurrent_limit' => 3,
            'delay' => 1500,
        ]
    ];
    
    public function __construct() {
        global $mysql_obj, $redis_data;
        
        $this->mysql_obj = $mysql_obj;
        $this->redis_data = $redis_data;
        $this->config = $this->loadConfig();
        $this->logger = new CollectorLogger();
        $this->taskManager = new TaskManager($this->redis_data);
        $this->progressTracker = new ProgressTracker($this->redis_data);
        
        $this->logger->info("多源采集器初始化完成");
    }
    
    /**
     * 启动多源采集任务
     * 
     * @param array $sources 要采集的源列表
     * @param array $options 采集选项
     */
    public function startCollection($sources = [], $options = []) {
        $defaultOptions = [
            'batch_size' => 50,        // 每批处理数量
            'max_workers' => 10,       // 最大工作进程数
            'resume_from_break' => true, // 是否从断点续传
            'save_progress_interval' => 10, // 保存进度间隔(秒)
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 如果没有指定源，使用所有支持的源
        if (empty($sources)) {
            $sources = array_keys($this->supportedSources);
        }
        
        $this->logger->info("开始多源采集任务", [
            'sources' => $sources,
            'options' => $options
        ]);
        
        // 创建主任务
        $mainTaskId = $this->taskManager->createMainTask($sources, $options);
        
        // 为每个源创建子任务
        foreach ($sources as $source) {
            if (!isset($this->supportedSources[$source])) {
                $this->logger->warning("不支持的采集源: {$source}");
                continue;
            }
            
            $this->createSourceTasks($source, $mainTaskId, $options);
        }
        
        // 启动多进程采集
        $this->executeMultiProcessCollection($mainTaskId, $options);
    }
    
    /**
     * 为指定源创建采集任务
     */
    private function createSourceTasks($source, $mainTaskId, $options) {
        $sourceConfig = $this->supportedSources[$source];
        
        // 获取待采集的小说列表
        $novelList = $this->getNovelListBySource($source, $options);
        
        if (empty($novelList)) {
            $this->logger->info("源 {$source} 没有待采集的小说");
            return;
        }
        
        // 分批创建任务
        $batches = array_chunk($novelList, $options['batch_size']);
        
        foreach ($batches as $batchIndex => $batch) {
            $taskData = [
                'source' => $source,
                'batch_index' => $batchIndex,
                'novels' => $batch,
                'source_config' => $sourceConfig,
                'main_task_id' => $mainTaskId
            ];
            
            $taskId = $this->taskManager->createSubTask($mainTaskId, $taskData);
            $this->logger->info("为源 {$source} 创建批次任务 {$batchIndex}, 任务ID: {$taskId}");
        }
    }
    
    /**
     * 执行多进程采集
     */
    private function executeMultiProcessCollection($mainTaskId, $options) {
        $maxWorkers = $options['max_workers'];
        $workers = [];
        $progressTimer = null;
        
        // 启动进度保存定时器
        if ($options['save_progress_interval'] > 0) {
            $progressTimer = swoole_timer_tick($options['save_progress_interval'] * 1000, function() use ($mainTaskId) {
                $this->progressTracker->saveProgress($mainTaskId);
            });
        }
        
        // 创建工作进程
        for ($i = 0; $i < $maxWorkers; $i++) {
            $workers[$i] = $this->createWorkerProcess($i, $mainTaskId);
        }
        
        // 等待所有工作进程完成
        $this->waitForWorkers($workers);
        
        // 清理定时器
        if ($progressTimer) {
            swoole_timer_clear($progressTimer);
        }
        
        // 完成主任务
        $this->taskManager->completeMainTask($mainTaskId);
        $this->logger->info("多源采集任务完成: {$mainTaskId}");
    }
    
    /**
     * 创建工作进程
     */
    private function createWorkerProcess($workerId, $mainTaskId) {
        $process = new Swoole\Process(function($worker) use ($workerId, $mainTaskId) {
            $this->logger->info("工作进程 {$workerId} 启动");
            
            while (true) {
                // 获取待处理任务
                $task = $this->taskManager->getNextTask($mainTaskId);
                
                if (!$task) {
                    $this->logger->info("工作进程 {$workerId} 没有更多任务，退出");
                    break;
                }
                
                try {
                    // 处理任务
                    $this->processTask($task, $workerId);
                    
                    // 标记任务完成
                    $this->taskManager->completeTask($task['id']);
                    
                } catch (Exception $e) {
                    $this->logger->error("工作进程 {$workerId} 处理任务失败", [
                        'task_id' => $task['id'],
                        'error' => $e->getMessage()
                    ]);
                    
                    // 标记任务失败
                    $this->taskManager->failTask($task['id'], $e->getMessage());
                }
            }
            
            $this->logger->info("工作进程 {$workerId} 结束");
        });
        
        $process->start();
        return $process;
    }
    
    /**
     * 处理单个采集任务
     */
    private function processTask($task, $workerId) {
        $taskData = json_decode($task['data'], true);
        $source = $taskData['source'];
        $novels = $taskData['novels'];
        $sourceConfig = $taskData['source_config'];
        
        $this->logger->info("工作进程 {$workerId} 开始处理源 {$source} 的批次任务", [
            'task_id' => $task['id'],
            'novel_count' => count($novels)
        ]);
        
        // 创建源专用的采集器
        $collector = $this->createSourceCollector($source, $sourceConfig);
        
        foreach ($novels as $novel) {
            try {
                // 检查是否需要从断点续传
                $progress = $this->progressTracker->getNovelProgress($novel['store_id']);
                
                if ($progress && $progress['status'] === 'completed') {
                    $this->logger->info("小说已完成采集，跳过: {$novel['title']}");
                    continue;
                }
                
                // 采集小说信息
                $result = $collector->collectNovel($novel, $progress);
                
                if ($result['success']) {
                    $this->progressTracker->updateNovelProgress($novel['store_id'], [
                        'status' => 'completed',
                        'completed_at' => time(),
                        'chapters_collected' => $result['chapters_count']
                    ]);
                    
                    $this->logger->info("小说采集完成: {$novel['title']}", [
                        'chapters' => $result['chapters_count']
                    ]);
                } else {
                    $this->progressTracker->updateNovelProgress($novel['store_id'], [
                        'status' => 'failed',
                        'error' => $result['error'],
                        'failed_at' => time()
                    ]);
                    
                    $this->logger->error("小说采集失败: {$novel['title']}", [
                        'error' => $result['error']
                    ]);
                }
                
                // 添加延迟避免过于频繁的请求
                if ($sourceConfig['delay'] > 0) {
                    usleep($sourceConfig['delay'] * 1000);
                }
                
            } catch (Exception $e) {
                $this->logger->error("处理小说时发生异常: {$novel['title']}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * 创建源专用采集器
     */
    private function createSourceCollector($source, $sourceConfig) {
        switch ($source) {
            case 'paoshu8':
                return new Paoshu8Collector($sourceConfig, $this->mysql_obj, $this->redis_data, $this->logger);
            case 'biquge':
                return new BiqugeCollector($sourceConfig, $this->mysql_obj, $this->redis_data, $this->logger);
            case 'qidian':
                return new QidianCollector($sourceConfig, $this->mysql_obj, $this->redis_data, $this->logger);
            case 'zongheng':
                return new ZonghengCollector($sourceConfig, $this->mysql_obj, $this->redis_data, $this->logger);
            default:
                throw new Exception("不支持的采集源: {$source}");
        }
    }
    
    /**
     * 根据源获取待采集小说列表
     */
    private function getNovelListBySource($source, $options) {
        $table_novel_name = Env::get('APICONFIG.TABLE_NOVEL');
        
        $where = "source = '{$source}' AND is_async = 0";
        
        // 如果启用断点续传，排除已完成的
        if ($options['resume_from_break']) {
            $completedIds = $this->progressTracker->getCompletedNovelIds($source);
            if (!empty($completedIds)) {
                $where .= " AND store_id NOT IN (" . implode(',', $completedIds) . ")";
            }
        }
        
        $sql = "SELECT store_id, title, author, story_link, story_id 
                FROM {$table_novel_name} 
                WHERE {$where} 
                ORDER BY store_id ASC";
        
        return $this->mysql_obj->fetchAll($sql);
    }
    
    /**
     * 等待所有工作进程完成
     */
    private function waitForWorkers($workers) {
        foreach ($workers as $worker) {
            $worker->wait();
        }
    }
    
    /**
     * 加载配置
     */
    private function loadConfig() {
        return [
            'max_retry_times' => 3,
            'request_timeout' => 30,
            'concurrent_requests' => 10,
        ];
    }
    
    /**
     * 获取采集统计信息
     */
    public function getCollectionStats($mainTaskId) {
        return [
            'main_task' => $this->taskManager->getMainTask($mainTaskId),
            'sub_tasks' => $this->taskManager->getSubTasks($mainTaskId),
            'progress' => $this->progressTracker->getMainTaskProgress($mainTaskId)
        ];
    }
}

// 使用示例
if (is_cli()) {
    $collector = new MultiSourceCollector();
    
    // 从命令行参数获取配置
    $sources = isset($argv[1]) ? explode(',', $argv[1]) : [];
    $batchSize = isset($argv[2]) ? intval($argv[2]) : 50;
    $maxWorkers = isset($argv[3]) ? intval($argv[3]) : 5;
    
    $options = [
        'batch_size' => $batchSize,
        'max_workers' => $maxWorkers,
        'resume_from_break' => true,
        'save_progress_interval' => 10
    ];
    
    echo "启动多源采集器...\n";
    echo "采集源: " . (empty($sources) ? "全部" : implode(', ', $sources)) . "\n";
    echo "批次大小: {$batchSize}\n";
    echo "最大工作进程: {$maxWorkers}\n";
    echo "断点续传: 启用\n";
    echo "----------------------------------------\n";
    
    $collector->startCollection($sources, $options);
    
    echo "采集任务完成!\n";
}
?>