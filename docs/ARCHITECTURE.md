# NEOVEL Data - 小说采集系统技术架构

## 🏗️ 系统架构概览

```mermaid
graph TB
    subgraph "前端层 Frontend Layer"
        A[Web Interface] --> B[JavaScript/jQuery]
        B --> C[AJAX Upload]
        C --> D[Image Area Select]
    end

    subgraph "应用层 Application Layer"
        E[PHP 7.x+] --> F[Composer Autoloader]
        F --> G[MVC Architecture]
        G --> H[Service Layer]
        H --> I[Model Layer]
    end

    subgraph "业务模块 Business Modules"
        J[Novel Crawler] --> K[Multi-Source Collector]
        K --> L[Chapter Parser]
        L --> M[Content Filter]
        M --> N[Image Processor]
    end

    subgraph "数据层 Data Layer"
        O[MySQL] --> P[Redis Cache]
        P --> Q[File Storage]
        Q --> R[JSON Config]
    end

    subgraph "外部服务 External Services"
        S[Search Engines] --> T[Elasticsearch]
        T --> U[XunSearch]
        U --> V[GoFound]
    end

    subgraph "第三方集成 Third-party Integration"
        W[Cloud Services] --> X[Qiniu OSS]
        X --> Y[AWS SDK]
        Y --> Z[Tencent SMS]
    end

    A --> E
    E --> J
    J --> O
    O --> S
    S --> W
```

## 🔧 技术栈详细分析

### 核心技术栈

#### 1. 后端框架与语言
```yaml
语言: PHP 7.x+
架构模式: MVC + Service Layer
依赖管理: Composer
自动加载: PSR-4
```

#### 2. 数据存储
```yaml
关系数据库: MySQL
缓存系统: Redis
文件存储: 本地文件系统 + 云存储
配置存储: JSON/PHP配置文件
```

#### 3. 搜索引擎
```yaml
全文搜索: 
  - Elasticsearch 7.11+
  - XunSearch (中文搜索)
  - GoFound (自建搜索)
```

#### 4. 网络请求与爬虫
```yaml
HTTP客户端:
  - Guzzle HTTP 7.0+
  - cURL Multi
  - Swoole 协程
  - QueryList (DOM解析)
```

## 📊 系统架构图

```mermaid
graph LR
    subgraph "数据采集层 Data Collection"
        A1[泡书吧采集器] --> B1[数据清洗]
        A2[笔趣阁采集器] --> B1
        A3[起点采集器] --> B1
        A4[纵横采集器] --> B1
        B1 --> C1[内容过滤]
        C1 --> D1[格式转换]
    end

    subgraph "数据处理层 Data Processing"
        D1 --> E1[章节解析]
        E1 --> F1[内容加密/解密]
        F1 --> G1[图片处理]
        G1 --> H1[文本分词]
    end

    subgraph "存储层 Storage Layer"
        H1 --> I1[MySQL主库]
        H1 --> I2[MySQL从库]
        H1 --> I3[Redis缓存]
        H1 --> I4[文件存储]
    end

    subgraph "搜索层 Search Layer"
        I1 --> J1[Elasticsearch索引]
        I1 --> J2[XunSearch索引]
        I1 --> J3[GoFound索引]
    end

    subgraph "服务层 Service Layer"
        J1 --> K1[API接口]
        J2 --> K1
        J3 --> K1
        K1 --> L1[前端展示]
    end
```

## 🗄️ 数据库架构

```mermaid
erDiagram
    MC_BOOK ||--o{ MC_CHAPTER : contains
    MC_BOOK ||--o{ MC_BOOK_COMMENT : has
    MC_BOOK {
        int id PK
        string book_name
        string author
        string pic
        text desc
        string class_name
        string last_chapter_title
        datetime last_chapter_time
        int status
        datetime created_at
        datetime updated_at
    }
    
    MC_CHAPTER {
        int id PK
        int book_id FK
        string chapter_name
        text content
        int sort
        datetime created_at
    }
    
    MC_BOOK_COMMENT {
        int id PK
        int book_id FK
        string title
        int score
        int comment_count
        string category
        datetime addtime
    }
    
    IMS_BIQUGE_INFO {
        int store_id PK
        string title
        string author
        string story_link
        string source
        int is_async
        datetime createtime
        datetime updatetime
    }
```

## 🔄 数据流架构

```mermaid
sequenceDiagram
    participant C as 爬虫调度器
    participant S as 采集服务
    participant P as 数据处理
    participant D as 数据库
    participant R as Redis
    participant E as 搜索引擎

    C->>S: 启动采集任务
    S->>S: 多源并发采集
    S->>P: 原始数据
    P->>P: 数据清洗与过滤
    P->>D: 存储结构化数据
    P->>R: 缓存热点数据
    P->>E: 建立搜索索引
    E-->>C: 返回处理结果
```

