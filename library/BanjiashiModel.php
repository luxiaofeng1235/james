<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :BanjishiModel.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:处理佳士小说的采集配置以及规则拉取
// ///////////////////////////////////////////////////
use QL\QueryList;
use Overtrue\Pinyin\Pinyin;

class BanjishiModel{

    private static  $method = 'post'; #指定请求方式
    private static  $chunk_size =200;//指定具体的页码分页长度最多请求200个
    public static   $timeout = 900; // 默认缓存过期时间为15分钟
    public static  $defaultimg=null; //默认封面


     /**
    * @note 获取默认图片问题
    * @param $id int id小说
    * @param $index int 索引id
    * @return array
    *
    */
    private static  function getDefaultImg(){
        self::$defaultimg = Env::get('APICONFIG.DEFAULT_PIC');
        return self::$defaultimg;
    }


    /**
    * @note 生成url
    * @param $id int id小说
    * @param $index int 索引id
    * @return array
    *
    */
    public static function getBanjiashiUrl($id,$index=1) {
        if(!$id ){
            return false;
        }
        $url = StoreModel::replaceParam(Env::get('APICONFIG.BANJIASHI_NEXT_URL'),'store_id',$id);
        $url = StoreModel::replaceParam($url,'page',$index);
        return $url;
    }


    /**
     * @note 生成章节访问的url信息
     * @param $id int 小说ID
     * @param $allPage int 总页码数量
     * @return array
     *
     */
    public static function createCHapterUrls($id = 0,$allPage = []){
        if(!$allPage){
            return [];
        }

        $base_url  = Env::get('APICONFIG.BANJIASHI_NEXT_URL');
        $newUrl = [];
        #处理获取的应用配置信息

        if(count($allPage)>1){
            for ($i=0; $i < count($allPage); $i++) {
                if ($i  == 0){
                    //第一页和后面的都不一样所以要单独拉取
                    $next_page =  StoreModel::replaceParam(Env::get('APICONFIG.BANJIASHI_DETAIL_URL'),'store_id',$id);
                }else{
                     $next_page = BanjishiModel::getBanjiashiUrl($id,$allPage[$i]);
                }
                $urls[]= $next_page;
            }
            foreach($urls as $t_url){
                $host_url = parse_url($t_url);
                $newUrl[$host_url['path']] = $t_url;
            }
        }else{
            $newUrl[] = $next_page =  StoreModel::replaceParam(Env::get('APICONFIG.BANJIASHI_DETAIL_URL'),'store_id',$id);
        }
        return $newUrl;
    }


