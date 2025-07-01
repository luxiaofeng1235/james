# 🏗️ NEOVEL Data 技术栈架构图

## 📊 完整技术栈架构图

```mermaid
graph TB
    subgraph "🌐 前端展示层 Frontend Presentation Layer"
        FE1[HTML5/CSS3] --> FE2[JavaScript ES6+]
        FE2 --> FE3[jQuery 3.x]
        FE3 --> FE4[AJAX文件上传]
        FE4 --> FE5[图片区域选择]
        FE5 --> FE6[响应式设计]
    end

    subgraph "🔧 Web服务层 Web Server Layer"
        WS1[Nginx 1.18+] --> WS2[PHP-FPM 7.4+]
        WS2 --> WS3[负载均衡]
        WS3 --> WS4[SSL/TLS]
        WS4 --> WS5[Gzip压缩]
    end

    subgraph "💻 PHP应用层 PHP Application Layer"
        PHP1[PHP 7.4+] --> PHP2[Composer 2.x]
        PHP2 --> PHP3[PSR-4 自动加载]
        PHP3 --> PHP4[MVC架构模式]
        PHP4 --> PHP5[依赖注入]
        PHP5 --> PHP6[中间件系统]
    end

    subgraph "🔍 网络爬虫层 Web Scraping Layer"
        SC1[Guzzle HTTP 7.0] --> SC2[cURL Multi]
        SC2 --> SC3[Swoole 协程]
        SC3 --> SC4[QueryList DOM解析]
        SC4 --> SC5[代理IP轮换]
        SC5 --> SC6[反爬虫策略]
    end

    subgraph "📚 业务逻辑层 Business Logic Layer"
        BL1[小说采集服务] --> BL2[多源数据整合]
        BL2 --> BL3[内容过滤清洗]
        BL3 --> BL4[章节解析处理]
        BL4 --> BL5[图片下载处理]
        BL5 --> BL6[数据格式转换]
    end

    subgraph "🗄️ 数据存储层 Data Storage Layer"
        DS1[MySQL 8.0] --> DS2[主从复制]
        DS2 --> DS3[分库分表]
        DS3 --> DS4[连接池管理]
        DS4 --> DS5[事务管理]
    end

    subgraph "⚡ 缓存层 Cache Layer"
        CA1[Redis 6.x] --> CA2[内存缓存]
        CA2 --> CA3[会话存储]
        CA3 --> CA4[队列缓存]
        CA4 --> CA5[分布式锁]
    end

    subgraph "🔍 搜索引擎层 Search Engine Layer"
        SE1[Elasticsearch 7.11] --> SE2[全文搜索]
        SE2 --> SE3[XunSearch 中文搜索]
        SE3 --> SE4[GoFound 自建搜索]
        SE4 --> SE5[搜索建议]
        SE5 --> SE6[相关性排序]
    end

    subgraph "📁 文件存储层 File Storage Layer"
        FS1[本地文件系统] --> FS2[七牛云存储]
        FS2 --> FS3[AWS S3]
        FS3 --> FS4[CDN加速]
        FS4 --> FS5[图片压缩]
    end

    subgraph "📱 第三方服务层 Third-party Services"
        TS1[腾讯云SMS] --> TS2[微信支付]
        TS2 --> TS3[支付宝支付]
        TS3 --> TS4[邮件服务]
        TS4 --> TS5[短信验证]
    end

    subgraph "📊 监控日志层 Monitoring & Logging"
        ML1[Monolog日志] --> ML2[错误监控]
        ML2 --> ML3[性能监控]
        ML3 --> ML4[业务监控]
        ML4 --> ML5[告警系统]
    end

    FE6 --> WS1
    WS5 --> PHP1
    PHP6 --> SC1
    SC6 --> BL1
    BL6 --> DS1
    DS5 --> CA1
    CA5 --> SE1
    SE6 --> FS1
    FS5 --> TS1
    TS5 --> ML1
```

## 🔧 核心技术组件详解

