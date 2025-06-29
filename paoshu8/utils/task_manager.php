<?php
/**
 * 任务管理器
 * 负责任务的创建、分发、状态管理
 */

class TaskManager {
    
    private $redis;
    private $keyPrefix = 'collector:task:';
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    /**
     * 创建主任务
     */
    public function createMainTask($sources, $options) {
        $taskId = $this->generateTaskId();
        
        $taskData = [
            'id' => $taskId,
            'type' => 'main',
            'sources' => $sources,
            'options' => $options,
            'status' => 'pending',
            'created_at' => time(),
            'sub_tasks' => [],
            'stats' => [
                'total_novels' => 0,
                'completed_novels' => 0,
                'failed_novels' => 0,
                'total_chapters' => 0
            ]
        ];
        
        $this->redis->set_redis($this->keyPrefix . 'main:' . $taskId, json_encode($taskData));
        
        return $taskId;
    }
    
    /**
     * 创建子任务
     */
    public function createSubTask($mainTaskId, $taskData) {
        $subTaskId = $this->generateTaskId();
        
        $subTask = [
            'id' => $subTaskId,
            'type' => 'sub',
            'main_task_id' => $mainTaskId,
            'data' => json_encode($taskData),
            'status' => 'pending',
            'created_at' => time(),
            'started_at' => null,
            'completed_at' => null,
            'worker_id' => null,
            'error' => null
        ];
        
        // 保存子任务
        $this->redis->set_redis($this->keyPrefix . 'sub:' . $subTaskId, json_encode($subTask));
        
        // 添加到任务队列
        $this->redis->lpush_redis($this->keyPrefix . 'queue:' . $mainTaskId, $subTaskId);
        
        // 更新主任务的子任务列表
        $this->addSubTaskToMain($mainTaskId, $subTaskId);
        
        return $subTaskId;
    }
    
    /**
     * 获取下一个待处理任务
     */
    public function getNextTask($mainTaskId) {
        $subTaskId = $this->redis->rpop_redis($this->keyPrefix . 'queue:' . $mainTaskId);
        
        if (!$subTaskId) {
            return null;
        }
        
        $taskData = $this->redis->get_redis($this->keyPrefix . 'sub:' . $subTaskId);
        
        if (!$taskData) {
            return null;
        }
        
        $task = json_decode($taskData, true);
        
        // 标记任务为进行中
        $task['status'] = 'running';
        $task['started_at'] = time();
        
        $this->redis->set_redis($this->keyPrefix . 'sub:' . $subTaskId, json_encode($task));
        
        return $task;
    }
    
    /**
     * 完成任务
     */
    public function completeTask($taskId) {
        $taskData = $this->redis->get_redis($this->keyPrefix . 'sub:' . $taskId);
        
        if (!$taskData) {
            return false;
        }
        
        $task = json_decode($taskData, true);
        $task['status'] = 'completed';
        $task['completed_at'] = time();
        
        $this->redis->set_redis($this->keyPrefix . 'sub:' . $taskId, json_encode($task));
        
        // 更新主任务统计
        $this->updateMainTaskStats($task['main_task_id'], 'completed');
        
        return true;
    }
    
    /**
     * 标记任务失败
     */
    public function failTask($taskId, $error) {
        $taskData = $this->redis->get_redis($this->keyPrefix . 'sub:' . $taskId);
        
        if (!$taskData) {
            return false;
        }
        
        $task = json_decode($taskData, true);
        $task['status'] = 'failed';
        $task['completed_at'] = time();
        $task['error'] = $error;
        
        $this->redis->set_redis($this->keyPrefix . 'sub:' . $taskId, json_encode($task));
        
        // 更新主任务统计
        $this->updateMainTaskStats($task['main_task_id'], 'failed');
        
        return true;
    }
    
    /**
     * 完成主任务
     */
    public function completeMainTask($mainTaskId) {
        $taskData = $this->redis->get_redis($this->keyPrefix . 'main:' . $mainTaskId);
        
        if (!$taskData) {
            return false;
        }
        
        $task = json_decode($taskData, true);
        $task['status'] = 'completed';
        $task['completed_at'] = time();
        
        $this->redis->set_redis($this->keyPrefix . 'main:' . $mainTaskId, json_encode($task));
        
        return true;
    }
    
