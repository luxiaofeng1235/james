<?php
/**
 * 自定义采集器示例
 * 演示如何创建自定义的小说采集器
 */

require_once dirname(__DIR__) . '/collectors/base_collector.php';

/**
 * 示例：自定义小说网站采集器
 * 
 * 这个示例展示了如何为一个新的小说网站创建采集器
 * 假设我们要为 "example.com" 网站创建采集器
 */
class ExampleCollector extends BaseCollector {
    
    /**
     * 采集单本小说的主要方法
     * 
     * @param array $novel 小说基本信息
     * @param array $progress 进度信息（用于断点续传）
     * @return array 采集结果
     */
    public function collectNovel($novel, $progress = null) {
        try {
            $this->logger->info("开始采集示例网站小说: {$novel['title']}", [
                'store_id' => $novel['store_id'],
                'story_id' => $novel['story_id']
            ]);
            
            // 1. 检查是否已经同步过
            if (isset($novel['is_async']) && $novel['is_async'] == 1) {
                $this->logger->info("小说已同步，跳过: {$novel['title']}");
                return ['success' => true, 'chapters_count' => 0, 'message' => '已同步'];
            }
            
            // 2. 检查断点续传
            if ($progress && isset($progress['last_chapter'])) {
                $this->logger->info("从断点继续采集", [
                    'last_chapter' => $progress['last_chapter']
                ]);
            }
            
            // 3. 获取小说详情页HTML
            $html = $this->getNovelHtml($novel['story_link'], $novel['story_id']);
            
            if (!$html) {
                throw new Exception("无法获取小说详情页HTML");
            }
            
            // 4. 解析小说基本信息
            $storeData = $this->parseNovelInfo($html, $this->getParseRules());
            
            if (empty($storeData)) {
                throw new Exception("解析小说信息失败");
            }
            
            // 5. 处理和验证数据
            $storeData['story_link'] = $novel['story_link'];
            $storeData = $this->processNovelData($storeData, $novel);
            $storeData['story_id'] = $novel['story_id'];
            
            // 验证必要字段
            $this->validateNovelData($storeData);
            
            // 6. 获取章节列表
            $chapters = $this->getChapterList($html, $storeData['title']);
            
            // 7. 处理章节数据
            $itemList = $this->processChapterData($chapters, $novel);
            
            // 8. 应用自定义过滤规则
            $itemList = $this->applyCustomFilters($itemList);
            
            // 9. 清洗章节数据
            $itemList = NovelModel::cleanArrayData($itemList, ['chapter_id']);
            
            // 10. 保存封面图片
            $this->saveNovelImage(
                $storeData['cover_logo'] ?? '', 
                $storeData['title'], 
                $storeData['author']
            );
            
            // 11. 创建JSON文件
            $hostData = parse_url($novel['story_link']);
            $refererUrl = $hostData['scheme'] . '://' . $hostData['host'];
            $this->createJsonFile($storeData, $itemList, $refererUrl);
            
            // 12. 同步到线上数据库
            $syncProId = $this->syncToOnlineDatabase($storeData);
            $storeData['pro_book_id'] = $syncProId;
            
            // 13. 更新本地数据库
            $this->updateLocalDatabase($storeData, $novel);
            
            // 14. 同步章节信息
            $novelData = array_merge([
                'pro_book_id' => $syncProId,
                'story_id' => $novel['story_id'],
                'store_id' => $novel['store_id'],
                'syn_chapter_status' => $novel['syn_chapter_status'] ?? 0,
            ], $storeData);
            
            $this->factory->synChapterInfo($novel['story_id'], $novelData);
            
            // 15. 标记完成
            $this->markAsCompleted($novel['store_id'], $syncProId);
            
            $this->logger->info("示例网站小说采集完成: {$novel['title']}", [
                'chapters_count' => count($itemList),
                'pro_book_id' => $syncProId
            ]);
            
            return [
                'success' => true,
                'chapters_count' => count($itemList),
                'pro_book_id' => $syncProId
            ];
            
        } catch (Exception $e) {
            $this->logger->error("示例网站小说采集失败: {$novel['title']}", [
                'store_id' => $novel['store_id'],
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取解析规则
     * 根据目标网站的HTML结构定义解析规则
     */
    private function getParseRules() {
        return [
            // 基本信息解析规则
            'title' => ['.book-title h1', 'text'],
            'author' => ['.book-author a', 'text'],
            'cover_logo' => ['.book-cover img', 'src'],
            'intro' => ['.book-intro .content', 'text'],
            'status' => ['.book-status .status', 'text'],
            'cate_name' => ['.book-category a', 'text'],
            'tag' => ['.book-tags .tag', 'text'],
            'nearby_chapter' => ['.book-latest a', 'text'],
            'third_update_time' => ['.book-update-time', 'text'],
            'text_num' => ['.book-stats .words', 'text'],
            'location' => ['.book-location', 'text']
        ];
    }
    
    /**
     * 自定义数据处理
     * 处理特定网站的数据格式和特殊情况
     */
    protected function processNovelData($storeData, $novel) {
        // 调用父类的通用处理
        $storeData = parent::processNovelData($storeData, $novel);
        
        // 示例网站特殊处理
        
        // 1. 处理图片URL
        if (isset($storeData['cover_logo']) && !preg_match('/^https?:\/\//', $storeData['cover_logo'])) {
            $storeData['cover_logo'] = 'https://example.com' . $storeData['cover_logo'];
        }
        
        // 2. 处理字数格式
        if (isset($storeData['text_num'])) {
            $textNum = $storeData['text_num'];
            if (preg_match('/(\d+(?:\.\d+)?)\s*万字/', $textNum, $matches)) {
                $storeData['text_num'] = floatval($matches[1]) * 10000;
            } elseif (preg_match('/(\d+)\s*字/', $textNum, $matches)) {
                $storeData['text_num'] = intval($matches[1]);
            }
        }
        
        // 3. 处理更新时间格式
        if (isset($storeData['third_update_time'])) {
            $timeStr = $storeData['third_update_time'];
            // 处理相对时间，如"2小时前"、"昨天"等
            $storeData['third_update_time'] = $this->parseRelativeTime($timeStr);
        }
        
        // 4. 处理分类映射
        if (isset($storeData['cate_name'])) {
            $storeData['cate_name'] = $this->mapCategory($storeData['cate_name']);
        }
        
        // 5. 处理状态标准化
        if (isset($storeData['status'])) {
            $storeData['status'] = $this->normalizeStatus($storeData['status']);
        }
        
        return $storeData;
    }
    
    /**
     * 自定义章节过滤规则
     */
    private function applyCustomFilters($chapters) {
        // 过滤广告章节
        $adKeywords = [
            '广告',
            '推广',
            '免费阅读',
            '最新网址',
            '手机阅读',
            '作者有话说'
        ];
        
        $filteredChapters = [];
        
        foreach ($chapters as $chapter) {
            $chapterName = $chapter['link_name'] ?? '';
            
            // 检查是否包含广告关键词
            $isAd = false;
            foreach ($adKeywords as $keyword) {
                if (strpos($chapterName, $keyword) !== false) {
                    $isAd = true;
                    break;
                }
            }
            
            // 检查章节名称长度（过短或过长的可能是广告）
            if (mb_strlen($chapterName) < 3 || mb_strlen($chapterName) > 50) {
                $isAd = true;
            }
            
            // 检查是否为重复章节
            if ($this->isDuplicateChapter($chapter, $filteredChapters)) {
                continue;
            }
            
            if (!$isAd) {
                $filteredChapters[] = $chapter;
            } else {
                $this->logger->debug("过滤广告章节: {$chapterName}");
            }
        }
        
        return $filteredChapters;
    }
    
    /**
     * 解析相对时间
     */
    private function parseRelativeTime($timeStr) {
        $now = time();
        
        if (preg_match('/(\d+)\s*分钟前/', $timeStr, $matches)) {
            return $now - ($matches[1] * 60);
        } elseif (preg_match('/(\d+)\s*小时前/', $timeStr, $matches)) {
            return $now - ($matches[1] * 3600);
        } elseif (preg_match('/(\d+)\s*天前/', $timeStr, $matches)) {
            return $now - ($matches[1] * 86400);
        } elseif (strpos($timeStr, '昨天') !== false) {
            return $now - 86400;
        } elseif (strpos($timeStr, '今天') !== false) {
            return $now;
        } else {
            // 尝试解析具体日期
            $timestamp = strtotime($timeStr);
            return $timestamp ?: $now;
        }
    }
    
    /**
     * 分类映射
     */
    private function mapCategory($category) {
        $categoryMap = [
            '玄幻奇幻' => '玄幻',
            '都市言情' => '都市',
            '历史军事' => '历史',
            '科幻灵异' => '科幻',
            '网游竞技' => '网游',
            '武侠仙侠' => '武侠',
            '女生频道' => '言情'
        ];
        
        return $categoryMap[$category] ?? $category;
    }
    
    /**
     * 状态标准化
     */
    private function normalizeStatus($status) {
        if (in_array($status, ['连载', '连载中', '更新中', '未完结'])) {
            return '连载中';
        } elseif (in_array($status, ['完结', '已完结', '完本', '全本'])) {
            return '已经完本';
        } else {
            return '未知';
        }
    }
    
    /**
     * 检查重复章节
     */
    private function isDuplicateChapter($chapter, $existingChapters) {
        $chapterName = $chapter['link_name'] ?? '';
        $chapterUrl = $chapter['link_url'] ?? '';
        
        foreach ($existingChapters as $existing) {
            if ($existing['link_name'] === $chapterName || 
                $existing['link_url'] === $chapterUrl) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 重写错误响应检查，添加网站特定的错误模式
     */
    protected function isErrorResponse($html) {
        // 调用父类的通用检查
        if (parent::isErrorResponse($html)) {
            return true;
        }
        
        // 示例网站特定的错误模式
        $siteSpecificErrors = [
            '/小说不存在/',
            '/页面已删除/',
            '/访问被限制/',
            '/服务器维护中/'
        ];
        
        foreach ($siteSpecificErrors as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 自定义编码转换
     */
    protected function convertEncoding($html) {
        // 示例网站使用GBK编码
        if (mb_detect_encoding($html, ['UTF-8', 'GBK', 'GB2312'], true) !== 'UTF-8') {
            $html = iconv('GBK', 'UTF-8//IGNORE', $html);
        }
        
        // 清理特殊字符
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
        
        return $html;
    }
}

/**
 * 使用示例
 */
echo "=== 自定义采集器示例 ===\n\n";

try {
    // 模拟配置和依赖
    $sourceConfig = [
        'name' => '示例小说网',
        'base_url' => 'https://example.com',
        'concurrent_limit' => 3,
        'delay' => 1500,
        'encoding' => 'gbk'
    ];
    
    // 这里在实际使用中需要传入真实的依赖对象
    // $collector = new ExampleCollector($sourceConfig, $mysql_obj, $redis_data, $logger);
    
    echo "1. 自定义采集器类已定义\n";
    echo "   - 继承自 BaseCollector\n";
    echo "   - 实现了 collectNovel() 方法\n";
    echo "   - 包含网站特定的数据处理逻辑\n\n";
    
    echo "2. 主要特性:\n";
    echo "   ✓ 自定义解析规则\n";
    echo "   ✓ 特殊数据格式处理\n";
    echo "   ✓ 广告章节过滤\n";
    echo "   ✓ 重复内容检测\n";
    echo "   ✓ 相对时间解析\n";
    echo "   ✓ 分类映射\n";
    echo "   ✓ 状态标准化\n\n";
    
    echo "3. 使用方法:\n";
    echo "   ```php\n";
    echo "   // 创建采集器实例\n";
    echo "   \$collector = new ExampleCollector(\$config, \$mysql, \$redis, \$logger);\n";
    echo "   \n";
    echo "   // 采集单本小说\n";
    echo "   \$result = \$collector->collectNovel(\$novel, \$progress);\n";
    echo "   \n";
    echo "   // 检查结果\n";
    echo "   if (\$result['success']) {\n";
    echo "       echo \"采集成功: {\$result['chapters_count']} 章节\";\n";
    echo "   } else {\n";
    echo "       echo \"采集失败: {\$result['error']}\";\n";
    echo "   }\n";
    echo "   ```\n\n";
    
    echo "4. 集成到多源采集器:\n";
    echo "   - 在 multi_source_collector.php 中注册新采集器\n";
    echo "   - 在配置文件中添加新源的配置\n";
    echo "   - 更新 createSourceCollector() 方法\n\n";
    
    echo "5. 测试建议:\n";
    echo "   - 先用少量数据测试解析规则\n";
    echo "   - 验证数据格式转换是否正确\n";
    echo "   - 测试错误处理和重试机制\n";
    echo "   - 检查性能和内存使用\n\n";
    
    echo "✓ 自定义采集器示例完成\n";
    echo "  参考此示例可以为任何小说网站创建专用采集器\n";
    
} catch (Exception $e) {
    echo "❌ 示例运行失败: " . $e->getMessage() . "\n";
}

echo "\n=== 示例结束 ===\n";
?>