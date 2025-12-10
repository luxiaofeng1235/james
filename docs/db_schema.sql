-- NEOVEL/采集器核心表结构（自建开发环境用）
-- 说明：
-- 1) 字段按现有代码推导，线上真实表可有差异；可根据业务再裁剪/加索引。
-- 2) 章节正文默认存本地 JSON/TXT（见 NovelModel::getBookFilePath），未包含 mc_chapter。
-- 3) 榜单/书评/采集中间表用于热搜、高分榜、评论抓取和多源对账，可按需启用。
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- 小说主表（mc_book）
CREATE TABLE IF NOT EXISTS mc_book (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  book_name           VARCHAR(255) NOT NULL COMMENT '书名',
  author              VARCHAR(255) DEFAULT NULL COMMENT '作者',
  pic                 VARCHAR(255) DEFAULT NULL COMMENT '封面地址',
  `desc`              TEXT COMMENT '简介',
  class_name          VARCHAR(128) DEFAULT NULL COMMENT '分类名称',
  tags                VARCHAR(255) DEFAULT NULL COMMENT '标签',
  serialize           TINYINT(1) DEFAULT 0 COMMENT '0=连载,1=完结',
  last_chapter_title  VARCHAR(255) DEFAULT NULL COMMENT '最新章节标题',
  last_chapter_time   INT UNSIGNED DEFAULT NULL COMMENT '最新章节时间戳',
  update_chapter_title VARCHAR(255) DEFAULT NULL COMMENT '更新章节标题',
  update_chapter_time INT UNSIGNED DEFAULT NULL COMMENT '更新时间戳',
  source_url          VARCHAR(255) DEFAULT NULL COMMENT '采集源URL',
  source              VARCHAR(64) DEFAULT NULL COMMENT '采集源标识',
  status              TINYINT(1) DEFAULT 1 COMMENT '1=展示,0=下架',
  search_count        INT UNSIGNED DEFAULT 0 COMMENT '搜索次数',
  score               DECIMAL(3,1) DEFAULT 0.0 COMMENT '评分',
  is_rec              TINYINT(1) DEFAULT 0 COMMENT '是否推荐',
  is_less             TINYINT(1) DEFAULT 0 COMMENT '是否缺章',
  chapter_num         INT UNSIGNED DEFAULT 0 COMMENT '章节总数',
  chapter_few_num     INT UNSIGNED DEFAULT 0 COMMENT '缺少章节数',
  text_num            INT UNSIGNED DEFAULT 0 COMMENT '总字数',
  hot_score           INT UNSIGNED DEFAULT 0 COMMENT '热度评分',
  addtime             INT UNSIGNED DEFAULT NULL COMMENT '添加时间',
  uptime              INT UNSIGNED DEFAULT NULL COMMENT '更新时间',
  created_at          INT UNSIGNED DEFAULT NULL COMMENT '创建时间',
  updated_at          INT UNSIGNED DEFAULT NULL COMMENT '修改时间',
  UNIQUE KEY uk_book_author (book_name, author),
  KEY idx_status_search (status, search_count),
  KEY idx_source_url (source_url),
  KEY idx_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 搜索/推荐榜表（mc_book_search_rank）
CREATE TABLE IF NOT EXISTS mc_book_search_rank (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  bid          INT UNSIGNED NOT NULL COMMENT 'mc_book.id',
  rec_type     VARCHAR(32) NOT NULL COMMENT '榜单类型 hot_search/high',
  search_count INT UNSIGNED DEFAULT 0 COMMENT '搜索次数/热度',
  score        DECIMAL(3,1) DEFAULT 0.0 COMMENT '评分',
  created_at   INT UNSIGNED DEFAULT NULL COMMENT '创建时间',
  updated_at   INT UNSIGNED DEFAULT NULL COMMENT '修改时间',
  UNIQUE KEY uk_rec_book (rec_type, bid),
  KEY idx_rec_score (rec_type, score),
  KEY idx_rec_search (rec_type, search_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 书评抓取列表（mc_book_comment）
CREATE TABLE IF NOT EXISTS mc_book_comment (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  book_id        VARCHAR(64) NOT NULL COMMENT '外部书籍ID',
  title          VARCHAR(255) NOT NULL COMMENT '书名',
  author         VARCHAR(255) DEFAULT NULL COMMENT '作者',
  cover_logo     VARCHAR(255) DEFAULT NULL COMMENT '封面',
  book_url       VARCHAR(255) DEFAULT NULL COMMENT '书页地址',
  score          DECIMAL(3,1) DEFAULT 0.0 COMMENT '综合评分',
  comment_count  INT UNSIGNED DEFAULT 0 COMMENT '评论数',
  category       VARCHAR(255) DEFAULT NULL COMMENT '标签/分类',
  addtime        INT UNSIGNED DEFAULT NULL COMMENT '采集时间',
  UNIQUE KEY uk_book (book_id),
  KEY idx_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 书评基础信息（mc_book_comment_info）
CREATE TABLE IF NOT EXISTS mc_book_comment_info (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  book_id       INT UNSIGNED NOT NULL COMMENT '书籍ID/外部ID',
  title         VARCHAR(255) NOT NULL COMMENT '书名',
  author        VARCHAR(255) DEFAULT NULL COMMENT '作者',
  book_url      VARCHAR(255) DEFAULT NULL COMMENT '书页地址',
  cover_logo    VARCHAR(255) DEFAULT NULL COMMENT '封面',
  tags          TEXT COMMENT '标签原文',
  category      VARCHAR(255) DEFAULT NULL COMMENT '分类',
  comment_count INT UNSIGNED DEFAULT 0 COMMENT '评论数',
  score         DECIMAL(3,1) DEFAULT 0.0 COMMENT '评分',
  created_at    INT UNSIGNED DEFAULT NULL COMMENT '创建时间',
  updated_at    INT UNSIGNED DEFAULT NULL COMMENT '修改时间',
  UNIQUE KEY uk_book (book_id),
  KEY idx_title_author (title, author)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 书评明细（mc_book_comment_detail）
CREATE TABLE IF NOT EXISTS mc_book_comment_detail (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  book_id         INT UNSIGNED NOT NULL COMMENT '书籍ID',
  user_id         VARCHAR(64) DEFAULT NULL COMMENT '评论用户ID',
  avtar_id        VARCHAR(64) DEFAULT NULL COMMENT '头像ID',
  username        VARCHAR(255) DEFAULT NULL COMMENT '用户名',
  avtar_url       VARCHAR(255) DEFAULT NULL COMMENT '头像链接',
  content         TEXT COMMENT '评论内容',
  score           DECIMAL(3,1) DEFAULT 0.0 COMMENT '评分',
  syn_update_time DATETIME DEFAULT NULL COMMENT '评论时间',
  addtime         INT UNSIGNED DEFAULT NULL COMMENT '采集时间',
  KEY idx_book (book_id),
  KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 小说分类（mc_book_class）
CREATE TABLE IF NOT EXISTS mc_book_class (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  class_name VARCHAR(128) NOT NULL COMMENT '分类名',
  class_pic  VARCHAR(255) DEFAULT NULL COMMENT '分类图',
  book_type  TINYINT UNSIGNED DEFAULT 0 COMMENT '类型标识',
  status     TINYINT(1) DEFAULT 1 COMMENT '1=启用',
  created_at INT UNSIGNED DEFAULT NULL COMMENT '创建时间',
  UNIQUE KEY uk_class (class_name, book_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 采集端中间表：源站小说表（ims_novel_info）
CREATE TABLE IF NOT EXISTS ims_novel_info (
  store_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '源站自增ID',
  story_id           VARCHAR(64) DEFAULT NULL COMMENT '源站唯一标识',
  pro_book_id        INT UNSIGNED DEFAULT 0 COMMENT '对应 mc_book.id',
  title              VARCHAR(255) NOT NULL COMMENT '书名',
  author             VARCHAR(255) DEFAULT NULL COMMENT '作者',
  cover_logo         VARCHAR(255) DEFAULT NULL COMMENT '封面',
  story_link         VARCHAR(255) DEFAULT NULL COMMENT '源站详情链接',
  source             VARCHAR(64) DEFAULT NULL COMMENT '采集源标识',
  cate_name          VARCHAR(128) DEFAULT NULL COMMENT '分类名',
  tags               VARCHAR(255) DEFAULT NULL COMMENT '标签',
  serialize          TINYINT(1) DEFAULT 0 COMMENT '0=连载,1=完结',
  chapter_num        INT UNSIGNED DEFAULT 0 COMMENT '章节数',
  chapter_few_num    INT UNSIGNED DEFAULT 0 COMMENT '缺章数',
  is_less            TINYINT(1) DEFAULT 0 COMMENT '是否缺章',
  is_async           TINYINT(1) DEFAULT 0 COMMENT '是否已同步到主表',
  syn_chapter_status TINYINT(1) DEFAULT 0 COMMENT '章节同步状态',
  note               VARCHAR(255) DEFAULT NULL COMMENT '备注',
  text_num           INT UNSIGNED DEFAULT 0 COMMENT '字数',
  addtime            INT UNSIGNED DEFAULT NULL COMMENT '添加时间',
  updated_at         INT UNSIGNED DEFAULT NULL COMMENT '修改时间',
  KEY idx_async (is_async),
  KEY idx_source (source),
  KEY idx_title_author (title, author),
  KEY idx_pro_book (pro_book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 源站章节表（ims_chapter）
CREATE TABLE IF NOT EXISTS ims_chapter (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键',
  store_id   INT UNSIGNED NOT NULL COMMENT 'ims_novel_info.store_id',
  novelid    VARCHAR(64) DEFAULT NULL COMMENT '源站章节ID',
  chapter_id VARCHAR(64) DEFAULT NULL COMMENT '章节唯一标识',
  link_url   VARCHAR(255) NOT NULL COMMENT '章节链接',
  link_name  VARCHAR(255) NOT NULL COMMENT '章节标题',
  content    LONGTEXT COMMENT '章节正文，可为空（正文存文件时）',
  addtime    INT UNSIGNED DEFAULT NULL COMMENT '添加时间',
  KEY idx_store (store_id),
  KEY idx_story (novelid),
  KEY idx_link (link_url(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 采集校验/比对备份表（ims_novel_info_bak）
CREATE TABLE IF NOT EXISTS ims_novel_info_bak LIKE ims_novel_info;
ALTER TABLE ims_novel_info_bak
  ADD COLUMN empty_status TINYINT(1) DEFAULT 0 AFTER is_less,
  ADD COLUMN check_time   INT UNSIGNED DEFAULT NULL AFTER empty_status;
