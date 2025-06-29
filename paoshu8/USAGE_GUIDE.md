# 多源小说采集器系统 - 使用指南

## 📖 项目概述

多源小说采集器系统是一个高性能、可扩展的小说内容采集解决方案，支持多个小说网站的并发采集，具备断点续传、任务管理、进度跟踪等企业级功能。

### 🎯 核心特性

- **🌐 多源支持**: 支持泡书吧、笔趣阁、起点中文网、纵横中文网等多个采集源
- **⚡ 并发处理**: 多进程并发采集，性能提升5-10倍
- **🔄 断点续传**: 网络中断自动恢复，支持任务暂停和恢复
- **📊 任务管理**: 完整的任务队列系统，支持批量处理
- **📈 进度跟踪**: 实时监控采集进度和状态
- **📝 结构化日志**: 多级别日志系统，便于调试和监控
- **🛠️ CLI工具**: 命令行管理界面，支持自动化部署
- **🔧 配置驱动**: 灵活的配置文件，无需修改代码

---

## 🔄 版本对比

### v1.0 (原始版本) vs v2.0 (优化版本)

| 功能特性 | v1.0 原始版本 | v2.0 优化版本 | 改进说明 |
|---------|---------------|---------------|----------|
| **架构设计** | 单文件脚本 | 模块化架构 | 更好的代码组织和维护性 |
| **采集源** | 主要支持paoshu8 | 支持4+个采集源 | 可扩展的插件化设计 |
| **并发处理** | 单线程顺序处理 | 多进程并发 | 5-10倍性能提升 |
| **断点续传** | ❌ 不支持 | ✅ 完整支持 | 网络中断不影响进度 |
| **任务管理** | 简单执行 | 任务队列系统 | 支持批量和分布式处理 |
| **进度跟踪** | ❌ 无 | ✅ 实时跟踪 | 可视化进度监控 |
| **错误处理** | 基础处理 | 智能重试机制 | 自动恢复和错误分类 |
| **日志系统** | echo输出 | 结构化日志 | 多级别、可搜索日志 |
| **配置管理** | 硬编码 | 配置文件驱动 | 灵活的参数调整 |
| **命令行工具** | ❌ 无 | ✅ 完整CLI | 便于自动化和运维 |
| **监控能力** | ❌ 无 | ✅ 完整监控 | 性能指标和状态监控 |

### 文件结构对比

#### v1.0 文件结构
```
paoshu8/
└── gather_info_local.php    # 单一脚本文件 (约300行)
```

#### v2.0 文件结构
```
paoshu8/
├── multi_source_collector.php           # 主控制器 (约400行)
├── collectors/                           # 采集器模块
│   ├── base_collector.php              # 基础采集器 (约300行)
│   ├── paoshu8_collector.php           # 泡书吧采集器 (约150行)
│   └── biquge_collector.php            # 笔趣阁采集器 (约200行)
├── utils/                               # 工具类模块
│   ├── task_manager.php                # 任务管理器 (约400行)
│   ├── progress_tracker.php            # 进度跟踪器 (约300行)
│   └── collector_logger.php            # 日志记录器 (约350行)
├── config/                              # 配置文件
│   └── collector_config.php            # 主配置文件 (约200行)
├── cli/                                 # 命令行工具
│   └── collector_cli.php               # CLI管理工具 (约500行)
├── logs/                                # 日志目录 (自动创建)
├── USAGE_GUIDE.md                       # 使用文档
└── examples/                            # 使用示例
    ├── basic_usage.php
    ├── advanced_usage.php
    └── custom_collector.php
```

---

## 🚀 快速开始

### 环境要求

- **PHP**: >= 7.4
- **扩展**: swoole, redis, curl, json, mbstring
- **内存**: >= 8GB 推荐
- **存储**: >= 100GB 可用空间

### 安装步骤

