<?php
/**
 * 泡书吧采集器
 * 专门处理泡书吧网站的小说采集
 */

require_once 'base_collector.php';

class Paoshu8Collector extends BaseCollector {
    
    /**
     * 采集单本小说
     */
    public function collectNovel($novel, $progress = null) {
        try {
            $this->logger->info("开始采集泡书吧小说: {$novel['title']}", [
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
            
            $this->logger->info("泡书吧小说采集完成: {$novel['title']}", [
                'chapters_count' => count($itemList),
                'pro_book_id' => $syncProId
            ]);
            
            return [
                'success' => true,
                'chapters_count' => count($itemList),
                'pro_book_id' => $syncProId
            ];
            
        } catch (Exception $e) {
            $this->logger->error("泡书吧小说采集失败: {$novel['title']}", [
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
        global $urlRules;
        return $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['info_replace'] ?? [];
    }
    
    /**
     * 重写获取HTML方法，泡书吧特殊处理
     */
    protected function getNovelHtml($url, $storyId) {
        // 泡书吧直接curl请求
        if (preg_match('/paoshu8/', $url)) {
            $retLink = substr($url, -1, 1);
            if ($retLink != '/') {
                $url .= '/';
            }
            
            $this->logger->debug("泡书吧直接请求: {$url}");
            $html = $this->requestWithRetry($url);
            return $this->convertEncoding($html);
        }
        
        // 其他情况使用父类方法
        return parent::getNovelHtml($url, $storyId);
    }
    
    /**
     * 泡书吧特殊的数据处理
     */
    protected function processNovelData($storeData, $novel) {
        $storeData = parent::processNovelData($storeData, $novel);
        
        // 泡书吧特殊处理：为了应付过审，只跑特定分类
        if ($storeData['source'] == 'paoshu8') {
            $storeData['cate_name'] = '网游竞技';
            $storeData['tag'] = '网游';
        }
        
        return $storeData;
    }
}
?>