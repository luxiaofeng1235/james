<?php
use QL\QueryList;
use QL\Ext\CurlMulti;
class DouyinModel{


    private static $timeout = 900;
    private static  $chunk_size =200;//指定具体的页码分页长度最多请求200个
    private static  $defaultimg = 'https://www.banjiashi.com/qs_theme/defaultimg.png'; //默认封面
    private static $method ='get';
    private static $cateIds = [1,4,8,11]; //默认只采集这几个分类吧


    /**
     * @note 获取采集HTML的key信息
     * @param $id int 小说ID（第三方的标识）
     * @param $source string 网站来源
     * @return string
     *
     */
    public static function douyinHtmlCacheKey($source="",$id = ""){
        if(!$source || !$id){
            return false;
        }
        #缓存对应的html的key的标记
        #规则：book+来源+第三方id
        $html_key  =sprintf("book_%s_id:%s",$source,$id);
        return $html_key;
    }


     /**
    * @note 获取佳士小说的ID-根据url来解析
    * @param $story_link string 根据连接地址解析获取目标网站ID
    * @return string
    *
    */
    public static function getDouyinId($story_link= ""){
        if(!$story_link){
            return 0;
        }
        $hostData= parse_url($story_link);
        $pathData = explode('/',$hostData['path']);
        $pathData = array_filter($pathData);
        $pathData = array_values($pathData);
        if(count($pathData)>1){
            //使用后缀里的第二个
            $third_novel_id = $pathData[1] ?? 0;
        }else{
            //如果某学里面没有这个就用默认的第一个
            $third_novel_id = $pathData[0] ?? 0;
        }
        return $third_novel_id;
    }

     /**
     * @note 根据id和allPage来获取对应的分页的章节列表
     * @param $id int 小说ID（第三方的标识）
     * @param $title string  小说标题
     * @param $allPage int 所有分页内容 array对象
     * @return array
     *
     */
    public static function getAllDouyinChapter($id,$title,$allPage){
        if(!$id || !$title || !$allPage){
            return false;
        }
        $book_key ="douyin_id:".$id;
        echo "redis_key =【{$book_key}】 缓存过期时间为：timtout =【".self::$timeout." S】 \r\n";
        global $redis_data,$urlRules;
        $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_info'];
        $info = $redis_data->get_redis($book_key);
        if(!$info || empty($info) || $info=='[]'){
            $urls = DouyinModel::createDouyinUrls($id,$allPage);
            $t_url = array_values($urls);
            $chapterList = StoreModel::swooleRquest($t_url , self::$method);
            $goodsList = [];
            echo "当前小说共 【 ".count($urls)." 】 页需要去拉取\r\n";
            foreach ($urls as $key =>$val){
                $host_data = parse_url($val);
                $path = $host_data['path'];
                if(isset($chapterList[$path])){
                    $goodsList[$path]= $chapterList[$path] ?? '';
                }
            }
            $list = [];
            // unset($goodsList['/index/88934/1/']);
            if(count($goodsList)>0){
                foreach($goodsList as $key =>$val){
                    $chapter = DouyinModel::getDouyinChapter($key,$title,$val);
                    if(!$chapter){
                        $chapter = [];
                    }
                    $list = array_merge($list,$chapter);
                }
            }
            if(!$list){
                $list = [];
            }
            $redis_data->set_redis($book_key,json_encode($list),self::$timeout);
            return $list;
        }else{
            echo "我是缓存中 key= 【{$book_key}】 的章节目录哈 \r\n";
            $data = json_decode($info,true);
            return $data ?? [];
        }
    }