    /**
     * @note 获取佳士小说的所有章节列表
     * @param $path string 路径信息
     * @param $title string 标题
     * @param $html string HTML内容
     * @return array
     *
     */
    public static function getBanjiashiChapter($path ="",$title="",$html=""){
        if(empty($html) || empty($html)){
            return false;
        }
        #处理标题转义字符
        $title = NovelModel::exchangePregStr($title);
        $preg_xioashuo = '/'.$title.' 章节列表.*<li class="now">/ism'; #匹配第一页
        $preg_next_page = '/<ul class="vlist">.*><div class="page_num">/ism'; #匹配下一页
        if (strpos($path,'xiaoshuo')){
            preg_match('/《' . $title . '》章节目录.*<\/ul><div class="title2">/ism', $html, $with_content);
            // preg_match($preg_xioashuo,$html,$with_content);
        }else{
            // preg_match($preg_next_page,$html,$with_content);
            //按照目录的翻页进行匹配
            preg_match('/<ul class=\"chapter_mu ycxsid\">.*<div class="title2">/ism', $html, $with_content);
        }

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
    * @note 获取佳士小说的ID-根据url来解析
    * @param $story_link string 根据连接地址解析获取目标网站ID
    * @return string
    *
    */
    public static function getBanjiashiId($story_link= ""){
        if(!$story_link){
            return 0;
        }
        $hostData= parse_url($story_link);
        $pathData = explode('/',$hostData['path']);
        $pathData = array_filter($pathData);
        $pathData = array_values($pathData);
        $third_novel_id = $pathData[1] ?? 0;
        return $third_novel_id;
    }

    /**
     * @note 获取采集HTML的key信息
     * @param $id int 小说ID（第三方的标识）
     * @param $source string 网站来源
     * @return string
     *
     */
    public static function getCollectHtmlCacheKey($source="",$id = ""){
        if(!$source || !$id){
            return false;
        }
        #缓存对应的html的key的标记
        #规则：book+来源+第三方id
        $html_key  =sprintf("book_%s_id:%s",$source,$id);
        return $html_key;
    }

    /**
     * @note 根据id和allPage来获取对应的分页的章节列表
     * @param $id int 小说ID（第三方的标识）
     * @param $title string  小说标题
     * @param $allPage int 所有分页内容 array对象
     * @param $story_link string 采集的url
     * @param $method 请求方式 ：默认为post去进行请求
     * @return array
     *
     */
    public static function getAllBanjiashiChapter($id,$title,$allPage,$story_link,$method='post'){
        if(!$id || !$title || !$allPage){
            return false;
        }
        $book_key ="banjiashi_id:".$id;
        echo "redis_key =【{$book_key}】 缓存过期时间为：timtout =【".self::$timeout." S】 \r\n";
        global $redis_data,$urlRules;
        $rules = CommonService::collectListRule($story_link);
        // $redis_data->del_Redis($book_key);

        $info = $redis_data->get_redis($book_key);
        if(!$info || empty($info) || $info=='[]'){
            #根据需要生成对应的url信息
            $urls = BanjishiModel::createCHapterUrls($id,$allPage);
            $t_url = array_values($urls);
            $chapterList = StoreModel::swooleRquest($t_url , $method);
            $goodsList = [];
            // echo '<pre>';
            // var_dump($chapterList);
            // echo '</pre>';
            // exit;
            echo "当前小说共 【 ".count($urls)." 】 页需要去拉取\r\n";
                
            foreach ($urls as $key =>$val){
                $host_data = parse_url($val);
                $path = $host_data['path'];
                if(isset($chapterList[$path])){
                    #处理图片存储问题，第一页的图片有问题需要重新覆盖一遍
                    if (strpos($path,'xiaoshuo')){
                        // $info_data=QueryList::html($chapterList[$path])
                        //     ->rules($rules)
                        //         ->query()
                        //         ->getData()
                        //         ->all();
                        //处理默认封面的问题，原始封面有点难看
                        //修复：由于https://www.banjiashi.com/这个网站的图片都是一些不合规的图，先用默认图代替
                        $info_data['cover_logo'] = self::getDefaultImg();
                        // if(strstr($info_data['cover_logo'], 'defaultimg')){
                        //     $info_data['cover_logo'] = self::getDefaultImg();
                        // }
                        #存储图片到对应的目录中去
                         if(isset($info_data['cover_logo'])){
                             $img_ret= BanjishiModel::saveImageNovelLocal($info_data['title'],$info_data['author'],$info_data['cover_logo']);
                             echo "下载图片到服务器地址：{$img_ret} \r\n";
                         }
                    }
                    $goodsList[$path]= $chapterList[$path] ?? '';
                }
            }
            $list = [];
            // unset($goodsList['/index/88934/1/']);
            if(count($goodsList)>0){
                foreach($goodsList as $key =>$val){
                    $chapter = BanjishiModel::getBanjiashiChapter($key,$title,$val);
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
    * @note 获取单个章节内容信息
    *
    * @param $contents array 获取拼接内容
    * @return array
    */
    public static function getChapterContent($contents=""){
        if (!$contents){
            return false;
        }
        global $urlRules;

        if(preg_match('/class=\"content\"/', $contents)){
            $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_content_new'];
        }else{
            #banjiashi_content_new
            $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_content'];
        }
        $data = QueryList::html($contents)
                ->rules($rules)
                ->query()
                ->removeHead()
                ->getData();
        $data = $data->all();
        return $data;
    }

    /**
    * @note 存储本地图片
    *
    * @param $title string 标题
    * @param $author string 作者
    * @param $cover_logo string标题
    * @return array
    */
    public static function saveImageNovelLocal($title,$author,$cover_logo){
        if(!$title || !$author || !$cover_logo){
            return false;
        }
        $pinyin_class = new Pinyin();
        $save_img_path = Env::get('SAVE_IMG_PATH');
        if (!is_dir($save_img_path)) {
            createFolders($save_img_path);
        }
        // $imgFileName = NovelModel::getFirstImgePath($title, $author, $cover_logo, $pinyin_class);
        #以日期创建文件夹返回的图片路径
        $imgFileName = NovelModel::getNovelToPic($title,$author,$cover_logo);
        if(!file_exists($imgFileName)){//如果不存在这个图才去更新
            $res = webRequest($cover_logo, 'GET'); //利用图片信息来下载
            if(empty($res)){
                #获取一张默认的远程头像
                $default_remote_img = self::getDefaultImg();
                echo "远程图片获取为空，会从".$default_remote_img."重新获取保存\r\n";
                $res = webRequest($default_remote_img,'GET');
            }
            $img_con = $res ?? '';
            #写入图片香公馆信息
            @writeFileCombine($imgFileName, $img_con);
        }
       return $imgFileName;
    }

    /**
    * @note 获取所有的拉取下来的数据信息
    *
    * @param  $url string 拉取的基本列表信息
    * @return  array
    */

    public static function getAllIndexList($url,$redis_key ='banjiashi_index_all'){
        if(!$url){
            return false;
        }

        global $redis_data,$urlRules;
        $index_item = $redis_data->get_redis($redis_key);
        if($index_item){
            echo "删除未过期的redis缓存数据重新抓取=====\r\n";
            $redis_data->del_redis($redis_key);
        }

        echo "设置的缓存过期时间为：timtout = {".self::$timeout." S} \r\n";
        if(!$index_item){
            $num = range(1, 10);
            foreach($num as $index){
                // $urls[]="{$url}rank/lastupdate/{$index}.html";
            }

            $new_url  = "{$url}list/6/1.html";
            // $rand_url= $urls[mt_rand(0,count($urls)-1)];
            $p_urls = array_merge([],[$new_url]); //随机取出来一条进行采集
            // array_unshift($urls, $url); //把首页的也加进来
            $source = NovelModel::getSourceUrl($url);
            $list = StoreModel::swooleRquest($p_urls,self::$method);
            $allData = [];
            foreach($list as $value){
                $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_index'];
                $range =  $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_range'];
                #查询出来最新的更新的列表
                $storyList = QueryList::html($value)
                        ->rules($rules)
                        ->range($range)
                        ->query()
                        ->getData();
                $storyList = $storyList->all();
                if($storyList){
                    foreach($storyList as $val){
                        $story_link = trim($val['story_link']);
                        $urlData = parse_url($story_link);
                        $path_result  = $urlData['path'];
                        $path_result = str_replace('/index/' , '', $path_result);
                        $path = preg_replace('/\//','_',$path_result);
                        $story_id = substr($path,  0 , -1);
                        $val['story_id'] = $story_id;
                        $val['source'] = $source;
                        $allData[]=$val;
                    }
                }
            }
            $redis_data->set_redis($redis_key,json_encode($allData),self::$timeout);
            return $allData;
        }else{
            $timer = $redis_data->ttl($redis_key);
            $avager_time = sprintf("%.2f",$timer/60);
            echo "我是redis中的缓存数据， 未过期，大概还有【{$avager_time}】 分到期\r\n";
            $ret = json_decode($index_item,true);
            return $ret ?? [];
        }
    }

     /**
    * @note 生成对象数据
    *
    * @param $urls array 章节URL
    * @param $title string 标题
    * @return array
    */

    public static function createIndexData($url,$redis_key ='banjiashi_index_all'){
        if(!$url){
            return false;
        }
        global $redis_data,$urlRules;
        // $redis_data->del_redis($redis_key);
        // echo 33;exit;
        // $tk = $redis_data->ttl($redis_key);
        // echo '<pre>';
        // var_dump($tk);
        // echo '</pre>';
        // exit;

        $index_item = $redis_data->get_redis($redis_key);
        if($index_item){
            echo "删除未过期的redis缓存数据重新抓取=====\r\n";
            $redis_data->del_redis($redis_key);
        }
        echo "设置的缓存过期时间为：timtout = {".self::$timeout." S} \r\n";
        if(!$index_item){
            echo "缓存过期，从首页的 base_url = 【{$url}】 直接获取最新数据\r\n";
            $source = NovelModel::getSourceUrl($url);
            $res = StoreModel::swooleRquest($url,self::$method);

            $index_data = array_values($res);
            $data = $index_data[0] ?? [];
            if($data){
                $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_index'];
                $range =  $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_range'];
                #查询出来最新的更新的列表
                $storyList = QueryList::html($data)
                        ->rules($rules)
                        ->range($range)
                        ->query()
                        ->getData();
                // echo '<pre>';
                // var_dump($storyList);
                // echo '</pre>';
                // exit;
                $storyList = $storyList->all();
                if(!empty($storyList)){
                    foreach($storyList as &$val){
                        if(!$val || !isset($val['story_link'])){
                            continue;
                        }
                        //解析获取story_id
                        $story_link = trim($val['story_link']);
                        $urlData = parse_url($story_link);
                        $path_result  = $urlData['path'];
                        $path_result = str_replace('/index/' , '', $path_result);
                        $path = preg_replace('/\//','_',$path_result);
                        $story_id = substr($path,  0 , -1);
                        $val['story_id'] = $story_id;
                        $val['source'] = $source;
                    }
                }
                $redis_data->set_redis($redis_key,json_encode($storyList),self::$timeout);
                return $storyList;
            }
        }else{
            $timer = $redis_data->ttl($redis_key);
            $avager_time = sprintf("%.2f",$timer/60);
            echo "我是redis中的缓存数据， 未过期，大概还有【{$avager_time}】 分到期\r\n";
            $ret = json_decode($index_item,true);
            return $ret ?? [];
        }

    }

      /**
    * @note 替换分页中的url信息返回
    *
    * @param $url array 连接地址
    * @return string
    */
    public static function replaceTrueUrl($url=''){
        if(!$url){
            return false;
        }
        $source_url = preg_replace('/\/1\\//','/',$url);
        $source_url = preg_replace('/index/','xiaoshuo',$source_url);
        return $source_url;
    }




    /**
    * @note 获取请求所有的章节目录信息
    *
    * @param $urls array 章节URL
    * @param $txt_path string 路径
    * @param $title string 小说名称
    * @param $method 指定是否为get还是post请求
    * @return array
    */
    public static function getChapterAllList($urls = [],$txt_path,$title,$method='post'){
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
            // $urlList[$path] =$val;
            $t_url[] = $val['chapter_link'];
        }
        print_r("开始解析章节中的分页对应的页码数据信息---------------------\r\n");
        #请求日志信息
        $res = StoreModel::swooleRquest($t_url,$method);

        #重试机制，防止页码出现问题
        $res = StoreModel::swooleCallRequest($res, $chapterList, $referer_url,$method);

        $api_url =$newData= [];

        foreach($res as $key =>$val){
            #单独获取内容的正则信息
            $detail = BanjishiModel::getChapterContent($val);
            $num = $detail['chapter_pager'] ??0;
            foreach($num as $v){
                $cur_url = str_replace(".html", "/{$v}.html", $chapterList[$key]['chapter_link']);
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
                // $res =
            }
        }
        if(!$api_url){
            return [];
        }


        // $request_url = array_values($api_url);
        $multiList = array_chunk($api_url, self::$chunk_size);
          // sleep(1); //休息一秒继续
        print_r("开始根据分页拉取相关章节内容 【total_nums = ".count($api_url)."】---------------------\r\n");
        $returnList= [];
        ##这里需要按照指定的长度进行分割，因为页码中有可能是100个分割了好几页有七八个URL，就按照默认200进行分割请求，然后同意合并数据，减少压力
        $reqTotal= count($multiList);
        $reqNum = 0;
        foreach($multiList as $gkey => $gval){
            $reqNum++;
            $list = StoreModel::swooleRquest(array_values($gval) ,$method);
                // echo '<pre>';
                // var_dump($list);
                // echo '</pre>';
                // exit;
            $list = StoreModel::swooleCallRequest($list, $new_chapter_data, $referer_url,$method);
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
                $html = BanjishiModel::getChapterContent($gv);
                $html = $html['content'] ?? '';
                $response_content[$gk] = $html;
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
              array_pop($indexData);
              $anther_key = implode('/', $indexData) .('.html');
              $store_c[$anther_key][]=$gval;
            }
        }

        #把正文数据组装好进行返回
        $collectChapter = [];
        if(!empty($store_c)){
          foreach($store_c as $tk =>$tv){
              if(!$tk || !$tv)
                  continue;
              $build_string = implode('',$tv);
              $collectChapter[$tk] = $build_string;
          }
        }
        #重构数据
        $returnList = self::buildContents($referer_url,$chapterList,$collectChapter,$title);
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

    public static function buildContents($referer_url,$chapterList,$store_c,$title){
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
}
?>