<?php
/*
 * 同步小说里的json文件信息
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;


echo "start_time：".date('Y-m-d H:i:s') .PHP_EOL;
$redis_key = 'json_refresh_store_id';//redis的对应可以设置
// $redis_data->set_redis($redis_key,57609);
// echo 33;die;
$id = $redis_data->get_redis($redis_key);
$where_data = '1 and pro_book_id>0';
$limit= 500; //控制列表的步长
$order_by =' order by pro_book_id asc';

if($id){
    $where_data .=" and pro_book_id > ".$id;
}

$sql = "select pro_book_id,store_id,title,story_id,story_link from ims_novel_info where ".$where_data;
$sql .= $order_by;
$sql .= " limit ".$limit;
echo "sql = {$sql}\n\n";
$info = $mysql_obj->fetchAll($sql,'db_slave');
if(!$info) $info = array();
if($info){
    $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['info'];
    foreach($info as $key => $value){
        $story_id = trim($value['story_id']);
        $story_link = trim($value['story_link']);
        $store_id = intval($value['store_id']);
        $title = trim($value['title']);
        $pro_book_id = intval($value['pro_book_id']);
        if(!$story_id) continue;


        //读取相关的内容信息
        $html = getHtmlData($story_id ,$story_link);
        $info_data=QueryList::html($html)
                    ->rules($rules)
                    ->query()->getData();
        $store_data = $info_data->all();

         //转义标题
        $store_data['title'] = addslashes(trim($store_data['title']));
        //处理作者并转义
        $author_data = explode('：',$store_data['author']);
        $author = isset($author_data[1]) ?  addslashes(trim($author_data[1])) : '';
        $store_data['author']  = $author;


        $novel_list_path = Env::get('SAVE_JSON_PATH'). DS . NovelModel::getAuthorFoleder($store_data['title'],$store_data['author']).'.' .NovelModel::$json_file_type;

        //处理配置信息
        $rt = NovelModel::getCharaList($html , $title);
        if(!empty($rt)){
            $chapter_detail = $rt;
            //去除章节里的首尾空格，并移除广告章节重组数据
            $chapter_detail =NovelModel::removeDataRepeatStr($chapter_detail);
            //处理相关数据
            $item_list = buildChapterList($chapter_detail , $value);
            //创建生成json文件信息
            NovelModel::createJsonFile($store_data,$item_list,0);
            echo "num =".($key+1)." \t title={$title} \t store_id = {$store_id}\t pro_book_id={$pro_book_id} \t url ={$story_link} \t path = {$novel_list_path} success \r\n";
        }else{
            echo "num =".($key+1)." \t title={$title} \t store_id ={$store_id} \t pro_book_id={$pro_book_id} \t url ={$story_link} 未匹配到有效素信息，也有可能是HTML页面不存在~~ \r\n";
        }
    }
}else{
    echo "no data \r\n";
}

$ids = array_column($info,'pro_book_id');
$max_id = max($ids);
$redis_data->set_redis($redis_key,$max_id);//设置增量ID下一次轮训的次数
echo "下次轮训的起止pro_book_id起止位置 pro_book_id：".$max_id.PHP_EOL;
echo "count-num:".$limit."\r\n";
echo "finish_time：".date('Y-m-d H:i:s') .PHP_EOL;
echo "over\r\n";

/**
* @note 处理当前的章节信息
* @param $chapter_detail array章节列表
* @return string
*/
function buildChapterList($chapter_detail= [],$info){
    $items = [];
    foreach($chapter_detail as $val){
        $link_url = trim($val['link_url']);
        $chapter_ret= explode('/',$link_url);
        $chapter_str=str_replace('.html','',$chapter_ret[2]);
        $chapter_id = (int) $chapter_str;
        $val['chapter_id'] = $chapter_id;//章节id
        $val['store_id'] = $info['store_id']; //关联主表info里的store_id
        $val['story_id'] = $info['story_id'];//小说的id
        $val['createtime'] = time();
        $val['novelid'] = $chapter_id;
        $val['link_str'] = $link_url;//兼容下面的定时处理
        $items[$val['link_url']] = $val;
        $chapter_ids[$val['chapter_id']] = 1;
    }
    $item_list = array_values($items);
    //清洗掉不需要的字段
    $item_list = NovelModel::cleanArrayData($item_list,['chapter_id']);
    return $item_list;
}

/**
* @note 获取当前的HTML信息
*
* @return string
*/

 function getHtmlData($story_id='',$story_link){
    if(!$story_id || !$story_link)
        return false;
    $files = Env::get('SAVE_HTML_PATH').DS.'detail_'.$story_id.'.'.NovelModel::$file_type;
    if(!file_exists($files)){
        return "no this story ---".$story_link."\r\n";
    }
    $html = readFileData($files);
    if(!$html){
         return "no this story files： {$story_link}\r\n";
    }
    return  $html;
}
?>