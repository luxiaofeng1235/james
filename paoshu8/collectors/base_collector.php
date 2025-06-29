<?php
/**
 * 基础采集器抽象类
 * 定义所有采集器的通用接口和方法
 */

abstract class BaseCollector {
    
    protected $sourceConfig;
    protected $mysql_obj;
    protected $redis_data;
    protected $logger;
    protected $factory;
    
    public function __construct($sourceConfig, $mysql_obj, $redis_data, $logger) {
        $this->sourceConfig = $sourceConfig;
        $this->mysql_obj = $mysql_obj;
        $this->redis_data = $redis_data;
        $this->logger = $logger;
        $this->factory = new FileFactory($mysql_obj, $redis_data);
    }
    
    /**
     * 采集单本小说
     * 
     * @param array $novel 小说信息
     * @param array $progress 进度信息
     * @return array 采集结果
     */
    abstract public function collectNovel($novel, $progress = null);
    
    /**
     * 获取小说详情页HTML
     */
    protected function getNovelHtml($url, $storyId) {
        // 优先从本地缓存读取
        $htmlFile = $this->getHtmlCacheFile($storyId);
        
        if (file_exists($htmlFile)) {
            $this->logger->debug("从缓存读取HTML: {$htmlFile}");
            return readFileData($htmlFile);
        }
        
        // 从远程获取
        $this->logger->debug("从远程获取HTML: {$url}");
        $html = $this->requestWithRetry($url);
        
        if ($html) {
            // 转换编码
            $html = $this->convertEncoding($html);
            // 保存到缓存
            writeFileCombine($htmlFile, $html);
        }
        
        return $html;
    }
    
    /**
     * 带重试的HTTP请求
     */
    protected function requestWithRetry($url, $maxRetries = 3) {
        $retries = 0;
        
        while ($retries < $maxRetries) {
            try {
                $html = webRequest($url, 'GET');
                
                if ($html && !$this->isErrorResponse($html)) {
                    return $html;
                }
                
            } catch (Exception $e) {
                $this->logger->warning("请求失败，准备重试", [
                    'url' => $url,
                    'retry' => $retries + 1,
                    'error' => $e->getMessage()
                ]);
            }
            
            $retries++;
            
            if ($retries < $maxRetries) {
                // 指数退避
                sleep(pow(2, $retries));
            }
        }
        
        throw new Exception("请求失败，已达到最大重试次数: {$url}");
    }
    