        /**
     * @note 获取佳士小说的所有章节列表
     * @param $path string 路径信息
     * @param $title string 标题
     * @param $html string HTML内容
     * @return array
     *
     */
    public static function getDouyinChapter($path ="",$title="",$html=""){
        if(empty($html) || empty($html)){
            return false;
        }
        #处理标题转义字符
        $title = NovelModel::exchangePregStr($title);
        preg_match('/《' . $title . '》正文.*<\/dl>/ism', $html, $with_content);
        //《离婚后她千亿身价曝光，成了全球的神》章节目录
        //ul class="chapter_mu"
        # preg_match('/<ul class=\"chapter_mu\">.*/ism', $html, $list);
        $link_reg = '/<a.*?href="(.*?)".*?>/';
        //这个地方因为A链接有可能是多个的形式展示，导致取不出来A标签，用一个万能的表达式来根据当前的配置取相关的连接信息
        $text_reg = '/<a.*?href=\"[^\"]*\".*?>(.*?)<\/a>/ims'; //匹配链接里的文本(zhge)
        $chapter_list = [];
        if($with_content){
            $contents = $with_content[0] ?? '';
            preg_match_all($link_reg, $contents, $link_href); //匹配链接
            preg_match_all($text_reg, $contents, $link_text); //匹配文本;
            $link_text = array_map('trimBlankLine', $link_text);
            $len = count($link_href[1]);
            $result = [];
            for($i = 0;$i<$len;$i++){
                $text =  trimBlankSpace($link_text[1][$i]) ;
                $text = str_replace("<span>",'',$text);
                $text = str_replace("</span>",'',$text);
                $chapter_list[] = [
                    'link_name' =>  $text ,
                    'link_url'  => $link_href[1][$i] ?? '',
                ];
            }
        }
        return $chapter_list;
    }

    /**
    * @note 生成自动化的对象数据
    *
    * @param $urls array 章节URL
    * @param $title string 标题
    * @return array
    */
    public static function createDouyinIndexData($url,$redis_key ='douyin_index_all'){
        if(!$url){
            return false;
        }
        global $redis_data,$urlRules;
        echo "start collect data from url ={$url}\r\n";
        $urls=[];
        foreach(self::$cateIds as $cate_id){

            $cate_url =sprintf("%sfenlei/%s/1/",$url,$cate_id);
            $urls[] = $cate_url;
        }
        array_unshift($urls, $url);
        $source = NovelModel::getSourceUrl($url);
        $res = StoreModel::swooleRquest($urls,self::$method);
        $item = [];
        $baseinfo = [];
        foreach($res as $key =>$val){
            $range = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['douyin_range'];
            $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['douyin_index'];
            #查询出来最新的更新的列表
            $storyList = QueryList::html($val)
                    ->rules($rules)
                    ->range($range)
                    ->query()
                    ->getData();
            $storyList = $storyList->all();
            // echo "count = ".count($storyList).PHP_EOL;
            if(!empty($storyList)){
                foreach($storyList as &$val){
                    if(!$val || !isset($val['story_link'])){
                        continue;
                    }
                    //解析获取story_id
                    $story_link = trim($val['story_link']);
                    $urlData = parse_url($story_link);
                    $path_result  = $urlData['path'];
                    $path_result = str_replace('/bqg/' , '', $path_result);
                    $path = preg_replace('/\//','_',$path_result);
                    $story_id = substr($path,  0 , -1);
                    $val['story_id'] = $story_id;
                    $val['source'] = $source;
                    $baseinfo[] = $val;
                }
            }
        }
        // $index_item = $redis_data->get_redis($redis_key);
        // if($index_item){
        //     echo "删除未过期的redis缓存数据重新抓取=====\r\n";
        //     $redis_data->del_redis($redis_key);
        // }
        // echo "设置的缓存过期时间为：timtout = {".self::$timeout." S} \r\n";
        // if(!$index_item){
            // echo "缓存过期，从首页的 base_url = 【{$url}】 直接获取最新数据\r\n";
        // $source = NovelModel::getSourceUrl($url);
        // $res = StoreModel::swooleRquest($url,self::$method);
        // $index_data = array_values($res);
        // $data = $index_data[0] ?? [];
        return $baseinfo;
        // }else{
        //     $timer = $redis_data->ttl($redis_key);
        //     $avager_time = sprintf("%.2f",$timer/60);
        //     echo "我是redis中的缓存数据， 未过期，大概还有【{$avager_time}】 分到期\r\n";
        //     $ret = json_decode($index_item,true);
        //     return $ret ?? [];
        // }

    }

        /**
     * @note 生成章节访问的url信息
     * @param $id int 小说ID
     * @param $allPage int 总页码数量
     * @return array
     *
     */
    public static function createDouyinUrls($id = 0,$allPage = []){
        if(!$allPage){
            return [];
        }

        $base_url  = Env::get('APICONFIG.DOUYIN_NEXT_URL');
        $newUrl = [];
        #处理获取的应用配置信息

        if(count($allPage)>=1){
            $page = 0;
            for ($i=0; $i < count($allPage); $i++) {
                $page++;
                $next_page = DouyinModel::getDouyinUrl($id,$page);
                $urls[]= $next_page;
            }
            foreach($urls as $t_url){
                $host_url = parse_url($t_url);
                $newUrl[$host_url['path']] = $t_url;
            }
        }
        return $newUrl;
    }