    /**
     * 获取主任务信息
     */
    public function getMainTask($mainTaskId) {
        $taskData = $this->redis->get_redis($this->keyPrefix . 'main:' . $mainTaskId);
        
        if (!$taskData) {
            return null;
        }
        
        return json_decode($taskData, true);
    }
    
    /**
     * 获取子任务列表
     */
    public function getSubTasks($mainTaskId) {
        $mainTask = $this->getMainTask($mainTaskId);
        
        if (!$mainTask || !isset($mainTask['sub_tasks'])) {
            return [];
        }
        
        $subTasks = [];
        
        foreach ($mainTask['sub_tasks'] as $subTaskId) {
            $taskData = $this->redis->get_redis($this->keyPrefix . 'sub:' . $subTaskId);
            
            if ($taskData) {
                $subTasks[] = json_decode($taskData, true);
            }
        }
        
        return $subTasks;
    }
    
    /**
     * 获取任务统计信息
     */
    public function getTaskStats($mainTaskId) {
        $subTasks = $this->getSubTasks($mainTaskId);
        
        $stats = [
            'total' => count($subTasks),
            'pending' => 0,
            'running' => 0,
            'completed' => 0,
            'failed' => 0
        ];
        
        foreach ($subTasks as $task) {
            $stats[$task['status']]++;
        }
        
        return $stats;
    }
    
    /**
     * 清理已完成的任务
     */
    public function cleanupCompletedTasks($olderThan = 86400) {
        $cutoff = time() - $olderThan;
        
        // 获取所有主任务
        $pattern = $this->keyPrefix . 'main:*';
        $keys = $this->redis->keys($pattern);
        
        foreach ($keys as $key) {
            $taskData = $this->redis->get_redis($key);
            
            if (!$taskData) {
                continue;
            }
            
            $task = json_decode($taskData, true);
            
            if ($task['status'] === 'completed' && 
                isset($task['completed_at']) && 
                $task['completed_at'] < $cutoff) {
                
                // 清理子任务
                if (isset($task['sub_tasks'])) {
                    foreach ($task['sub_tasks'] as $subTaskId) {
                        $this->redis->del_redis($this->keyPrefix . 'sub:' . $subTaskId);
                    }
                }
                
                // 清理任务队列
                $this->redis->del_redis($this->keyPrefix . 'queue:' . $task['id']);
                
                // 清理主任务
                $this->redis->del_redis($key);
            }
        }
    }
    
    /**
     * 重启失败的任务
     */
    public function restartFailedTasks($mainTaskId) {
        $subTasks = $this->getSubTasks($mainTaskId);
        $restarted = 0;
        
        foreach ($subTasks as $task) {
            if ($task['status'] === 'failed') {
                // 重置任务状态
                $task['status'] = 'pending';
                $task['started_at'] = null;
                $task['completed_at'] = null;
                $task['worker_id'] = null;
                $task['error'] = null;
                
                $this->redis->set_redis($this->keyPrefix . 'sub:' . $task['id'], json_encode($task));
                
                // 重新加入队列
                $this->redis->lpush_redis($this->keyPrefix . 'queue:' . $mainTaskId, $task['id']);
                
                $restarted++;
            }
        }
        
        return $restarted;
    }
    
    /**
     * 生成任务ID
     */
    private function generateTaskId() {
        return uniqid('task_', true);
    }
    
    /**
     * 添加子任务到主任务
     */
    private function addSubTaskToMain($mainTaskId, $subTaskId) {
        $taskData = $this->redis->get_redis($this->keyPrefix . 'main:' . $mainTaskId);
        
        if (!$taskData) {
            return false;
        }
        
        $task = json_decode($taskData, true);
        $task['sub_tasks'][] = $subTaskId;
        
        $this->redis->set_redis($this->keyPrefix . 'main:' . $mainTaskId, json_encode($task));
        
        return true;
    }
    
    /**
     * 更新主任务统计
     */
    private function updateMainTaskStats($mainTaskId, $type) {
        $taskData = $this->redis->get_redis($this->keyPrefix . 'main:' . $mainTaskId);
        
        if (!$taskData) {
            return false;
        }
        
        $task = json_decode($taskData, true);
        
        if ($type === 'completed') {
            $task['stats']['completed_novels']++;
        } elseif ($type === 'failed') {
            $task['stats']['failed_novels']++;
        }
        
        $this->redis->set_redis($this->keyPrefix . 'main:' . $mainTaskId, json_encode($task));
        
        return true;
    }
}
?>