    /**
     * 检查是否为错误响应
     */
    protected function isErrorResponse($html) {
        $errorPatterns = [
            '/503 Service/',
            '/403 Forbidden/',
            '/404 Not Found/',
            '/请求失败/',
            '/您请求的文件不存在/'
        ];
        
        foreach ($errorPatterns as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 转换编码
     */
    protected function convertEncoding($html) {
        return array_iconv($html);
    }
    
    /**
     * 获取HTML缓存文件路径
     */
    protected function getHtmlCacheFile($storyId) {
        $cachePath = Env::get('SAVE_HTML_PATH');
        return $cachePath . DS . 'detail_' . $storyId . '.txt';
    }
    
    /**
     * 解析小说基本信息
     */
    protected function parseNovelInfo($html, $rules) {
        $info_data = QueryList::html($html)
            ->rules($rules)
            ->query()
            ->getData();
            
        return $info_data->all();
    }
    
    /**
     * 处理小说数据
     */
    protected function processNovelData($storeData, $novel) {
        // 处理空字符串
        $storeData['location'] = str_replace("\r\n", '', $storeData['location'] ?? '');
        $storeData['location'] = trim($storeData['location']);
        
        // 处理更新时间
        if (isset($storeData['third_update_time'])) {
            $storeData['third_update_time'] = strtotime($storeData['third_update_time']);
        }
        
        // 设置来源
        $storeData['source'] = NovelModel::getSourceUrl($storeData['story_link']);
        
        // 处理标题和作者
        $storeData['title'] = trimBlankSpace($storeData['title'] ?? '');
        $storeData['author'] = trimBlankSpace($storeData['author'] ?? '');
        
        // 处理章节信息
        if (isset($storeData['nearby_chapter'])) {
            $storeData['nearby_chapter'] = addslashes($storeData['nearby_chapter']);
        }
        
        // 处理简介
        if (isset($storeData['intro'])) {
            $intro = addslashes($storeData['intro']);
            $intro = cut_str($intro, 200);
            $intro = trimBlankSpace($intro);
            $storeData['intro'] = trimBlankLine($intro);
        }
        
        // 处理标签
        if (isset($storeData['tag'])) {
            $storeData['tag'] = str_replace('小说', '', $storeData['tag']);
        }
        
        // 设置创建时间
        if (!isset($novel['createtime']) || $novel['createtime'] == 0) {
            $storeData['createtime'] = time();
        }
        
        // 初始化存储信息
        $storeData = NovelModel::initStoreInfo($storeData);
        
        return $storeData;
    }
    
    /**
     * 验证小说数据
     */
    protected function validateNovelData($storeData) {
        // 检查作者
        if (empty($storeData['author'])) {
            throw new Exception("小说没有作者信息");
        }
        
        // 检查标题
        if (empty($storeData['title'])) {
            throw new Exception("小说没有标题信息");
        }
        
        return true;
    }
    
    /**
     * 获取章节列表
     */
    protected function getChapterList($html, $title) {
        $chapters = NovelModel::getCharaList($html, $title);
        
        if (count($chapters) <= 20) {
            throw new Exception("小说章节过少，当前章节数: " . count($chapters));
        }
        
        return $chapters;
    }
    
    /**
     * 处理章节数据
     */
    protected function processChapterData($chapters, $novel) {
        $items = [];
        $chapterIds = [];
        
        foreach ($chapters as $val) {
            if (empty($val['link_name'])) {
                continue;
            }
            
            $linkUrl = trim($val['link_url']);
            $chapterRet = explode('/', $linkUrl);
            $chapterStr = str_replace('.html', '', $chapterRet[2] ?? '');
            $chapterId = (int) $chapterStr;
            
            $val['chapter_id'] = $chapterId;
            $val['store_id'] = $novel['store_id'];
            $val['story_id'] = $novel['story_id'];
            $val['createtime'] = time();
            $val['novelid'] = $chapterId;
            $val['link_str'] = $linkUrl;
            
            $items[$val['link_url']] = $val;
            $chapterIds[$val['chapter_id']] = 1;
        }
        
        return array_values($items);
    }
    
    /**
     * 保存图片到本地
     */
    protected function saveNovelImage($coverUrl, $title, $author) {
        if (!$coverUrl) {
            return false;
        }
        
        try {
            return NovelModel::saveImgToLocal($coverUrl, $title, $author);
        } catch (Exception $e) {
            $this->logger->warning("保存图片失败", [
                'url' => $coverUrl,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 同步到线上数据库
     */
    protected function syncToOnlineDatabase($storeData) {
        try {
            $syncProId = NovelModel::getRedisProId($storeData['store_id'] ?? 0);
            
            if (empty($syncProId)) {
                $syncProId = NovelModel::exchange_book_handle($storeData, $this->mysql_obj);
            }
            
            if (!$syncProId) {
                throw new Exception("未能关联线上小说ID");
            }
            
            return $syncProId;
            
        } catch (Exception $e) {
            $this->logger->error("同步到线上数据库失败", [
                'title' => $storeData['title'] ?? '',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * 更新本地数据库
     */
    protected function updateLocalDatabase($storeData, $novel) {
        try {
            $tableNovelName = Env::get('APICONFIG.TABLE_NOVEL');
            $whereData = "store_id = '" . $novel['store_id'] . "'";
            
            // 对比新旧数据
            $diffData = NovelModel::arrayDiffFiled($novel, $storeData);
            
            if (!empty($diffData)) {
                $diffData['updatetime'] = time();
                $this->mysql_obj->update_data($diffData, $whereData, $tableNovelName);
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("更新本地数据库失败", [
                'store_id' => $novel['store_id'] ?? '',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * 创建JSON文件
     */
    protected function createJsonFile($storeData, $chapters, $refererUrl) {
        try {
            NovelModel::createJsonFile($storeData, $chapters, 0, $refererUrl, $storeData['story_link']);
            return true;
        } catch (Exception $e) {
            $this->logger->error("创建JSON文件失败", [
                'title' => $storeData['title'] ?? '',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * 标记采集完成
     */
    protected function markAsCompleted($storeId, $proBookId = null) {
        try {
            $this->factory->updateStatusInfo($storeId);
            
            if ($proBookId) {
                $this->factory->updateDownStatus($proBookId);
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("标记完成状态失败", [
                'store_id' => $storeId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
?>