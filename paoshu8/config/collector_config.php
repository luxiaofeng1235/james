<?php
/**
 * 多源采集器配置文件
 */

return [
    // 全局配置
    'global' => [
        'max_workers' => 10,              // 最大工作进程数
        'batch_size' => 50,               // 每批处理的小说数量
        'request_timeout' => 30,          // 请求超时时间(秒)
        'max_retry_times' => 3,           // 最大重试次数
        'progress_save_interval' => 10,   // 进度保存间隔(秒)
        'log_level' => 'INFO',            // 日志级别
        'enable_resume' => true,          // 是否启用断点续传
    ],
    
    // 采集源配置
    'sources' => [
        'paoshu8' => [
            'name' => '泡书吧',
            'enabled' => true,
            'base_url' => 'https://www.paoshu8.com',
            'concurrent_limit' => 5,      // 并发限制
            'delay' => 1000,              // 请求间隔(毫秒)
            'rules_key' => 'paoshu8',     // 解析规则键名
            'encoding' => 'utf-8',        // 页面编码
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
            ],
            'proxy' => [
                'enabled' => false,
                'type' => 'http',         // http, socks5
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => '',
            ],
            'parse_rules' => [
                'title' => ['.book-title', 'text'],
                'author' => ['.book-author', 'text'],
                'cover_logo' => ['.book-cover img', 'src'],
                'intro' => ['.book-intro', 'text'],
                'status' => ['.book-status', 'text'],
                'cate_name' => ['.book-category', 'text'],
                'tag' => ['.book-tag', 'text'],
                'nearby_chapter' => ['.book-last-chapter a', 'text'],
                'third_update_time' => ['.book-update-time', 'text'],
                'text_num' => ['.book-words', 'text'],
            ],
            'chapter_rules' => [
                'list_selector' => '#list dd',
                'link_selector' => 'a',
                'name_attr' => 'text',
                'url_attr' => 'href',
            ],
        ],
        
        'biquge' => [
            'name' => '笔趣阁',
            'enabled' => true,
            'base_url' => 'https://www.biquge.com',
            'concurrent_limit' => 3,
            'delay' => 1500,
            'rules_key' => 'biquge',
            'encoding' => 'gbk',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            ],
            'proxy' => [
                'enabled' => false,
            ],
            'parse_rules' => [
                'title' => ['#info h1', 'text'],
                'author' => ['#info p:first', 'text'],
                'cover_logo' => ['#fmimg img', 'src'],
                'intro' => ['#intro', 'text'],
                'status' => ['#info p:nth-child(3)', 'text'],
                'cate_name' => ['.con_top a:nth-child(3)', 'text'],
                'nearby_chapter' => ['#info p:nth-child(4) a', 'text'],
                'third_update_time' => ['#info p:nth-child(4)', 'text'],
            ],
            'chapter_rules' => [
                'list_selector' => '#list dd',
                'link_selector' => 'a',
                'name_attr' => 'text',
                'url_attr' => 'href',
            ],
        ],
        
        'qidian' => [
            'name' => '起点中文网',
            'enabled' => false,          // 默认禁用，需要特殊处理
            'base_url' => 'https://www.qidian.com',
            'concurrent_limit' => 2,
            'delay' => 2000,
            'rules_key' => 'qidian',
            'encoding' => 'utf-8',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Referer' => 'https://www.qidian.com',
            ],
            'proxy' => [
                'enabled' => false,
            ],
            'parse_rules' => [
                'title' => ['.book-info h1 em', 'text'],
                'author' => ['.book-info .writer', 'text'],
                'cover_logo' => ['.book-img img', 'src'],
                'intro' => ['.book-intro p', 'text'],
                'status' => ['.book-info .tag', 'text'],
                'cate_name' => ['.crumb a:nth-child(3)', 'text'],
                'nearby_chapter' => ['.update a', 'text'],
                'third_update_time' => ['.update span', 'text'],
                'text_num' => ['.book-info .num span:first', 'text'],
            ],
        ],
        
        'zongheng' => [
            'name' => '纵横中文网',
            'enabled' => false,
            'base_url' => 'http://www.zongheng.com',
            'concurrent_limit' => 3,
            'delay' => 1500,
            'rules_key' => 'zongheng',
            'encoding' => 'utf-8',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
            'proxy' => [
                'enabled' => false,
            ],
            'parse_rules' => [
                'title' => ['.book-name', 'text'],
                'author' => ['.au-name a', 'text'],
                'cover_logo' => ['.book-img img', 'src'],
                'intro' => ['.book-dec p', 'text'],
                'status' => ['.book-label', 'text'],
                'cate_name' => ['.detail-list a:nth-child(2)', 'text'],
                'nearby_chapter' => ['.update-time a', 'text'],
                'third_update_time' => ['.update-time', 'text'],
            ],
        ],
    ],
    
    // 数据库配置
    'database' => [
        'novel_table' => 'ims_novel_info',
        'chapter_table' => 'ims_chapter',
        'book_table' => 'mc_book',
        'batch_insert_size' => 100,
    ],
    
    // 缓存配置
    'cache' => [
        'html_cache_enabled' => true,
        'html_cache_ttl' => 86400,        // HTML缓存时间(秒)
        'redis_key_prefix' => 'collector:',
        'progress_ttl' => 604800,         // 进度缓存时间(7天)
    ],
    
    // 文件存储配置
    'storage' => [
        'html_path' => '/data/html',
        'image_path' => '/data/images',
        'json_path' => '/data/json',
        'log_path' => '/data/logs',
    ],
    
    // 监控和报警配置
    'monitoring' => [
        'enabled' => true,
        'stats_interval' => 60,           // 统计间隔(秒)
        'alert_thresholds' => [
            'error_rate' => 0.1,          // 错误率阈值
            'memory_usage' => 0.8,        // 内存使用率阈值
            'queue_size' => 1000,         // 队列大小阈值
        ],
        'webhook_url' => '',              // 报警webhook地址
    ],
    
    // 清理配置
    'cleanup' => [
        'auto_cleanup' => true,
        'log_retention_days' => 30,       // 日志保留天数
        'cache_retention_days' => 7,      // 缓存保留天数
        'completed_task_retention_hours' => 24, // 已完成任务保留小时数
    ],
    
    // 性能优化配置
    'performance' => [
        'memory_limit' => '8000M',
        'max_execution_time' => 0,
        'opcache_enabled' => true,
        'gzip_compression' => true,
    ],
    
    // 安全配置
    'security' => [
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 3600,
        ],
        'ip_whitelist' => [],
        'user_agent_blacklist' => [
            'bot',
            'spider',
            'crawler',
        ],
    ],
];
?>