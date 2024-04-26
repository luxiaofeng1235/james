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
 * @version 0.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
require_once dirname(__DIR__).'/library/file_factory.php';
require_once dirname(__DIR__).'/library/process_url.php';

echo "\r\n";
echo "---------------------------------------------------------------------------------\r\n";

$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
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
//先从redis取，没有走数据库
$info = NovelModel::getRedisBookDetail($store_id);
if(empty($info)){
    $info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_novel_name);
}
$url = Env::get('APICONFIG.PAOSHU_API_URL'); //采集源配置的服务器地址
if($info){
    $story_link = trim($info[0]['story_link']);//小说地址
    $hostData= parse_url($story_link);
    $referer_url = $hostData['scheme']  . '://' . $hostData['host'];
    $story_id = trim($info[0]['story_id']);
    if($info[0]['is_async'] == 1){
        $factory->updateDownStatus($info[0]['pro_book_id']);
        echo "url：---".$story_link."---当前数据已同步，请勿重复同步11\r\n";
        NovelModel::killMasterProcess();//退出主程序
        exit();
    }

    //定义小说信息的抓取规则
    $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['info_replace'];
    //网站的源文件夹
    $html_file = Env::get('SAVE_HTML_PATH').DS.'detail_'.$story_id.'.'.NovelModel::$file_type;
    echo "file_path = {$html_file} \r\n";
    $item_list  = [];
    if(!$html_file || !file_exists($html_file)){
        echo "no this story ---".$story_link."\r\n";

        echo "**********************************\r\n";
        //如果当前文件不存在的话，直接从远端拉取数据，保留数据方便下次计算
        echo "now i will try get this contents : {$story_link}\r\n";
        $data = webRequest($story_link,'GET');
        //转换一下编码如果是乱码
        $data =  array_iconv($data);
        writeFileCombine($html_file ,$data);
        // NovelModel::killMasterProcess();//退出主程序
        // exit();
    }
    $html = readFileData($html_file);
    $html = html_entity_decode($html);

    if(!$html ){
        //记录是否有相关的HTML的数据信息
        printlog('this novel：'.$story_link.' is no local html data');
        echo "no this story files： {$story_link}\r\n";
        //更新为已同步防止重复同步
        $where_condition = "story_id = '". $story_id."'";
        $no_chapter_data['syn_chapter_status'] = 1;
        $no_chapter_data['is_async'] = 1;
        //对比新旧数据返回最新的更新
        $mysql_obj->update_data($no_chapter_data,$where_condition,$table_novel_name);
        $factory->updateDownStatus($info[0]['pro_book_id']);
        NovelModel::killMasterProcess();//退出主程序
        exit();
    }

    // $arr =iconv('gbk', 'UTF-8', $html);
    // echo '<pre>';
    // print_R($arr);
    // echo '</pre>';
    // exit;

    //爬取相关规则下的类
    $info_data=QueryList::html($html)
                ->rules($rules)
                ->query()
                ->getData();
    $store_data = $info_data->all();
    // $arr = array_iconv($store_data['author']);
    // echo '<pre>';
    // print_R($arr);
    // echo '</pre>';
    // exit;
    if(!empty($store_data)){


        //判断是其他站过来的
        if(strpos($story_link,'xs74w') &&  !strpos($store_data['cover_logo'] ,'https')){
            $store_data['cover_logo'] = Env::get('APICONFIG.PAOSHU_NEW_HOST'). $store_data['cover_logo'];
        }

        $store_data['story_link'] = $story_link;
        echo "url:".$story_link."||| story_id：".$story_id .PHP_EOL;
        //处理空字符串
        $location = str_replace("\r\n",'',$store_data['location']);
        $location =trim($location);
        $store_data['location'] = $location;
        $third_update_time = strtotime($store_data['third_update_time']);
        $store_data['third_update_time'] = $third_update_time;

        //需要根据对应的节点来判断
        $store_data['source'] = NovelModel::getSourceUrl($store_data['story_link']);
        //转义标题

        $store_data['title'] = trimBlankSpace($store_data['title']); //过滤前后空格
        $author = trimBlankSpace($store_data['author']); //过滤前后空格
        $store_data['author']  = $author;

        //章节也需要处理特殊的转义字符
        $store_data['nearby_chapter'] = addslashes($store_data['nearby_chapter']);
        $intro = addslashes($store_data['intro']);//转义 特殊字符
        $intro = cut_str($intro,200); //切割字符串
        $store_data['intro'] = trimBlankLine($intro);
        $store_data['tag'] = str_replace('小说','',$store_data['tag']);
        //执行更新操作
        if($info[0]['createtime'] == 0){
            $store_data['createtime']  = time();
        }

        //替换兼容采集器的一些字段规则
        $store_data = NovelModel::initStoreInfo($store_data);
        //获取相关的列表数据
        $rt = NovelModel::getCharaList($html,$store_data['title']);

        if(count($rt)<=20){ //章节如果过少，就不需要去同步了
            $where_condition = "story_id = '".$story_id."'";
            $no_chapter_data['syn_chapter_status'] = 1;
            $no_chapter_data['is_async'] = 1;
            //对比新旧数据返回最新的更新
            $mysql_obj->update_data($no_chapter_data,$where_condition,$table_novel_name);
            echo "当前小说章节过少，请等待下次完善后再进行采集\r\n";
            NovelModel::killMasterProcess();//退出主程序
            exit();
        }

        // //保存图片到本地==暂时屏蔽不需要
        $t= NovelModel::saveImgToLocal($store_data['cover_logo'],$store_data['title'],$store_data['author']);
        $item_list = $chapter_ids = $items= [];
        if(!empty($rt)){
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
                $chapter_str=str_replace('.html','',$chapter_ret[2]);
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
            //创建生成json目录结构
            NovelModel::createJsonFile($store_data,$item_list,0,$referer_url);
        }else{
            //如果没有章节，把对应的章节也改成已处理
            $where_condition = "story_id = '".$story_id."'";
            $no_chapter_data['syn_chapter_status'] = 1;
            $no_chapter_data['is_async'] = 1;
            //对比新旧数据返回最新的更新
            $mysql_obj->update_data($no_chapter_data,$where_condition,$table_novel_name);
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
        $where_data = "story_id = '".$story_id."'";
        //只有获取到章节才去处理小说并且同步到mc_book表操作
        if($item_list){
            //同步小说的基础信息到线上mc_book表信息
            $sync_pro_id = NovelModel::getRedisProId($store_id);
            //默认先查redis缓存里的
            if(empty($sync_pro_id)){
                $sync_pro_id = NovelModel::exchange_book_handle($store_data,$mysql_obj);
            }
            $store_data['pro_book_id'] = $sync_pro_id;
            if(!$sync_pro_id){

                $where_condition = "story_id = '".$story_id."'";
                $no_chapter_data['syn_chapter_status'] = 1;
                $no_chapter_data['is_async'] = 1;
                //对比新旧数据返回最新的更新
                $mysql_obj->update_data($no_chapter_data,$where_condition,$table_novel_name);

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

            $another_data = array_merge(
            [
                'pro_book_id'=>$sync_pro_id,//线上书籍ID
                'story_id'=>$story_id,//小说ID
                'store_id'  => $info[0]['store_id'],
                'syn_chapter_status'    =>$info[0]['syn_chapter_status'] ?? 0,//章节状态
            ],
            $store_data);
            //同步当前的章节的基础信息
            //同步章节内容
            $factory->synChapterInfo($story_id,$another_data);
        }else{

            //主要需要更新线上的对应的ID
            $storeInfo = $mysql_obj->fetch("select pro_book_id from ".$table_novel_name." where store_id ={$store_id}",'db_slave');
            $pro_book_id = intval($storeInfo['pro_book_id']);
            //这里面防止解析为空去更新对应的状态
            $where_condition = "story_id = '".$story_id."'";
            $no_chapter_data['syn_chapter_status'] = 1;
            $no_chapter_data['is_async'] = 1;
            //更新is_down的状态
            $pro_book_id>0 && $factory->updateDownStatus($pro_book_id);
            //对比新旧数据返回最新的更新
            $mysql_obj->update_data($no_chapter_data,$where_condition,$table_novel_name);
            echo "此小说【".$store_data['title']."】  pro_book_id =".intval($info[0]['pro_book_id'])." \t暂无没有章节信息----------\r\n";
            NovelModel::killMasterProcess();//退出主程序
            exit();
        }
        if(!$item_list){
            $itemlist = [];
        }
        //获取小说的章节路径
        $novel_list_path = Env::get('SAVE_NOVEL_PATH'). DS . NovelModel::getAuthorFoleder($store_data['title'],$store_data['author']);
        printlog('同步小说：'.$store_data['title'].'|基本信息数据完成--pro_book_id：'.$sync_pro_id.'--update_id：'.$update_id);
        echo "now_time：".date('Y-m-d H:i:s')."\tself_store_id：".$update_id."\tpro_book_id：".$sync_pro_id."\tnovel_path：".$novel_list_path."\t当前小说：".$store_data['title']."|story_id=".$story_id." ---url：".$story_link."\t拉取成功，共匹配到JOSN文件的章节数量：".count($item_list)."个\r\n";
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
