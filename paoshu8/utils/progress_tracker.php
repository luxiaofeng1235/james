<?php
/**
 * 进度跟踪器
 * 负责跟踪采集进度，支持断点续传
 */

class ProgressTracker {
    
    private $redis;
    private $keyPrefix = 'collector:progress:';
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    /**
     * 更新小说采集进度
     */
    public function updateNovelProgress($storeId, $progress) {
        $key = $this->keyPrefix . 'novel:' . $storeId;
        
        $currentProgress = $this->getNovelProgress($storeId);
        
        if ($currentProgress) {
            $progress = array_merge($currentProgress, $progress);
        }
        
        $progress['updated_at'] = time();
        
        $this->redis->set_redis($key, json_encode($progress), 86400 * 7); // 保存7天
        
        return true;
    }
    
    /**
     * 获取小说采集进度
     */
    public function getNovelProgress($storeId) {
        $key = $this->keyPrefix . 'novel:' . $storeId;
        $data = $this->redis->get_redis($key);
        
        if (!$data) {
            return null;
        }
        
        return json_decode($data, true);
    }
    
    /**
     * 获取已完成的小说ID列表
     */
    public function getCompletedNovelIds($source = null) {
        $pattern = $this->keyPrefix . 'novel:*';
        $keys = $this->redis->keys($pattern);
        $completedIds = [];
        
        foreach ($keys as $key) {
            $data = $this->redis->get_redis($key);
            
            if (!$data) {
                continue;
            }
            
            $progress = json_decode($data, true);
            
            if ($progress['status'] === 'completed') {
                // 从key中提取store_id
                $storeId = str_replace($this->keyPrefix . 'novel:', '', $key);
                
                // 如果指定了源，需要验证
                if ($source) {
                    if (isset($progress['source']) && $progress['source'] === $source) {
                        $completedIds[] = $storeId;
                    }
                } else {
                    $completedIds[] = $storeId;
                }
            }
        }
        
        return $completedIds;
    }
    
    /**
     * 保存主任务进度
     */
    public function saveMainTaskProgress($mainTaskId) {
        $key = $this->keyPrefix . 'main_task:' . $mainTaskId;
        
        $progress = [
            'saved_at' => time(),
            'checkpoint' => $this->createCheckpoint($mainTaskId)
        ];
        
        $this->redis->set_redis($key, json_encode($progress), 86400 * 30); // 保存30天
        
        return true;
    }
    
    /**
     * 获取主任务进度
     */
    public function getMainTaskProgress($mainTaskId) {
        $key = $this->keyPrefix . 'main_task:' . $mainTaskId;
        $data = $this->redis->get_redis($key);
        
        if (!$data) {
            return null;
        }
        
        return json_decode($data, true);
    }
    
    /**
     * 创建检查点
     */
    private function createCheckpoint($mainTaskId) {
        // 这里可以保存当前任务的详细状态
        // 包括已完成的任务、失败的任务等
        return [
            'main_task_id' => $mainTaskId,
            'timestamp' => time(),
            'completed_novels' => $this->getCompletedNovelIds(),
            // 可以添加更多检查点信息
        ];
    }
    
    /**
     * 从检查点恢复
     */
    public function restoreFromCheckpoint($mainTaskId) {
        $progress = $this->getMainTaskProgress($mainTaskId);
        
        if (!$progress || !isset($progress['checkpoint'])) {
            return null;
        }
        
        return $progress['checkpoint'];
    }
    
    /**
     * 清理过期的进度数据
     */
    public function cleanupExpiredProgress($olderThan = 86400 * 30) {
        $cutoff = time() - $olderThan;
        
        // 清理小说进度
        $pattern = $this->keyPrefix . 'novel:*';
        $keys = $this->redis->keys($pattern);
        
        foreach ($keys as $key) {
            $data = $this->redis->get_redis($key);
            
            if (!$data) {
                continue;
            }
            
            $progress = json_decode($data, true);
            
            if (isset($progress['updated_at']) && $progress['updated_at'] < $cutoff) {
                $this->redis->del_redis($key);
            }
        }
        
        // 清理主任务进度
        $pattern = $this->keyPrefix . 'main_task:*';
        $keys = $this->redis->keys($pattern);
        
        foreach ($keys as $key) {
            $data = $this->redis->get_redis($key);
            
            if (!$data) {
                continue;
            }
            
            $progress = json_decode($data, true);
            
            if (isset($progress['saved_at']) && $progress['saved_at'] < $cutoff) {
                $this->redis->del_redis($key);
            }
        }
    }
    
    /**
     * 获取采集统计信息
     */
    public function getCollectionStats($source = null, $timeRange = 86400) {
        $pattern = $this->keyPrefix . 'novel:*';
        $keys = $this->redis->keys($pattern);
        $cutoff = time() - $timeRange;
        
        $stats = [
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'in_progress' => 0,
            'total_chapters' => 0,
            'sources' => []
        ];
        
        foreach ($keys as $key) {
            $data = $this->redis->get_redis($key);
            
            if (!$data) {
                continue;
            }
            
            $progress = json_decode($data, true);
            
            // 时间范围过滤
            if (isset($progress['updated_at']) && $progress['updated_at'] < $cutoff) {
                continue;
            }
            
            // 源过滤
            if ($source && isset($progress['source']) && $progress['source'] !== $source) {
                continue;
            }
            
            $stats['total']++;
            
            $status = $progress['status'] ?? 'unknown';
            
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
            
            if (isset($progress['chapters_collected'])) {
                $stats['total_chapters'] += $progress['chapters_collected'];
            }
            
            // 按源统计
            $novelSource = $progress['source'] ?? 'unknown';
            if (!isset($stats['sources'][$novelSource])) {
                $stats['sources'][$novelSource] = [
                    'total' => 0,
                    'completed' => 0,
                    'failed' => 0
                ];
            }
            
            $stats['sources'][$novelSource]['total']++;
            
            if ($status === 'completed') {
                $stats['sources'][$novelSource]['completed']++;
            } elseif ($status === 'failed') {
                $stats['sources'][$novelSource]['failed']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * 标记小说开始采集
     */
    public function markNovelStarted($storeId, $source, $title) {
        $this->updateNovelProgress($storeId, [
            'status' => 'started',
            'source' => $source,
            'title' => $title,
            'started_at' => time()
        ]);
    }
    
    /**
     * 标记小说采集完成
     */
    public function markNovelCompleted($storeId, $chaptersCount) {
        $this->updateNovelProgress($storeId, [
            'status' => 'completed',
            'chapters_collected' => $chaptersCount,
            'completed_at' => time()
        ]);
    }
    
    /**
     * 标记小说采集失败
     */
    public function markNovelFailed($storeId, $error) {
        $this->updateNovelProgress($storeId, [
            'status' => 'failed',
            'error' => $error,
            'failed_at' => time()
        ]);
    }
    
    /**
     * 保存进度快照
     */
    public function saveProgress($mainTaskId) {
        $this->saveMainTaskProgress($mainTaskId);
        
        // 可以在这里添加其他进度保存逻辑
        // 比如保存到文件、数据库等
    }
}
?>