### 1. PHP核心框架栈
```mermaid
graph LR
    subgraph "PHP Core Stack"
        A[PHP 7.4+] --> B[Composer]
        B --> C[PSR Standards]
        C --> D[Autoloading]
        D --> E[Dependency Injection]
        
        F[Think Template] --> G[Think View]
        G --> H[Template Engine]
        
        I[Environment Config] --> J[Multi-Environment]
        J --> K[Configuration Management]
    end
```

### 2. 数据采集技术栈
```mermaid
graph TB
    subgraph "Web Scraping Technology Stack"
        A1[HTTP Clients] --> B1[Guzzle HTTP 7.0]
        A1 --> B2[cURL Multi]
        A1 --> B3[Yurun HTTP]
        
        C1[DOM Parsing] --> D1[QueryList 4.2]
        C1 --> D2[phpQuery]
        C1 --> D3[Simple HTML DOM]
        
        E1[Async Processing] --> F1[Swoole Coroutine]
        E1 --> F2[ReactPHP Event Loop]
        E1 --> F3[Multi-Process]
        
        G1[Anti-Detection] --> H1[Proxy Rotation]
        G1 --> H2[User-Agent Rotation]
        G1 --> H3[Request Throttling]
    end
```

### 3. 数据处理技术栈
```mermaid
graph LR
    subgraph "Data Processing Stack"
        A[Raw Data] --> B[Content Filter]
        B --> C[Text Processing]
        C --> D[Format Conversion]
        
        E[Chinese Processing] --> F[Pinyin Conversion]
        F --> G[Traditional/Simplified]
        G --> H[Word Segmentation]
        
        I[Image Processing] --> J[Download & Cache]
        J --> K[Format Conversion]
        K --> L[Compression]
    end
```

### 4. 搜索技术栈
```mermaid
graph TB
    subgraph "Search Technology Stack"
        A[Search Engines] --> B[Elasticsearch 7.11]
        A --> C[XunSearch]
        A --> D[GoFound]
        
        B --> E[Full-text Search]
        C --> F[Chinese Tokenization]
        D --> G[Custom Search Logic]
        
        H[Search Features] --> I[Auto-complete]
        H --> J[Fuzzy Search]
        H --> K[Relevance Ranking]
        H --> L[Search Analytics]
    end
```

### 5. 存储技术栈
```mermaid
graph LR
    subgraph "Storage Technology Stack"
        A[Relational DB] --> B[MySQL 8.0]
        B --> C[Master-Slave]
        C --> D[Connection Pool]
        
        E[Cache] --> F[Redis 6.x]
        F --> G[Memory Cache]
        G --> H[Session Store]
        
        I[File Storage] --> J[Local FS]
        I --> K[Qiniu OSS]
        I --> L[AWS S3]
        
        M[Configuration] --> N[JSON Files]
        M --> O[PHP Arrays]
        M --> P[Environment Variables]
    end
```

## 🏗️ 系统架构分层图

```mermaid
graph TB
    subgraph "表现层 Presentation Layer"
        P1[Web界面] --> P2[API接口]
        P2 --> P3[移动端适配]
    end
    
    subgraph "业务层 Business Layer"
        B1[采集管理] --> B2[内容处理]
        B2 --> B3[搜索服务]
        B3 --> B4[用户管理]
    end
    
    subgraph "服务层 Service Layer"
        S1[小说服务] --> S2[章节服务]
        S2 --> S3[搜索服务]
        S3 --> S4[文件服务]
    end
    
    subgraph "数据访问层 Data Access Layer"
        D1[MySQL DAO] --> D2[Redis DAO]
        D2 --> D3[File DAO]
        D3 --> D4[Search DAO]
    end
    
    subgraph "基础设施层 Infrastructure Layer"
        I1[数据库] --> I2[缓存]
        I2 --> I3[文件系统]
        I3 --> I4[搜索引擎]
    end
    
    P3 --> B1
    B4 --> S1
    S4 --> D1
    D4 --> I1
```

## 🔄 数据流向图