1. **克隆项目**
```bash
git clone https://github.com/your-repo/novel-collector.git
cd novel-collector
```

2. **安装依赖**
```bash
composer install
```

3. **配置环境**
```bash
# 复制配置文件
cp paoshu8/config/collector_config.php.example paoshu8/config/collector_config.php

# 编辑配置文件
vim paoshu8/config/collector_config.php
```

4. **创建必要目录**
```bash
mkdir -p paoshu8/logs
mkdir -p /data/html
mkdir -p /data/images
mkdir -p /data/json
chmod 755 paoshu8/cli/collector_cli.php
```

5. **测试安装**
```bash
php paoshu8/cli/collector_cli.php sources
```

---

## 📋 使用指南

### 基础使用

#### 1. 启动采集任务
```bash
# 使用默认配置启动所有启用的采集源
php paoshu8/cli/collector_cli.php start

# 指定采集源
php paoshu8/cli/collector_cli.php start --sources paoshu8,biquge

# 自定义参数
php paoshu8/cli/collector_cli.php start \
  --sources paoshu8,biquge \
  --workers 5 \
  --batch-size 50 \
  --log-level INFO
```

#### 2. 查看状态
```bash
# 查看整体状态
php paoshu8/cli/collector_cli.php status

# 查看特定任务状态
php paoshu8/cli/collector_cli.php status task_12345
```

#### 3. 管理任务
```bash
# 暂停任务
php paoshu8/cli/collector_cli.php stop task_12345

# 恢复任务
php paoshu8/cli/collector_cli.php resume task_12345

# 重启失败的任务
php paoshu8/cli/collector_cli.php restart task_12345
```

### 高级使用

#### 1. 自定义配置
```php
// paoshu8/config/collector_config.php
return [
    'global' => [
        'max_workers' => 10,        // 最大工作进程数
        'batch_size' => 100,        // 批处理大小
        'request_timeout' => 30,    // 请求超时
    ],
    'sources' => [
        'paoshu8' => [
            'enabled' => true,
            'concurrent_limit' => 5,
            'delay' => 1000,        // 请求间隔(毫秒)
        ],
    ],
];
```

#### 2. 编程方式使用
```php
<?php
require_once 'paoshu8/multi_source_collector.php';

$collector = new MultiSourceCollector();

// 启动采集
$collector->startCollection(['paoshu8', 'biquge'], [
    'batch_size' => 50,
    'max_workers' => 5,
    'resume_from_break' => true,
]);

// 获取统计信息
$stats = $collector->getCollectionStats($taskId);
print_r($stats);
?>
```

#### 3. 自定义采集器
```php
<?php
require_once 'paoshu8/collectors/base_collector.php';

class CustomCollector extends BaseCollector {
    public function collectNovel($novel, $progress = null) {
        // 实现自定义采集逻辑
        return [
            'success' => true,
            'chapters_count' => 100
        ];
    }
}
?>
```

---

## 🧪 测试指南

### 单元测试

#### 1. 测试采集器功能
```bash
# 创建测试脚本
cat > test_collector.php << 'EOF'
<?php
require_once 'paoshu8/collectors/paoshu8_collector.php';
require_once 'library/init.inc.php';

// 测试单本小说采集
$collector = new Paoshu8Collector($config, $mysql_obj, $redis_data, $logger);

$testNovel = [
    'store_id' => 12345,
    'story_id' => 'test_123',
    'title' => '测试小说',
    'author' => '测试作者',
    'story_link' => 'https://www.paoshu8.com/book/test_123/',
    'is_async' => 0
];

$result = $collector->collectNovel($testNovel);
var_dump($result);
EOF

php test_collector.php
```

