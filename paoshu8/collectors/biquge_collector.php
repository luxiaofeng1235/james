<?php
/**
 * 笔趣阁采集器
 * 专门处理笔趣阁网站的小说采集
 */

require_once 'base_collector.php';

class BiqugeCollector extends BaseCollector {
    
    /**
     * 采集单本小说
     */
    public function collectNovel($novel, $progress = null) {
        try {
            $this->logger->info("开始采集笔趣阁小说: {$novel['title']}", [
                'store_id' => $novel['store_id'],
                'story_id' => $novel['story_id']
            ]);
            
            // 检查是否已经同步过
            if (isset($novel['is_async']) && $novel['is_async'] == 1) {
                $this->logger->info("小说已同步，跳过: {$novel['title']}");
                return ['success' => true, 'chapters_count' => 0, 'message' => '已同步'];
            }
            
            // 获取小说详情页HTML
            $html = $this->getNovelHtml($novel['story_link'], $novel['story_id']);
            
            if (!$html) {
                throw new Exception("无法获取小说详情页HTML");
            }
            
            // 解析小说信息
            $storeData = $this->parseNovelInfo($html, $this->getParseRules());
            
            if (empty($storeData)) {
                throw new Exception("解析小说信息失败");
            }
            
            // 处理小说数据
            $storeData['story_link'] = $novel['story_link'];
            $storeData = $this->processNovelData($storeData, $novel);
            $storeData['story_id'] = $novel['story_id'];
            
            // 验证数据
            $this->validateNovelData($storeData);
            
            // 获取章节列表
            $chapters = $this->getChapterList($html, $storeData['title']);
            
            // 处理章节数据
            $itemList = $this->processChapterData($chapters, $novel);
            
            // 清洗章节数据
            $itemList = NovelModel::cleanArrayData($itemList, ['chapter_id']);
            
            // 保存图片
            $this->saveNovelImage($storeData['cover_logo'] ?? '', $storeData['title'], $storeData['author']);
            
            // 获取referer URL
            $hostData = parse_url($novel['story_link']);
            $refererUrl = $hostData['scheme'] . '://' . $hostData['host'];
            
            // 创建JSON文件
            $this->createJsonFile($storeData, $itemList, $refererUrl);
            
            // 同步到线上数据库
            $syncProId = $this->syncToOnlineDatabase($storeData);
            $storeData['pro_book_id'] = $syncProId;
            
            // 更新本地数据库
            $this->updateLocalDatabase($storeData, $novel);
            
            // 同步章节信息
            $novelData = array_merge([
                'pro_book_id' => $syncProId,
                'story_id' => $novel['story_id'],
                'store_id' => $novel['store_id'],
                'syn_chapter_status' => $novel['syn_chapter_status'] ?? 0,
            ], $storeData);
            
            $this->factory->synChapterInfo($novel['story_id'], $novelData);
            
            // 标记完成
            $this->markAsCompleted($novel['store_id'], $syncProId);
            
            $this->logger->info("笔趣阁小说采集完成: {$novel['title']}", [
                'chapters_count' => count($itemList),
                'pro_book_id' => $syncProId
            ]);
            
            return [
                'success' => true,
                'chapters_count' => count($itemList),
                'pro_book_id' => $syncProId
            ];
            
        } catch (Exception $e) {
            $this->logger->error("笔趣阁小说采集失败: {$novel['title']}", [
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
     */
    private function getParseRules() {
        // 笔趣阁的解析规则
        return [
            'title' => ['.book-info h1', 'text'],
            'author' => ['.book-info .author', 'text'],
            'cover_logo' => ['.book-img img', 'src'],
            'intro' => ['.book-intro', 'text'],
            'status' => ['.book-state', 'text'],
            'cate_name' => ['.book-cate', 'text'],
            'tag' => ['.book-tag', 'text'],
            'nearby_chapter' => ['.book-last-chapter a', 'text'],
            'third_update_time' => ['.book-update-time', 'text'],
            'text_num' => ['.book-words', 'text']
        ];
    }
    
    /**
     * 笔趣阁特殊的编码转换
     */
    protected function convertEncoding($html) {
        // 笔趣阁可能需要特殊的编码处理
        if (mb_detect_encoding($html, ['UTF-8', 'GBK', 'GB2312'], true) !== 'UTF-8') {
            $html = iconv('GBK', 'UTF-8//IGNORE', $html);
        }
        return $html;
    }
    
    /**
     * 笔趣阁特殊的数据处理
     */
    protected function processNovelData($storeData, $novel) {
        $storeData = parent::processNovelData($storeData, $novel);
        
        // 笔趣阁特殊处理
        if (isset($storeData['cover_logo']) && !preg_match('/^https?:\/\//', $storeData['cover_logo'])) {
            // 补全图片URL
            $hostData = parse_url($novel['story_link']);
            $baseUrl = $hostData['scheme'] . '://' . $hostData['host'];
            $storeData['cover_logo'] = $baseUrl . $storeData['cover_logo'];
        }
        
        // 处理字数信息
        if (isset($storeData['text_num'])) {
            $textNum = $storeData['text_num'];
            if (preg_match('/(\d+(?:\.\d+)?)\s*万/', $textNum, $matches)) {
                $storeData['text_num'] = floatval($matches[1]) * 10000;
            } elseif (preg_match('/(\d+)/', $textNum, $matches)) {
                $storeData['text_num'] = intval($matches[1]);
            }
        }
        
        return $storeData;
    }
    
    /**
     * 笔趣阁章节列表特殊处理
     */
    protected function getChapterList($html, $title) {
        // 使用NovelModel的通用方法，但可能需要特殊处理
        $chapters = NovelModel::getCharaList($html, $title, false, false, 'biquge');
        
        if (count($chapters) <= 20) {
            throw new Exception("笔趣阁小说章节过少，当前章节数: " . count($chapters));
        }
        
        // 过滤广告章节
        $chapters = $this->filterAdChapters($chapters);
        
        return $chapters;
    }
    
    /**
     * 过滤广告章节
     */
    private function filterAdChapters($chapters) {
        $adKeywords = [
            '广告',
            '推广',
            '免费阅读',
            '最新网址',
            '手机阅读'
        ];
        
        return array_filter($chapters, function($chapter) use ($adKeywords) {
            $chapterName = $chapter['link_name'] ?? '';
            
            foreach ($adKeywords as $keyword) {
                if (strpos($chapterName, $keyword) !== false) {
                    return false;
                }
            }
            
            return true;
        });
    }
}
?>