<?php
/*
 * 同步小说里的html文件信息
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
$redis_key = 'json_refresh_url_id';//redis的对应可以设置
$id = $redis_data->get_redis($redis_key);
// $redis_data->set_redis($)
$where_data = '1 and pro_book_id>3000';
$limit= 500; //控制列表的步长
$order_by =' order by pro_book_id asc';

if($id){
    $where_data .=" and pro_book_id > ".$id;
}

$sql = "select pro_book_id,author,store_id,title,story_id,story_link from ims_novel_info where ".$where_data;
$sql .= $order_by;
$sql .= " limit ".$limit;
echo "sql = {$sql}\n\n";
$info = $mysql_obj->fetchAll($sql,'db_slave');
if(!$info) $info = array();
$diff_data = [];
if($info){

    foreach($info as $key => $value){
        $story_id = trim($value['story_id']);
        $story_link = trim($value['story_link']);
        $store_id = intval($value['store_id']);
        $author = $value['author'] ?? '';
        $title = trim($value['title']);
        $pro_book_id = intval($value['pro_book_id']);
        if(!$story_id) continue;
        //读取相关的内容信息
        $html = getHtmlData($story_id ,$story_link);
        //说明当前的目录时空的
        if(strstr($html, 'no this story')){
             $diff_data[] =[
                'title' => $title,
                'author'    =>$author,
                'link'      =>  $value['story_link'],
                'pro_book_id'    =>  $pro_book_id,
            ];
        }else{
              echo "index:".($key+1)." 【本地页面】 pro_book_id: {$pro_book_id}   title：{$title}  author:{$author}  url ={$story_link} exists \r\n";
        }
    }
    echo "ts_count to curl html::".count($diff_data)."\r\n";
    if(!empty($diff_data)){
        $urls = array_column($diff_data,'link');
        $finalList = curl_pic_multi::Curl_http($urls,1);
        $rules =  [
            'link'       => ['meta[property=og:novel:read_url]','content'],
        ];
        foreach($finalList as $ck =>$cv){
            if(!$cv) continue;
            $data = QueryList::html($cv)->rules($rules)->query()->getData();
            $html = $data->all();
            $new_story_id  = substr($html['link'],1,-1);
            if(!$new_story_id) continue;
            $save_path = Env::get('SAVE_HTML_PATH').DS.'detail_'.$new_story_id.'.'.NovelModel::$file_type;
            $items[$save_path] = $cv;
        }
        if(!empty($items)){
            $num = 0;
             foreach($items as $filename =>$html_data){
                 $num++;
                 writeFileCombine($filename , $html_data);//写入文件
                 echo "num = ".$num." \tsave_path ={$filename} success \r\n";
             }
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