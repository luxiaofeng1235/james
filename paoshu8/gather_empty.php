<?php
/*
 * 同步小说基本信息的主程序
 * 主要同步的数据有以下几个流程：
 * 1、同步ims_novel_info表信息和状态 --已实现
 * 2、下载图片到本地的指定目录 --已实现
 * 3、同步章节数据暂时放到ims_chapter表，后期采用json存储 -待完善
 * 4、同步线上mc_book数据比对--已实现
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
require_once dirname(__DIR__).'/library/file_factory.php';
require_once dirname(__DIR__).'/library/process_url.php';

echo "\r\n";
echo "---------------------------------------------------------------------------------\r\n";

use QL\QueryList;##引入querylist的采集器
$exec_start_time = microtime(true);
$startMemory = memory_get_peak_usage();
if(is_cli()){
    $store_id = $argv[1] ?? 0;
}else{
    $store_id  = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
}
if(!$store_id){
    echo '请选择要抓取的内容id';
    exit();
}


//实例化文件存储工厂类
$factory = new FileFactory($mysql_obj,$redis_data);
$table_novel_name =Env::get('APICONFIG.TABLE_NOVEL'); //小说基本信息表
$info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_novel_name);

$url = Env::get('APICONFIG.PAOSHU_API_URL'); //采集源配置的服务器地址
if($info){
    $story_link = trim($info[0]['story_link']);//小说地址 
    //替换一些绑定的无效的url进行处理
    $story_link = NovelModel::handleCollectUrl($story_link);
    $hostData= parse_url($story_link);
    //定义回调地址，主要确定是哪个采集源的referer
    $referer_url = $hostData['scheme']  . '://' . $hostData['host'];
    $story_id = trim($info[0]['story_id']);
    $ret_story_id = '';
    //获取采集源的一个标记
    $source_ref = NovelModel::getSourceUrl($story_link);

    #获取采集的url的小说ID
    $third_novel_id = CommonService::getCollectWebId($story_link);
    //判断是否匹配对应的含有_的path\
    $retMatch  = $third_novel_id ? array($third_novel_id) : '';
    //获取url里的ID信息
    $url_story_id = $retMatch[0] ??'';
        
    //防止story_id不一致，重新覆盖
    if(empty($story_id) || $story_id!= $url_story_id){
        $story_id = $url_story_id;
    }
    if($info[0]['is_async'] == 1){
        $factory->updateDownStatus($info[0]['pro_book_id']);
        echo "url：---".$story_link."---当前数据已同步，请勿重复同步11\r\n";
        NovelModel::killMasterProcess();//退出主程序
        exit();
    }

    $html_key = BanjishiModel::getCollectHtmlCacheKey($source_ref , $third_novel_id);
    echo "html_key的标记 key ={$html_key}\r\n";
    //网站的源文件夹
    $html_file = Env::get('SAVE_HTML_PATH').DS.$source_ref .'_detail_'.$story_id.'.'.NovelModel::$file_type;
    createFolders(dirname($html_file)); #创建目录
    echo "file_path = {$html_file} \r\n";
    $item_list  = [];
    $html = '';
    if(!preg_match('/paoshu8/',$story_link)){
        // $aa = $redis_data->ttl($html_key);
        $redis_data->del_redis($html_key);
        // echo 33;exit;
        //获取redis中的数据看是否已经过期，只有过期才去请求
        $html_status = $redis_data->get_redis($html_key);
        if( !$html_status){
            echo "**********************************\r\n";
            //如果当前文件不存在的话，直接从远端拉取数据，保留数据方便下次计算
            echo "now i will try get this contents : {$story_link}\r\n";
            echo "===========================================\r\n";
            echo "抓取 url对应的html_key ={$html_key} 未获取到缓存数据，重新走抓取流程\r\n";
            $data = webRequest($story_link,'GET');
                
            // //转换一下编码如果是乱码
            // $data =  array_iconv($data);
            writeFileCombine($html_file ,$data); #写入文件
            #记录已经记录的缓存key信息，下次不会重复抓取
            $redis_data->set_redis($html_key,1,BanjishiModel::$timeout);
        }else{
            echo "抓取 html_key ={$html_key} 有缓存数据，会自动读取file_path的数据\r\n";
        }
        $html = readFileData($html_file);
    }else{
        $ret_link = substr($story_link,-1,1);
        if($ret_link!='/'){
            $story_link .='/';
        }
        //paoshu8直接curl请求为了方便洗数据
        $html = webRequest($story_link,'GET');
    }
        
    $html = html_entity_decode($html);
    //判断转码处理
    if(in_array($source_ref,['bqg24','siluke520'])){
        //gbk需要转码
        $html = iconv('gbk', 'utf-8//ignore', $html);
    }
        
        
        
    if(!$html ){
        //记录是否有相关的HTML的数据信息
        printlog('this novel：'.$story_link.' is no local html data');
        echo "no this story files： {$story_link}\r\n";
        //更新为已同步防止重复同步
        $factory->updateStatusInfo($store_id);
        NovelModel::killMasterProcess();//退出主程序
        exit();
    }

    $is_fanti = $source_ref!='twking' ?  false : true; //定义是否为繁体
      //定义小说信息的抓取规则
    $rules = CommonService::collectListRule($story_link);
    //爬取相关规则下的类
    $info_data=QueryList::html($html)
                ->rules($rules)
                ->query()
                ->getData();
    $store_data = $info_data->all();
        
    //如果是繁体的话吗，就进行繁简体转换
    $is_fanti && $store_data = StoreModel::traverseEncoding($store_data);
    if(!empty($store_data)){
        $store_data['story_link'] = $story_link;
        echo "url:".$story_link."||| story_id：".$story_id .PHP_EOL;
        //处理空字符串
        $location = str_replace("\r\n",'',$store_data['location']);
        $location =trim($location);
        $store_data['location'] = $location;

        $third_update_time = strtotime($store_data['third_update_time']);
        $store_data['third_update_time'] = $third_update_time;
        //需要根据对应的节点来判断
        $store_data['source'] = $source_ref;
        //转义标题
        $store_data['title'] = trimBlankSpace($store_data['title']); //过滤前后空格
        $store_data['author']  = trimBlankSpace($store_data['author']); //过滤前后空格
        $store_data['title'] = htmlspecialchars($store_data['title'], ENT_QUOTES, 'UTF-8');//转义处理
        $store_data['author'] = htmlspecialchars($store_data['author'], ENT_QUOTES, 'UTF-8');//转义处理
            
        //章节也需要处理特殊的转义字符
        $store_data['nearby_chapter'] = addslashes($store_data['nearby_chapter']);
        $intro = addslashes($store_data['intro']);//转义 特殊字符
        // $intro = cut_str($intro,200); //切割字符串
        $intro = trimBlankSpace($intro);
        // $pattern = '/\s+/'; // 匹配空字符串
        // $replacement = ' '; // 替换为单个空格
        // $intro = preg_replace($pattern, $replacement, $intro);

        $store_data['intro'] = trimBlankLine($intro);
        $store_data['tag'] = str_replace('小说','',$store_data['tag']);
        //执行更新操作
        if($info[0]['createtime'] == 0){
            $store_data['createtime']  = time();
        }
        // echo "<pre>";
        // var_dump($store_data);
        // echo "</pre>";
        // exit();
            
        
        #获取对应的分页信息
        $allPage = $store_data['chapter_pages'] ?? [];
        //替换兼容采集器的一些字段规则
        $store_data = NovelModel::initStoreInfo($store_data);
        $store_data['story_id']  = $story_id; //重新覆盖story_id为当前的
        //判断如果作者或者作者没有就直接退出
        if(empty($store_data['author']) || empty($store_data['title'])){
            //更新小说的当前状态
            $notice_msg= "当前小说没有作者或标题，此小说{$store_data['title']} 不需要去同步了\r\n";
            echo $notice_msg;
            $factory->updateStatusInfo($store_id , $notice_msg);
            NovelModel::killMasterProcess();//退出主程序
            exit();
        }
        $is_exchange = false;
        //判断是否需要转换编码的字符集
        if( in_array($store_data['source'],['otcwuxi','xuges'])){
            $is_exchange = true;//转换字符集
        }
        #佳士小说处理逻辑
        if($source_ref == 'banjiashi'){
            #处理https://www.banjiashi.com/xiaoshuo/92166/这个目标站
            #特殊的目标站流程需要特殊判断弄下
            $rt = BanjishiModel::getAllBanjiashiChapter($third_novel_id,$store_data['title'],$allPage,$story_link);
        }else if($source_ref == 'douyinxs'){
            $rt = DouyinModel::getAllDouyinChapter($third_novel_id,$store_data['title'],$allPage);
        }else if($source_ref == 'bqg24'){
            $rt = Bqg24Model::getBqg24ChapterList($html,$store_data['title'],$story_link);
        }else{
            //获取相关的章节列表数据
            $rt = NovelModel::getCharaList($html,$store_data['title'] , $is_fanti , $is_exchange  ,$source_ref);
        }

        // dd($rt);

            

        // if(count($rt) < 100){ //章节如果过少，就不需要去同步了
        //     $notice_msg =  "当前小说章节过少，total={".count($rt)."} 请等待下次完善后再进行采集\r\n";
        //     echo $notice_msg;
        //     $factory->updateStatusInfo($store_id , $notice_msg);
        //     //如果采集的数据量过少把采集的更新时间批量更新下，防止每次数据都采集不完
        //     // $factory->updateLastUpdateTime($store_data['title'],$store_data['author']);
        //     NovelModel::killMasterProcess();//退出主程序
        //     exit();
        // }
            
        //保存图片到本地=
        $t= NovelModel::saveImgToLocal($store_data['cover_logo'],$store_data['title'],$store_data['author']);
        $item_list = $chapter_ids = $items= [];

        if(!empty($rt)){
            //由于当前的来源中没有最近更新的章节，所以手动处理下最后一章
            if($source_ref  =="xuges"){
                $return = array_slice($rt, -1, 1);
                $infoArr = $return[0] ?? [];
                $store_data['nearby_chapter'] = $infoArr['link_name'] ?? '';
            }
            $now_time = time();
            //重新赋值进行计算
            $chapter_detail = $rt;
            foreach($chapter_detail as $val){
                //如果章节名称为空，则不统计
                if(empty($val['link_name'])){
                    continue;
                }
                $link_url = trim($val['link_url']);
                $chapter_ret= explode('/',$link_url);
                $chapter_str=str_replace('.html','',$chapter_ret[2]??'');
                $chapter_id = (int) $chapter_str;
                $val['chapter_id'] = $chapter_id;//章节id
                $val['store_id'] = $info[0]['store_id']; //关联主表info里的store_id
                $val['story_id'] = $story_id;//小说的id
                $val['createtime'] = time();
                $val['novelid'] = $chapter_id;
                $val['link_str'] = $link_url;//兼容下面的定时处理
                $items[$val['link_url']] = $val;
                $chapter_ids[$val['chapter_id']] = 1;
            }
            $item_list = array_values($items);
            //清洗掉不需要的字段
            $item_list = NovelModel::cleanArrayData($item_list,['chapter_id']);
            if($source_ref == 'xuges'){
                $base_url = $story_link;//这个网站比较特殊，需要用其他的进行匹配
                $base_url = str_replace("index.htm", '',$base_url);
                $referer_url = $base_url;
            }
            //创建生成json目录结构
            NovelModel::createJsonFile($store_data,$item_list,0,$referer_url,$story_link);
        }else{
            //如果没有章节，把对应的章节也改成已处理
            $factory->updateStatusInfo($store_id);
            //更新首页的标记状态
            $factory->updateDownStatus($info[0]['pro_book_id']);

            printlog('未匹配到相关章节数据');
            echo "no chapter list\r\n";
            NovelModel::killMasterProcess();//退出主程序
        }

        $sync_pro_id = 0;//给一个默认值
         //执行相关的章节批处理程序
        $update_id = $store_id ?? 0;
        //更新的条件
        $where_data = "store_id = '".$store_id."'";
        //只有获取到章节才去处理小说并且同步到mc_book表操作
        if($item_list){
            //同步小说的基础信息到线上mc_book表信息
            $sync_pro_id = NovelModel::exchange_book_handle($store_data,$mysql_obj);
            $store_data['pro_book_id'] = $sync_pro_id;
            if(!$sync_pro_id){
                //更新小说同步状态
                $factory->updateStatusInfo($store_id);
                echo "未关联线上小说ID\r\n";
                NovelModel::killMasterProcess();//退出主程序
                printlog('未发现线上数据信息');
                exit();
            }

            //更新小说表的is_async为1，表示已经更新过了不需要重复更新
            //$store_data['is_async'] = 1;
            //对比新旧数据返回最新的更新
            //只有有数据才进行对比
            $diff_data = NovelModel::arrayDiffFiled($info[0]??[],$store_data);
            
            if(!empty($diff_data)){
                $diff_data['updatetime'] = time();
                $mysql_obj->update_data($diff_data,$where_data,$table_novel_name);
            }

            $novelData = array_merge([
                    'pro_book_id'=>$sync_pro_id,//线上书籍ID
                    'story_id'=>$story_id,//小说ID
                    'store_id'  => $info[0]['store_id'],
                    'syn_chapter_status'    =>$info[0]['syn_chapter_status'] ?? 0,//章节状态
                ],
            $store_data);
            //同步当前的章节的基础信息
            //同步章节内容
            $factory->synChapterInfo($story_id,$novelData);
        }else{
            //主要需要更新线上的对应的ID
            //更新小说同步状态
            $factory->updateStatusInfo($store_id);
            $pro_book_id  = intval($info[0]['pro_book_id']);
            //更新is_down的状态
            $pro_book_id>0 && $factory->updateDownStatus($pro_book_id);
            echo "此小说【".$store_data['title']."】  pro_book_id =".intval($pro_book_id)." \t暂无没有章节信息----------\r\n";
            NovelModel::killMasterProcess();//退出主程序
            exit();
        }
        if(!$item_list){
            $itemlist = [];
        }
        //获取小说的章节路径
        $novel_list_path = Env::get('SAVE_NOVEL_PATH'). DS . NovelModel::getAuthorFoleder($store_data['title'],$store_data['author']);
        printlog('同步小说：'.$store_data['title'].'|基本信息数据完成--pro_book_id：'.$sync_pro_id.'--update_id：'.$update_id);
        echo "now_time：".date('Y-m-d H:i:s')."\tlast_update_time：".date('Y-m-d H:i:s',$store_data['third_update_time'])."\tself_store_id：".$update_id."\tpro_book_id：".$sync_pro_id."\tnovel_path：".$novel_list_path."\t当前小说：".$store_data['title']."|story_id=".$story_id." ---url：".$story_link."\t拉取成功，共匹配到JOSN文件的章节数量：".count($item_list)."个\r\n";
        NovelModel::killMasterProcess();//退出主程序
    }
}else{
    NovelModel::killMasterProcess();//退出主程序
    echo "no data \r\n";
}
$exec_end_time = microtime(true);
$endMemory = memory_get_peak_usage();
$memoryUsage = $endMemory - $startMemory;//内存占用情况
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "peak memory usage:" . $memoryUsage ." bytes \r\n";
echo "---------------------------------------------------------------------------------\r\n";
?>