## 🏭 部署架构

```mermaid
graph TB
    subgraph "负载均衡层"
        LB[Nginx Load Balancer]
    end
    
    subgraph "应用服务器集群"
        APP1[PHP-FPM Server 1]
        APP2[PHP-FPM Server 2]
        APP3[PHP-FPM Server 3]
    end
    
    subgraph "数据库集群"
        MASTER[MySQL Master]
        SLAVE1[MySQL Slave 1]
        SLAVE2[MySQL Slave 2]
    end
    
    subgraph "缓存集群"
        REDIS1[Redis Master]
        REDIS2[Redis Slave]
    end
    
    subgraph "搜索集群"
        ES1[Elasticsearch Node 1]
        ES2[Elasticsearch Node 2]
        ES3[Elasticsearch Node 3]
    end
    
    subgraph "存储服务"
        OSS[对象存储 OSS]
        NFS[网络文件系统]
    end
    
    LB --> APP1
    LB --> APP2
    LB --> APP3
    
    APP1 --> MASTER
    APP2 --> SLAVE1
    APP3 --> SLAVE2
    
    APP1 --> REDIS1
    APP2 --> REDIS1
    APP3 --> REDIS1
    
    REDIS1 --> REDIS2
    
    APP1 --> ES1
    APP2 --> ES2
    APP3 --> ES3
    
    APP1 --> OSS
    APP2 --> NFS
    APP3 --> OSS
```

## 📦 核心组件依赖

### Composer 依赖包
```json
{
  "核心框架": {
    "topthink/think-view": "模板引擎",
    "topthink/think-template": "模板系统"
  },
  "数据处理": {
    "elasticsearch/elasticsearch": "搜索引擎",
    "hightman/xunsearch": "中文全文搜索",
    "monolog/monolog": "日志系统"
  },
  "网络请求": {
    "guzzlehttp/guzzle": "HTTP客户端",
    "ares333/php-curl": "cURL封装",
    "yurunsoft/yurun-http": "HTTP工具",
    "jaeger/querylist": "DOM解析",
    "jaeger/curlmulti": "并发请求"
  },
  "文档处理": {
    "mpdf/mpdf": "PDF生成",
    "phpoffice/phpword": "Word文档处理"
  },
  "云服务": {
    "qiniu/php-sdk": "七牛云存储",
    "aws/aws-sdk-php": "AWS服务",
    "tencentcloud/sms": "腾讯云短信"
  },
  "工具库": {
    "overtrue/pinyin": "拼音转换",
    "sqhlib/hanzi-convert": "简繁转换",
    "react/event-loop": "事件循环"
  }
}
```

## 🔧 系统配置架构

```mermaid
graph LR
    subgraph "配置管理"
        A[环境配置] --> B[.env_prod]
        A --> C[.env_dev]
        B --> D[数据库配置]
        C --> D
        D --> E[Redis配置]
        E --> F[搜索引擎配置]
        F --> G[第三方服务配置]
    end
    
    subgraph "业务配置"
        H[小说分类配置] --> I[采集规则配置]
        I --> J[广告配置]
        J --> K[上传配置]
    end
    
    G --> H
```

## 🚀 性能优化架构

### 1. 缓存策略
```yaml
多级缓存:
  - L1: Redis 内存缓存 (热点数据)
  - L2: 文件缓存 (章节内容)
  - L3: 数据库查询缓存
  - L4: CDN 静态资源缓存
```

### 2. 并发处理
```yaml
并发策略:
  - Swoole 协程 (网络IO)
  - cURL Multi (HTTP并发)
  - 多进程采集 (CPU密集)
  - 队列异步处理 (任务调度)
```

### 3. 数据库优化
```yaml
优化策略:
  - 读写分离
  - 分库分表
  - 索引优化
  - 连接池管理
```

## 🔐 安全架构

```mermaid
graph TB
    subgraph "安全防护层"
        A[WAF防火墙] --> B[DDoS防护]
        B --> C[IP白名单]
        C --> D[访问频率限制]
    end
    
    subgraph "应用安全"
        E[输入验证] --> F[SQL注入防护]
        F --> G[XSS防护]
        G --> H[CSRF防护]
    end
    
    subgraph "数据安全"
        I[数据加密] --> J[传输加密]
        J --> K[存储加密]
        K --> L[备份加密]
    end
    
    D --> E
    E --> I
```

## 📈 监控架构

```yaml
监控体系:
  系统监控:
    - CPU/内存使用率
    - 磁盘IO
    - 网络流量
  
  应用监控:
    - 接口响应时间
    - 错误率统计
    - 并发用户数
  
  业务监控:
    - 采集成功率
    - 数据质量
    - 搜索性能
```

这个架构图展示了整个小说采集系统的完整技术栈，包括数据采集、处理、存储、搜索和展示的全流程架构设计。