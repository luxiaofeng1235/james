<?php
/**
 * Lightweight pre-flight checker for env/config and local paths.
 * Usage: php scripts/smoke.php
 */

require_once dirname(__DIR__) . '/library/Env.php';

$errors = [];
$warnings = [];

try {
    // Trigger config load
    Env::get('TABLE_MC_BOOK');
} catch (Throwable $e) {
    $errors[] = "配置文件加载失败: " . $e->getMessage();
    reportAndExit($errors, $warnings);
}

$requiredTop = [
    'TABLE_MC_BOOK',
    'SAVE_JSON_PATH',
    'SAVE_NOVEL_PATH',
    'SAVE_IMG_PATH',
];

$requiredSections = [
    'DATABASE' => ['HOST_NAME', 'PORT', 'USERNAME', 'PASSWORD', 'DBNAME'],
    'DATABASE_PRO' => ['HOST_NAME', 'PORT', 'USERNAME', 'PASSWORD', 'DBNAME'],
    'REDIS' => ['HOST_NAME', 'PORT'],
    'APICONFIG' => ['TABLE_NOVEL', 'TABLE_CHAPTER', 'TABLE_CATE', 'PAOSHU_STR'],
    'SEARCH' => ['API_URL'],
];

foreach ($requiredTop as $key) {
    if (empty(Env::get($key))) {
        $errors[] = "缺少必填配置: {$key}";
    }
}

foreach ($requiredSections as $section => $keys) {
    foreach ($keys as $key) {
        $full = "{$section}.{$key}";
        if (Env::get($full) === false || Env::get($full) === null || Env::get($full) === '') {
            $errors[] = "缺少必填配置: {$full}";
        }
    }
}

$pathKeys = ['SAVE_JSON_PATH', 'SAVE_NOVEL_PATH', 'SAVE_IMG_PATH', 'SAVE_BOOK_COMMENT', 'SAVE_HTML_PATH'];
foreach ($pathKeys as $key) {
    $path = Env::get($key);
    if (!$path) {
        continue;
    }
    if (!is_dir($path)) {
        $warnings[] = "目录不存在: {$key} => {$path}";
    } elseif (!is_writable($path)) {
        $warnings[] = "目录不可写: {$key} => {$path}";
    }
}

reportAndExit($errors, $warnings);

function reportAndExit(array $errors, array $warnings): void
{
    foreach ($errors as $msg) {
        echo "[ERROR] {$msg}\n";
    }
    foreach ($warnings as $msg) {
        echo "[WARN] {$msg}\n";
    }
    if (!$errors && !$warnings) {
        echo "[OK] 配置检查通过\n";
    }
    exit($errors ? 1 : 0);
}
