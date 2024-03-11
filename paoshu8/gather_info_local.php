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
$info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_novel_name);
$url = Env::get('APICONFIG.PAOSHU_API_URL'); //获取配置的域名信息
//删除旧数据，防止有新的进行抓取
function delete_chapter_data($store_id,$story_id,$table_name){
    if(!$store_id){
        return false;
    }
    global $mysql_obj;
    $sql = "delete from ".$table_name." where story_id = '".$story_id."'";
    $mysql_obj->query($sql,'db_master');
}

//清洗掉不需要的字段
function cleanData($items = [],$filter_key=[]){
    if(!$items ||!$filter_key) return false;
    $list = [];
    foreach($items as $key => $val){
        $info = [];
        foreach($val as $k =>&$v){
            //如果在过滤的字段里，直接切除
            if(!in_array($k , $filter_key)){
                $info[$k] = $v;
            }
        }
        $list[$key] = $info;
    }
    return $list;
}

if($info){
    $story_link = trim($info[0]['story_link']);//小说地址
    if($info[0]['is_async'] == 1){
        echo "url：---".$story_link."---当前数据已同步，请勿重复同步\r\n";
        exit();
    }

    //定义小说信息的抓取规则
    $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['info'];
    $files = Env::get('SAVE_HTML_PATH').DS.'detail_'.$info[0]['story_id'].'.'.NovelModel::$file_type;

    if(!$files){
        echo "no this story ---".$story_link."\r\n";
        exit();
    }
    $html = readFileData($files);
    if(!$html){
         //记录是否有相关的HTML的数据信息
        printlog('this novel：'.$story_link.' is no local html data');
        echo "no this story files： {$story_link}\r\n";
        exit();
    }
    //爬取相关规则下的类
    $info_data=QueryList::html($html)
                ->rules($rules)
                ->query()->getData();
    $store_data = $info_data->all();
    if(!empty($store_data)){

        $store_data['story_link'] = $story_link;
        $story_id = trim($info[0]['story_id']); //小说的id
        echo "url:".$story_link."||| story_id：".$story_id .PHP_EOL;
        //处理空字符串
        $location = str_replace("\r\n",'',$store_data['location']);
        $location =trim($location);
        $store_data['location'] = $location;
        $update_time  = str_replace('最后更新：','',$store_data['third_update_time']);
        $third_update_time = $update_time.' 00:00:00';
        $third_update_time = strtotime($third_update_time);
        $store_data['third_update_time'] = $third_update_time;
        $store_data['source'] = Env::get('APICONFIG.PAOSHU_STR');

        //转义标题
        $store_data['title'] = addslashes(trim($store_data['title']));
        //处理作者并转义
        $author_data = explode('：',$store_data['author']);
        $author = isset($author_data[1]) ?  addslashes(trim($author_data[1])) : '';
        $store_data['author']  = $author;

        $store_data['updatetime'] = time();
        //章节也需要处理特殊的转义字符
        $store_data['nearby_chapter'] = addslashes($store_data['nearby_chapter']);
        $intro = addslashes($store_data['intro']);//转义 特殊字符
        $intro = cut_str($intro,200); //切割字符串
        $store_data['intro'] = $intro;
        $store_data['tag'] = str_replace('小说','',$store_data['tag']);
        //执行更新操作
        if($info[0]['createtime'] == 0){
            $store_data['createtime']  = time();
        }
        //保存图片到本地
        NovelModel::saveImgToLocal($store_data['cover_logo'],$store_data['title'],$store_data['author']);


        //更新的条件
        $where_data = "story_id = '".$story_id."'";
        //同步小说的基础信息到线上mc_book表信息
        $sync_pro_id = NovelModel::exchange_book_handle($store_data,$mysql_obj);
        $store_data['pro_book_id'] = $sync_pro_id;
        if(!$sync_pro_id){
            printlog('未发现线上数据信息');
            exit();
        }

        //更新小说表的is_async为1，表示已经更新过了不需要重复更新
        $store_data['is_async'] = 1;

        //对比新旧数据返回最新的更新
        $diff_data = NovelModel::arrayDiffFiled($info[0]??[],$store_data);
        $mysql_obj->update_data($diff_data,$where_data,$table_novel_name);

        //获取相关的列表数据
        $rt = NovelModel::getCharaList($html);
        $item_list = $chapter_ids = $items= [];
        if(!empty($rt)){
            $now_time = time();
            //重新赋值进行计算
            $chapter_detal = $rt;
            //处理过滤章节名称里的特殊字符---按照名称进行存储，部分章节可能重名
            $chapter_detal = removeDataRepeat($chapter_detal);
            foreach($chapter_detal as $val){
                $link_url = trim($val['link_url']);
                $chapter_ret= explode('/',$link_url);
                $chapter_str=str_replace('.html','',$chapter_ret[2]);
                $chapter_id = (int) $chapter_str;
                $val['chapter_id'] = $chapter_id;//章节id
                $val['store_id'] = $info[0]['store_id']; //关联主表info里的store_id
                $val['story_id'] = $story_id;//小说的id
                $val['createtime'] = time();
                $val['novelid'] = $chapter_id;
                $items[$val['link_url']] = $val;
                $chapter_ids[$val['chapter_id']] = 1;
            }
            $sort_ids= array_keys($chapter_ids);
            //取出来章节
            $item_list = array_values($items);

        array_multisort($sort_ids , SORT_ASC , $item_list);
        //清洗掉不需要的字段
        $item_list = cleanData($item_list,['chapter_id']);
        //创建生成json目录结构
        NovelModel::createJsonFile($store_data,$item_list,0);
        //拼接章节目录信息
        $novel_list_path = Env::get('SAVE_NOVEL_PATH').DS.$sync_pro_id;
        //执行相关的章节批处理程序
         $update_id = $info[0]['store_id'] ?? 0;
        printlog('同步小说：'.$store_data['title'].'|基本信息数据完成--pro_book_id：'.$sync_pro_id.'--update_id：'.$update_id);
        $another_data = array_merge(
            [
                'pro_book_id'=>$sync_pro_id,//线上书籍ID
                'story_id'=>$story_id,//小说ID
                'syn_chapter_status'    =>$info[0]['syn_chapter_status'] ?? 0,//章节状态
            ],
            $store_data);
        //同步当前的章节的基础信息
        $factory->synChapterInfo($story_id,$another_data);//同步章节内容
        echo "now_time：".date('Y-m-d H:i:s')."\tself_store_id：".$update_id."\tpro_book_id：".$sync_pro_id."\tnovel_path：".$novel_list_path."\t当前小说：".$store_data['title']."|story_id=".$story_id." ---url：".$story_link."\t拉取成功，共更新章节目录：".count($item_list)."个\r\n";
        }else{
            //如果没有章节，把对应的章节也改成已处理
            $where_condition = "story_id = '".$story_id."'";
            $no_chapter_data['syn_chapter_status'] = 1;
            //对比新旧数据返回最新的更新
            $mysql_obj->update_data($no_chapter_data,$where_condition,$table_novel_name);
            printlog('未匹配到相关章节数据');
            echo "no chapter list\r\n";
        }
    }
}else{
    echo "no data";
}
$exec_end_time = microtime(true);
$endMemory = memory_get_peak_usage();
$memoryUsage = $endMemory - $startMemory;//内存占用情况
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "peak memory usage:" . $memoryUsage ." bytes \r\n";
echo "---------------------------------------------------------------------------------\r\n";

//处理抓取中按照章节名称返回
//将章节中的全角符号转换成英文
//过滤调一些特殊分符号
function removeDataRepeat($data){
    if(!$data) return false;
    foreach($data as $key=>$val){
        $chapter_name = trim($val['link_name']);
        //处理连接中的特殊字符
        $link_name = replaceCnWords($chapter_name);
        if(!empty($link_name)){
            $t[$link_name] = [
                'link_name' =>$link_name,
                'link_url'  =>$val['link_url']
            ];
        }
    }
    $t= array_values($t);
    //移除广告章节
    $list = NovelModel::removeAdInfo($t);
    return $list;

}
?>