    /**
    * @note 生成url
    *
    */
    public static function getDouyinUrl($id,$index=1) {
        if(!$id ){
            return false;
        }
        $url = StoreModel::replaceParam(Env::get('APICONFIG.DOUYIN_NEXT_URL'),'store_id',$id);
        $url = StoreModel::replaceParam($url,'page',$index);
        return $url;
    }

      /**
    * @note 获取请求所有的章节目录信息
    *
    * @param $urls array 章节URL
    * @param $txt_path string 路径
    * @param $title string 小说名称
    * @return array
    */
    public static function getDouyinChapterAllList($urls = [],$txt_path,$title){
        if(!$urls){
            return false;
        }
        global $urlRules;
        $urlList= [];
        $referer_url = "";
        foreach($urls as $val){
            $mobilePath = $val['link_url'] ?? '';
            $referer_url = NovelModel::urlHostData($val['chapter_link']);
            $pathRet = parse_url($val['chapter_link']);
            $path= $pathRet['path'];
            $chapterList[$mobilePath] = [
                //拼装移动端的地址
                'save_path'  =>  $txt_path . DS . md5($val['link_name']) . '.' . NovelModel::$file_type,
                'chapter_name'  =>  $val['chapter_name'],
                'chapter_link'  =>  $val['chapter_link'],
                'mobile_url'  => $val['chapter_link'], //兼容老数据
                'chapter_mobile_link' => $val['chapter_link'],
            ];
            $t_url[] = $val['chapter_link'];
        }
            

        $api_url =$newData= [];
        foreach($chapterList as $key =>$val){
            $num = range(1, 2); //固定就两页每个连接里
            foreach($num as $v){
                $cur_url = str_replace(".html", "_{$v}.html", $chapterList[$key]['chapter_link']);
                $pathData= parse_url($cur_url);
                $pathInfo = $pathData['path'];
                $api_url[$pathInfo]=$cur_url;

                 ##加工处理对应的列表方便进行跟踪
                $new_url = parse_url($cur_url);
                $newData = $chapterList[$key] ??[];
                $newData['chapter_link']= $cur_url;
                $newData['mobile_url']= $cur_url;
                $newData['chapter_mobile_link']= $cur_url;
                $new_chapter_data[$new_url['path']] = $newData;
                ##按照列表重新分割一个出来
            }
        }
        if(!$api_url){
            return [];
        }

        print_r("开始解析章节中的分页对应的页码数据信息---------------------\r\n");
        $multiList = array_chunk($api_url, self::$chunk_size);
          print_r("开始根据分页拉取相关章节内容 【total_nums = ".count($api_url)."】---------------------\r\n");
        $returnList= [];
        $reqTotal= count($multiList);

        $reqNum = 0;
        foreach($multiList as $gkey => $gval){
            $reqNum++;
            $list = DouyinModel::getHtmlContents($gval);
            $returnList = array_merge($returnList , $list);
            echo "******************请求具体章节内容分页信息【 ".$reqNum." / ".$reqTotal." 】*****************************\r\n";
        }
         #异常判断
        if(!$returnList){
            return [];
        }

         $response_content = [];
        if(!empty($returnList)){
            // $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_content'];
            foreach($returnList as $gk =>$gv){
                $html = $gv ?? '';
                $response_content[$gk] = $gv;
            }
        }
        $sortData= [];
        ##按照api_url的顺序重新进行递归不然存储章节会有问题乱的存储会出问题
        foreach($api_url as $xk =>$xv){
           if(isset($response_content[$xk])){
              $sortData[$xk] = $response_content[$xk] ?? '';
           }
        }
          #按照规则进行拆解
        $store_c = [];
        if(!empty($sortData)){
            foreach($sortData as $gkey =>$gval){
              $indexData = explode('/', $gkey);
              $dataStr = explode('_',$indexData[3]??'');
              $pathInfo = $dataStr[0] ?? '';
              if($pathInfo){
                  $indexData[3]= $pathInfo;
              }
              $anther_key = implode('/', $indexData) .('.html');
              $store_c[$anther_key][]=$gval;
            }
        }

        //过滤掉重复的选项
        $store_c = self::mergeRepeatData($store_c);

        $collectChapter = [];
        if(!empty($store_c)){
          foreach($store_c as $tk =>$tv){
              if(!$tk || !$tv)
                  continue;
              $build_string = implode('',$tv);
              $collectChapter[$tk] = $build_string;
          }
        }

        //返回对应的重塑主句
        $returnList = self::handleContents($referer_url,$chapterList,$collectChapter,$title);
        return $returnList;
    }