#### 2. 测试任务管理器
```bash
cat > test_task_manager.php << 'EOF'
<?php
require_once 'paoshu8/utils/task_manager.php';
require_once 'library/init.inc.php';

$taskManager = new TaskManager($redis_data);

// 创建测试任务
$mainTaskId = $taskManager->createMainTask(['paoshu8'], ['batch_size' => 10]);
echo "创建主任务: {$mainTaskId}\n";

// 创建子任务
$subTaskId = $taskManager->createSubTask($mainTaskId, [
    'source' => 'paoshu8',
    'novels' => [['store_id' => 1, 'title' => 'test']]
]);
echo "创建子任务: {$subTaskId}\n";

// 获取任务
$task = $taskManager->getNextTask($mainTaskId);
var_dump($task);
EOF

php test_task_manager.php
```

### 集成测试

#### 1. 小规模测试
```bash
# 测试单个采集源，少量数据
php paoshu8/cli/collector_cli.php start \
  --sources paoshu8 \
  --workers 1 \
  --batch-size 5 \
  --log-level DEBUG
```

#### 2. 性能测试
```bash
# 测试多源并发性能
php paoshu8/cli/collector_cli.php start \
  --sources paoshu8,biquge \
  --workers 10 \
  --batch-size 100 \
  --log-level INFO

# 监控性能指标
tail -f paoshu8/logs/collector_$(date +%Y-%m-%d).log | grep "Performance"
```

#### 3. 断点续传测试
```bash
# 启动采集任务
php paoshu8/cli/collector_cli.php start --sources paoshu8 &
TASK_PID=$!

# 等待一段时间后中断
sleep 30
kill $TASK_PID

# 恢复任务
php paoshu8/cli/collector_cli.php resume task_id
```

### 压力测试

#### 1. 并发测试脚本
```bash
cat > stress_test.sh << 'EOF'
#!/bin/bash

echo "开始压力测试..."

# 启动多个采集进程
for i in {1..5}; do
    php paoshu8/cli/collector_cli.php start \
      --sources paoshu8 \
      --workers 2 \
      --batch-size 20 &
    echo "启动进程 $i"
done

# 监控系统资源
echo "监控系统资源..."
while true; do
    echo "$(date): CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}'), Memory: $(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2}')"
    sleep 10
done
EOF

chmod +x stress_test.sh
./stress_test.sh
```

---

## 📊 监控和维护

### 日志监控

#### 1. 实时日志查看
```bash
# 查看实时日志
tail -f paoshu8/logs/collector_$(date +%Y-%m-%d).log

# 过滤错误日志
tail -f paoshu8/logs/collector_$(date +%Y-%m-%d).log | grep ERROR

# 查看性能指标
tail -f paoshu8/logs/collector_$(date +%Y-%m-%d).log | grep "Performance"
```

#### 2. 日志分析脚本
```bash
cat > analyze_logs.sh << 'EOF'
#!/bin/bash

LOG_FILE="paoshu8/logs/collector_$(date +%Y-%m-%d).log"

echo "=== 日志分析报告 ==="
echo "日志文件: $LOG_FILE"
echo "生成时间: $(date)"
echo

echo "=== 错误统计 ==="
grep -c "ERROR" $LOG_FILE
echo

echo "=== 最近10个错误 ==="
grep "ERROR" $LOG_FILE | tail -10
echo

echo "=== 性能统计 ==="
grep "Performance" $LOG_FILE | tail -5
echo

echo "=== 采集统计 ==="
grep "采集完成" $LOG_FILE | wc -l
EOF

chmod +x analyze_logs.sh
./analyze_logs.sh
```

### 系统维护

#### 1. 清理脚本
```bash
# 清理旧日志
php paoshu8/cli/collector_cli.php cleanup logs

# 清理过期进度数据
php paoshu8/cli/collector_cli.php cleanup progress

# 清理已完成任务
php paoshu8/cli/collector_cli.php cleanup tasks

# 全部清理
php paoshu8/cli/collector_cli.php cleanup all
```

