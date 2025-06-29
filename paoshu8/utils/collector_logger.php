<?php
/**
 * 采集器日志记录器
 * 提供结构化的日志记录功能
 */

class CollectorLogger {
    
    private $logPath;
    private $logLevel;
    private $logLevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    public function __construct($logPath = null, $logLevel = 'INFO') {
        $this->logPath = $logPath ?: $this->getDefaultLogPath();
        $this->logLevel = $logLevel;
        
        // 确保日志目录存在
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * 记录调试信息
     */
    public function debug($message, $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * 记录一般信息
     */
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * 记录警告信息
     */
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * 记录错误信息
     */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * 记录严重错误
     */
    public function critical($message, $context = []) {
        $this->log('CRITICAL', $message, $context);
    }
    
    /**
     * 核心日志记录方法
     */
    private function log($level, $message, $context = []) {
        // 检查日志级别
        if ($this->logLevels[$level] < $this->logLevels[$this->logLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $pid = getmypid();
        $memory = $this->formatBytes(memory_get_usage(true));
        
        // 构建日志条目
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'pid' => $pid,
            'memory' => $memory,
            'message' => $message
        ];
        
        if (!empty($context)) {
            $logEntry['context'] = $context;
        }
        
        // 格式化日志消息
        $formattedMessage = $this->formatLogEntry($logEntry);
        
        // 写入日志文件
        $this->writeToFile($formattedMessage);
        
        // 同时输出到控制台（如果是CLI模式）
        if (is_cli()) {
            $this->writeToConsole($level, $message, $context);
        }
    }
    
    /**
     * 格式化日志条目
     */
    private function formatLogEntry($entry) {
        $formatted = sprintf(
            "[%s] [%s] [PID:%d] [MEM:%s] %s",
            $entry['timestamp'],
            $entry['level'],
            $entry['pid'],
            $entry['memory'],
            $entry['message']
        );
        
        if (isset($entry['context']) && !empty($entry['context'])) {
            $formatted .= ' ' . json_encode($entry['context'], JSON_UNESCAPED_UNICODE);
        }
        
        return $formatted . PHP_EOL;
    }
    
    /**
     * 写入日志文件
     */
    private function writeToFile($message) {
        try {
            file_put_contents($this->logPath, $message, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // 如果无法写入文件，至少输出到错误日志
            error_log("Failed to write to log file: " . $e->getMessage());
        }
    }
    
    /**
     * 输出到控制台
     */
    private function writeToConsole($level, $message, $context = []) {
        $colors = [
            'DEBUG' => "\033[36m",    // 青色
            'INFO' => "\033[32m",     // 绿色
            'WARNING' => "\033[33m",  // 黄色
            'ERROR' => "\033[31m",    // 红色
            'CRITICAL' => "\033[35m"  // 紫色
        ];
        
        $reset = "\033[0m";
        $color = $colors[$level] ?? '';
        
        $timestamp = date('H:i:s');
        $output = sprintf(
            "%s[%s] [%s]%s %s",
            $color,
            $timestamp,
            $level,
            $reset,
            $message
        );
        
        if (!empty($context)) {
            $output .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        echo $output . PHP_EOL;
    }
    
    /**
     * 获取默认日志路径
     */
    private function getDefaultLogPath() {
        $logDir = dirname(__DIR__) . '/logs';
        $logFile = 'collector_' . date('Y-m-d') . '.log';
        return $logDir . '/' . $logFile;
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
    
    /**
     * 记录性能指标
     */
    public function logPerformance($operation, $duration, $context = []) {
        $context['operation'] = $operation;
        $context['duration'] = round($duration, 4) . 's';
        
        $this->info("Performance metric", $context);
    }
    
    /**
     * 记录HTTP请求
     */
    public function logRequest($url, $method = 'GET', $responseCode = null, $duration = null) {
        $context = [
            'url' => $url,
            'method' => $method
        ];
        
        if ($responseCode !== null) {
            $context['response_code'] = $responseCode;
        }
        
        if ($duration !== null) {
            $context['duration'] = round($duration, 4) . 's';
        }
        
        $level = ($responseCode >= 400) ? 'ERROR' : 'DEBUG';
        $this->log($level, "HTTP Request", $context);
    }
    
    /**
     * 记录数据库操作
     */
    public function logDatabase($operation, $table, $duration = null, $affectedRows = null) {
        $context = [
            'operation' => $operation,
            'table' => $table
        ];
        
        if ($duration !== null) {
            $context['duration'] = round($duration, 4) . 's';
        }
        
        if ($affectedRows !== null) {
            $context['affected_rows'] = $affectedRows;
        }
        
        $this->debug("Database operation", $context);
    }
    
    /**
     * 记录采集统计
     */
    public function logCollectionStats($source, $stats) {
        $context = array_merge(['source' => $source], $stats);
        $this->info("Collection statistics", $context);
    }
    
    /**
     * 轮转日志文件
     */
    public function rotateLog($maxSize = 10485760) { // 10MB
        if (!file_exists($this->logPath)) {
            return;
        }
        
        if (filesize($this->logPath) > $maxSize) {
            $rotatedPath = $this->logPath . '.' . date('YmdHis');
            rename($this->logPath, $rotatedPath);
            
            // 可以选择压缩旧日志文件
            if (function_exists('gzencode')) {
                $content = file_get_contents($rotatedPath);
                file_put_contents($rotatedPath . '.gz', gzencode($content));
                unlink($rotatedPath);
            }
        }
    }
    
    /**
     * 清理旧日志文件
     */
    public function cleanupOldLogs($daysToKeep = 30) {
        $logDir = dirname($this->logPath);
        $cutoff = time() - ($daysToKeep * 86400);
        
        $files = glob($logDir . '/collector_*.log*');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
?>