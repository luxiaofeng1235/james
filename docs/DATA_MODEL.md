# 核心数据模型说明

## mc_book（书籍主表）
- 关键：`id`、`book_name`、`author`、`source_url/source` 去重标识；`status`(1=展示,0=下架)。
- 状态：`serialize`(0 连载/1 完结)、`is_rec` 推荐标记、`is_less` 缺章标记。
- 统计：`search_count`、`score`、`hot_score`、`chapter_num/chapter_few_num`、`text_num`。
- 更新时间：`update_chapter_title/update_chapter_time`、`last_chapter_title/last_chapter_time`、`uptime/addtime/created_at/updated_at`。
- 媒体与分类：`pic`、`class_name/tags`。

## mc_book_search_rank（榜单）
- `bid` 关联 mc_book，`rec_type`（如 hot_search/high），`search_count`/`score`，`created_at/updated_at`。

## 书评相关
- `mc_book_comment`：采集自有书评列表，字段 `book_id/title/author/cover_logo/book_url/score/comment_count/category/addtime`。
- `mc_book_comment_info`：待抓取的书评入口信息。
- `mc_book_comment_detail`：明细评论，含 `user_id/username/avtar_url/content/score/syn_update_time/addtime`。

## 分类表
- `mc_book_class`：`class_name/class_pic/book_type/status` 等。

## 采集中间表
- `ims_novel_info`：源站书目，含 `store_id/story_id/pro_book_id/is_async/is_less/syn_chapter_status/note` 等。
- `ims_chapter`：源站章节列表，存章节 url/title，正文通常以 JSON/TXT 方式落地而非数据库。
