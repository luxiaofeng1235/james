-- NEOVEL/采集器核心表结构（自建开发环境用）
-- 根据代码与文档推导的字段，供本地初始化或参考，可按需调整类型与索引
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- 小说主表（mc_book）
CREATE TABLE IF NOT EXISTS mc_book (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_name         VARCHAR(255) NOT NULL,
  author            VARCHAR(255) DEFAULT NULL,
  pic               VARCHAR(255) DEFAULT NULL,
  `desc`            TEXT,
  class_name        VARCHAR(128) DEFAULT NULL,
  tags              VARCHAR(255) DEFAULT NULL,
  serialize         TINYINT(1) DEFAULT 0 COMMENT '0=连载,1=完结',
  last_chapter_title VARCHAR(255) DEFAULT NULL,
  last_chapter_time INT UNSIGNED DEFAULT NULL,
  update_chapter_title VARCHAR(255) DEFAULT NULL,
  update_chapter_time INT UNSIGNED DEFAULT NULL,
  source_url        VARCHAR(255) DEFAULT NULL,
  source            VARCHAR(64) DEFAULT NULL,
  status            TINYINT(1) DEFAULT 1 COMMENT '1=展示,0=下架',
  search_count      INT UNSIGNED DEFAULT 0,
  score             DECIMAL(3,1) DEFAULT 0.0,
  is_rec            TINYINT(1) DEFAULT 0,
  is_less           TINYINT(1) DEFAULT 0 COMMENT '是否缺章标记',
  chapter_num       INT UNSIGNED DEFAULT 0,
  chapter_few_num   INT UNSIGNED DEFAULT 0,
  text_num          INT UNSIGNED DEFAULT 0,
  hot_score         INT UNSIGNED DEFAULT 0,
  addtime           INT UNSIGNED DEFAULT NULL,
  uptime            INT UNSIGNED DEFAULT NULL,
  created_at        INT UNSIGNED DEFAULT NULL,
  updated_at        INT UNSIGNED DEFAULT NULL,
  UNIQUE KEY uk_book_author (book_name, author),
  KEY idx_status_search (status, search_count),
  KEY idx_source_url (source_url),
  KEY idx_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 搜索/推荐榜表（mc_book_search_rank）
CREATE TABLE IF NOT EXISTS mc_book_search_rank (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bid          INT UNSIGNED NOT NULL,
  rec_type     VARCHAR(32) NOT NULL COMMENT 'hot_search/high 等',
  search_count INT UNSIGNED DEFAULT 0,
  score        DECIMAL(3,1) DEFAULT 0.0,
  created_at   INT UNSIGNED DEFAULT NULL,
  updated_at   INT UNSIGNED DEFAULT NULL,
  UNIQUE KEY uk_rec_book (rec_type, bid),
  KEY idx_rec_score (rec_type, score),
  KEY idx_rec_search (rec_type, search_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 书评抓取列表（mc_book_comment）
CREATE TABLE IF NOT EXISTS mc_book_comment (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id        VARCHAR(64) NOT NULL,
  title          VARCHAR(255) NOT NULL,
  author         VARCHAR(255) DEFAULT NULL,
  cover_logo     VARCHAR(255) DEFAULT NULL,
  book_url       VARCHAR(255) DEFAULT NULL,
  score          DECIMAL(3,1) DEFAULT 0.0,
  comment_count  INT UNSIGNED DEFAULT 0,
  category       VARCHAR(255) DEFAULT NULL,
  addtime        INT UNSIGNED DEFAULT NULL,
  UNIQUE KEY uk_book (book_id),
  KEY idx_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 书评基础信息（mc_book_comment_info）
CREATE TABLE IF NOT EXISTS mc_book_comment_info (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id       INT UNSIGNED NOT NULL,
  title         VARCHAR(255) NOT NULL,
  author        VARCHAR(255) DEFAULT NULL,
  book_url      VARCHAR(255) DEFAULT NULL,
  cover_logo    VARCHAR(255) DEFAULT NULL,
  tags          TEXT,
  category      VARCHAR(255) DEFAULT NULL,
  comment_count INT UNSIGNED DEFAULT 0,
  score         DECIMAL(3,1) DEFAULT 0.0,
  created_at    INT UNSIGNED DEFAULT NULL,
  updated_at    INT UNSIGNED DEFAULT NULL,
  UNIQUE KEY uk_book (book_id),
  KEY idx_title_author (title, author)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 书评明细（mc_book_comment_detail）
CREATE TABLE IF NOT EXISTS mc_book_comment_detail (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id         INT UNSIGNED NOT NULL,
  user_id         VARCHAR(64) DEFAULT NULL,
  avtar_id        VARCHAR(64) DEFAULT NULL,
  username        VARCHAR(255) DEFAULT NULL,
  avtar_url       VARCHAR(255) DEFAULT NULL,
  content         TEXT,
  score           DECIMAL(3,1) DEFAULT 0.0,
  syn_update_time DATETIME DEFAULT NULL,
  addtime         INT UNSIGNED DEFAULT NULL,
  KEY idx_book (book_id),
  KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 小说分类（mc_book_class）
CREATE TABLE IF NOT EXISTS mc_book_class (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_name VARCHAR(128) NOT NULL,
  class_pic  VARCHAR(255) DEFAULT NULL,
  book_type  TINYINT UNSIGNED DEFAULT 0,
  status     TINYINT(1) DEFAULT 1,
  created_at INT UNSIGNED DEFAULT NULL,
  UNIQUE KEY uk_class (class_name, book_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 采集端中间表：源站小说表（ims_novel_info）
CREATE TABLE IF NOT EXISTS ims_novel_info (
  store_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  story_id           VARCHAR(64) DEFAULT NULL,
  pro_book_id        INT UNSIGNED DEFAULT 0 COMMENT '对应 mc_book.id',
  title              VARCHAR(255) NOT NULL,
  author             VARCHAR(255) DEFAULT NULL,
  cover_logo         VARCHAR(255) DEFAULT NULL,
  story_link         VARCHAR(255) DEFAULT NULL,
  source             VARCHAR(64) DEFAULT NULL,
  cate_name          VARCHAR(128) DEFAULT NULL,
  tags               VARCHAR(255) DEFAULT NULL,
  serialize          TINYINT(1) DEFAULT 0,
  chapter_num        INT UNSIGNED DEFAULT 0,
  chapter_few_num    INT UNSIGNED DEFAULT 0,
  is_less            TINYINT(1) DEFAULT 0,
  is_async           TINYINT(1) DEFAULT 0,
  syn_chapter_status TINYINT(1) DEFAULT 0,
  note               VARCHAR(255) DEFAULT NULL,
  text_num           INT UNSIGNED DEFAULT 0,
  addtime            INT UNSIGNED DEFAULT NULL,
  updated_at         INT UNSIGNED DEFAULT NULL,
  KEY idx_async (is_async),
  KEY idx_source (source),
  KEY idx_title_author (title, author),
  KEY idx_pro_book (pro_book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 源站章节表（ims_chapter）
CREATE TABLE IF NOT EXISTS ims_chapter (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id   INT UNSIGNED NOT NULL,
  novelid    VARCHAR(64) DEFAULT NULL,
  chapter_id VARCHAR(64) DEFAULT NULL,
  link_url   VARCHAR(255) NOT NULL,
  link_name  VARCHAR(255) NOT NULL,
  content    LONGTEXT,
  addtime    INT UNSIGNED DEFAULT NULL,
  KEY idx_store (store_id),
  KEY idx_story (novelid),
  KEY idx_link (link_url(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 采集校验/比对备份表（ims_novel_info_bak）
CREATE TABLE IF NOT EXISTS ims_novel_info_bak LIKE ims_novel_info;
ALTER TABLE ims_novel_info_bak
  ADD COLUMN empty_status TINYINT(1) DEFAULT 0 AFTER is_less,
  ADD COLUMN check_time   INT UNSIGNED DEFAULT NULL AFTER empty_status;