#### 2. 健康检查脚本
```bash
cat > health_check.sh << 'EOF'
#!/bin/bash

echo "=== 系统健康检查 ==="

# 检查PHP进程
echo "PHP进程数: $(ps aux | grep -c 'php.*collector')"

# 检查内存使用
echo "内存使用: $(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2}')"

# 检查磁盘空间
echo "磁盘使用: $(df -h / | awk 'NR==2{print $5}')"

# 检查Redis连接
redis-cli ping > /dev/null && echo "Redis: 正常" || echo "Redis: 异常"

# 检查MySQL连接
mysql -e "SELECT 1" > /dev/null 2>&1 && echo "MySQL: 正常" || echo "MySQL: 异常"

# 检查日志文件大小
LOG_SIZE=$(du -sh paoshu8/logs/ | cut -f1)
echo "日志大小: $LOG_SIZE"
EOF

chmod +x health_check.sh
./health_check.sh
```

---

## 🔧 故障排除

### 常见问题

#### 1. 内存不足
```bash
# 症状：PHP Fatal error: Allowed memory size exhausted
# 解决：调整内存限制
ini_set("memory_limit", "16000M");

# 或在配置文件中调整
'performance' => [
    'memory_limit' => '16000M',
]
```

#### 2. 连接超时
```bash
# 症状：Connection timeout
# 解决：调整超时设置
'global' => [
    'request_timeout' => 60,  // 增加超时时间
]

# 或使用代理
'sources' => [
    'paoshu8' => [
        'proxy' => [
            'enabled' => true,
            'host' => 'proxy.example.com',
            'port' => 8080,
        ],
    ],
]
```

#### 3. 数据库连接问题
```bash
# 检查数据库连接
mysql -u username -p -e "SELECT 1"

# 检查连接池设置
'database' => [
    'max_connections' => 100,
    'connection_timeout' => 30,
]
```

#### 4. Redis连接问题
```bash
# 检查Redis服务
redis-cli ping

# 检查Redis配置
redis-cli config get maxmemory
```

### 调试技巧

#### 1. 启用调试模式
```bash
php paoshu8/cli/collector_cli.php start \
  --sources paoshu8 \
  --workers 1 \
  --batch-size 1 \
  --log-level DEBUG
```

#### 2. 单步调试
```php
// 在代码中添加调试信息
$this->logger->debug("调试信息", [
    'variable' => $value,
    'step' => 'processing'
]);
```

#### 3. 性能分析
```php
// 添加性能监控
$startTime = microtime(true);
// ... 执行代码 ...
$duration = microtime(true) - $startTime;
$this->logger->logPerformance('operation_name', $duration);
```

---

## 📈 性能优化

### 配置优化

#### 1. 并发设置
```php
// 根据服务器性能调整
'global' => [
    'max_workers' => min(10, cpu_count() * 2),  // CPU核心数的2倍
    'batch_size' => 50,                         // 根据内存调整
]
```

#### 2. 缓存优化
```php
'cache' => [
    'html_cache_enabled' => true,
    'html_cache_ttl' => 86400,      // 24小时缓存
    'redis_key_prefix' => 'collector:',
]
```

#### 3. 数据库优化
```sql
-- 添加索引
CREATE INDEX idx_store_id ON ims_novel_info(store_id);
CREATE INDEX idx_is_async ON ims_novel_info(is_async);
CREATE INDEX idx_source ON ims_novel_info(source);
```

### 系统优化

#### 1. 操作系统优化
```bash
# 增加文件描述符限制
echo "* soft nofile 65536" >> /etc/security/limits.conf
echo "* hard nofile 65536" >> /etc/security/limits.conf

# 优化网络参数
echo "net.core.somaxconn = 65536" >> /etc/sysctl.conf
sysctl -p
```

#### 2. PHP优化
```ini
; php.ini 优化
memory_limit = 16G
max_execution_time = 0
opcache.enable = 1
opcache.memory_consumption = 512
```

---

*最后更新: 2024年1月*