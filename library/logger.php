<?php

/**
 * 简单 CLI 日志工具，统一格式输出。
 */
function cli_log(string $level, string $message, array $context = []): void
{
    $ts = date('c');
    $lvl = strtoupper($level);
    $extra = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    echo "[{$ts}] [{$lvl}] {$message}{$extra}\n";
}