```mermaid
sequenceDiagram
    participant U as 用户/调度器
    participant W as Web服务器
    participant A as PHP应用
    participant C as 采集器
    participant P as 数据处理
    participant D as 数据库
    participant R as Redis
    participant S as 搜索引擎
    participant F as 文件存储

    U->>W: HTTP请求
    W->>A: 转发请求
    A->>C: 启动采集任务
    C->>C: 多源并发采集
    C->>P: 原始数据
    P->>P: 清洗过滤
    P->>D: 存储结构化数据
    P->>R: 缓存热点数据
    P->>S: 建立搜索索引
    P->>F: 存储文件资源
    A->>W: 返回结果
    W->>U: HTTP响应
```

## 📦 依赖关系图

```mermaid
graph TB
    subgraph "核心依赖 Core Dependencies"
        CD1[PHP 7.4+] --> CD2[Composer]
        CD2 --> CD3[PSR Standards]
    end
    
    subgraph "框架依赖 Framework Dependencies"
        FD1[ThinkPHP Components] --> FD2[Template Engine]
        FD2 --> FD3[View System]
    end
    
    subgraph "网络依赖 Network Dependencies"
        ND1[Guzzle HTTP] --> ND2[cURL Multi]
        ND2 --> ND3[QueryList]
        ND3 --> ND4[Swoole]
    end
    
    subgraph "数据依赖 Data Dependencies"
        DD1[MySQL PDO] --> DD2[Redis]
        DD2 --> DD3[Elasticsearch]
        DD3 --> DD4[XunSearch]
    end
    
    subgraph "工具依赖 Utility Dependencies"
        UD1[Monolog] --> UD2[Pinyin]
        UD2 --> UD3[Hanzi Convert]
        UD3 --> UD4[mPDF]
    end
    
    subgraph "云服务依赖 Cloud Dependencies"
        CLD1[Qiniu SDK] --> CLD2[AWS SDK]
        CLD2 --> CLD3[Tencent Cloud]
    end
    
    CD3 --> FD1
    FD3 --> ND1
    ND4 --> DD1
    DD4 --> UD1
    UD4 --> CLD1
```

## 🚀 部署架构图

```mermaid
graph TB
    subgraph "负载均衡层 Load Balancer"
        LB[Nginx Load Balancer]
    end
    
    subgraph "Web服务器集群 Web Server Cluster"
        WS1[Nginx + PHP-FPM 1]
        WS2[Nginx + PHP-FPM 2]
        WS3[Nginx + PHP-FPM 3]
    end
    
    subgraph "应用服务器集群 Application Server Cluster"
        AS1[PHP Application 1]
        AS2[PHP Application 2]
        AS3[PHP Application 3]
    end
    
    subgraph "数据库集群 Database Cluster"
        DB1[MySQL Master]
        DB2[MySQL Slave 1]
        DB3[MySQL Slave 2]
    end
    
    subgraph "缓存集群 Cache Cluster"
        RC1[Redis Master]
        RC2[Redis Slave]
        RC3[Redis Sentinel]
    end
    
    subgraph "搜索集群 Search Cluster"
        ES1[Elasticsearch Node 1]
        ES2[Elasticsearch Node 2]
        ES3[Elasticsearch Node 3]
    end
    
    subgraph "存储服务 Storage Services"
        ST1[Local File System]
        ST2[Qiniu Object Storage]
        ST3[AWS S3]
        ST4[CDN]
    end
    
    subgraph "监控服务 Monitoring Services"
        MN1[Application Monitoring]
        MN2[System Monitoring]
        MN3[Log Aggregation]
        MN4[Alert System]
    end
    
    LB --> WS1
    LB --> WS2
    LB --> WS3
    
    WS1 --> AS1
    WS2 --> AS2
    WS3 --> AS3
    
    AS1 --> DB1
    AS2 --> DB2
    AS3 --> DB3
    
    AS1 --> RC1
    AS2 --> RC1
    AS3 --> RC1
    
    AS1 --> ES1
    AS2 --> ES2
    AS3 --> ES3
    
    AS1 --> ST1
    AS2 --> ST2
    AS3 --> ST3
    
    AS1 --> MN1
    AS2 --> MN2
    AS3 --> MN3
```

这个技术栈架构图全面展示了NEOVEL Data小说采集系统的完整技术架构，包括前端、后端、数据库、缓存、搜索、存储等各个层面的技术选型和架构设计。