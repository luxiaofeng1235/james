# 配置与环境变量指引

Env 仅读取根目录 `.env`（`PHP_` 前缀存入环境）。示例见根目录 `.env.example` 或 `.env_dev.example`，复制为 `.env` 后使用。

## 必填键（最小运行）
- 顶层：`TABLE_MC_BOOK`（默认 `mc_book`）；`SAVE_JSON_PATH`、`SAVE_NOVEL_PATH`、`SAVE_IMG_PATH`、`SAVE_BOOK_COMMENT`、`SAVE_HTML_PATH` 为本地存储路径。
- `[DATABASE]`：`HOST_NAME`、`PORT`、`USERNAME`、`PASSWORD`、`DBNAME`；连接名 `db_master`/`db_slave` 复用此组。
- `[DATABASE_PRO]`：线上书库 `db_novel_pro`。
- `[REDIS]`：`HOST_NAME`、`PORT`、`PASSWORD`。
- `[APICONFIG]`：`TABLE_NOVEL`、`TABLE_CHAPTER`、`TABLE_CATE`，以及抓取源标识 `PAOSHU_STR`，基础域名 `PAOSHU_HOST/PAOSHU_API_URL`，默认封面 `DEFAULT_PIC`。
- `[SEARCH]`：`API_URL`（迅搜/全文检索接口）。
- `[COMMENT]`：`WEB_URL`、`API_URL`、`IMG_URL`、`TOKEN`（书评爬虫用）。
- `[CLI]`（可选）：`MEMORY_LIMIT`、`TIME_LIMIT` 覆盖 CLI 脚本资源限制。

## 章节存储
- 章节目录与正文默认写入 JSON/TXT，路径由 `SAVE_JSON_PATH`、`SAVE_NOVEL_PATH` 控制，命名规则见 `NovelModel::getBookFilePath`。
- 同步到 OSS 时读取本地 JSON/TXT，再更新 `mc_book.is_aws_store` 状态；建议在上传流程记录文件哈希/版本。

## 加载顺序与格式
- 未带 Section 的键直接映射为 `PHP_<KEY>`；Section 里的键映射为 `PHP_<SECTION>_<KEY>`（点号会被转为下划线）。
- 当前环境通过 `RUN_ENV` 或 `APP_ENV` 选择：`prod`/`dev`/`test`。
- 配置文件为 INI 语法，支持 `;` 注释；保持 UTF-8。

## 常见问题
- `Env::get` 返回空：检查文件是否存在、权限、Section 名是否与代码匹配（如 `APICONFIG`）。  
- Composer 自动加载：仓库未使用 `Acme\` 命名空间；如需自定义命名空间，请在 `composer.json` 中声明并创建对应目录。  
- 路径不存在：脚本会尝试自动创建目录，但建议提前创建并赋予可写权限。