        /**
    * @note 重构技术基础局提那几
    *
    * @param $chapterList array 章节列表
    * @param $store_c array 返回的章节内容信息
    * @param $title 小说标题
    * @param $referer_url string 回调地址
    * @return array
    */

    public static function handleContents($referer_url,$chapterList,$store_c,$title){
        if(!$chapterList || !$store_c){
            return [];
        }
        foreach($chapterList as $key =>$val){
            $store_content = "";
            if(isset($store_c[$key])){
                $store_content = $store_c[$key];
            }
            #替换相关内容
            $store_content = NovelModel::replaceContent($store_content,$val['chapter_link'],$val['chapter_link'],$title);

             if (!$store_content || empty($store_content)) {
                $store_content = '当前章节为空，请稍后重试...';
            }
            $store_content = str_replace(array("\r\n", "\r", "\n"), "", $store_content);
            //替换文本中的P标签
            $store_content = str_replace("<p>", '', $store_content);
            $store_content = str_replace("</p>", "\n\n", $store_content); //如果内容是P标签，用P来替换
            $store_content = str_replace("<br>", "\n\n", $store_content); //如果内容是br标签用br来进行替换
            $store_content = str_replace("<br />","\n\n", $store_content);//如果内容是br /标签用br来进行替换
            //替换try{.....}cache的一段话JS这个不需要了,还有一些特殊字符
            $store_content = preg_replace('/{([\s\S]*?)}/', '', $store_content);
            $store_content = preg_replace('/try\scatch\(ex\)/', '', $store_content);
            $store_content = preg_replace('/content1()/', '', $store_content);
            $chapterList[$key]['content'] = $store_content;
        }
        return $chapterList;

    }


    /**
    * @note 合并重复数据信息
    *
    * @param $urls array 章节URL
    * @return array
    */
    public static function mergeRepeatData($store_c= []){
        if(empty($store_c)){
            return [];
        }
        $newList= [];
        foreach($store_c as $path =>$vals){
            $newArr = array_unique($vals);
            $newList[$path]  = $newArr;
        }
        return $newList;
    }


    /**
    * @note 获取html正文内容信息
    *
    * @param $urls array 章节URL
    * @return array
    */
    public static function getHtmlContents($urls=[]){
        if(!$urls){
            return false;
        }
        $source_url = reset($urls);   //获取小说源
        $rules = CommonService::collectContentRule($source_url);
            
        $items =[];
        $ql = QueryList::getInstance();
        $ql->use(CurlMulti::class);
        //or Custom function name
        $ql->use(CurlMulti::class,'curlMulti');
        $dataNum = [];
        foreach($urls as $key =>$val){
            $dataNum[$val] = $key;
        }
        $ql->curlMulti($urls)->success(function (QueryList $ql,CurlMulti $curl,$r)use(&$items,$dataNum,$rules){
            $url = $r['info']['url'];
            $num = $dataNum[$url] ?? 0;
            $hostData = parse_url($url);
            $urlPath  = $hostData['path'] ?? '';
            echo "async child-curl-process \t num={$num} \t url= {$url} \t code= {$r['info']['http_code']} \tstrlen= ".strlen($r['body'])."\r\n";
            $data = $ql->rules($rules)
                    ->query()
                    ->getData();
            $data = $data->all();
            $content = $data['content'] ?? '';
            if($urlPath){
                $items[$urlPath] = $content;
            }
            // $ite
            // 释放资源
            QueryList::destructDocuments();
        })->error(function ($errorInfo,CurlMulti $curl){
            echo "Current url:{$errorInfo['info']['url']} \r\n";
            print_r($errorInfo['error']);
        })->start([
            // 最大并发数，这个值可以运行中动态改变。
            'maxThread' => 6,
            // 触发curl错误或用户错误之前最大重试次数，超过次数$error指定的回调会被调用。
            'maxTry' => 3,
            // 全局CURLOPT_*
            'opt' => [
                 CURLOPT_HTTPGET => true, //
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_RETURNTRANSFER => 1
            ],
            // 缓存选项很容易被理解，缓存使用url来识别。如果使用缓存类库不会访问网络而是直接返回缓存。
            'cache' => ['enable' => false, 'compress' => false, 'dir' => null, 'expire' =>86400, 'verifyPost' => false]
        ]);
        return $items ?? [];
    }
}
?>