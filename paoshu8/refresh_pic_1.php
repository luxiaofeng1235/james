<?php
/*
 * 同步小说里的封面图到服务器上的图片信息
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
use Overtrue\Pinyin\Pinyin;
$pinyin = new  Pinyin(); //初始化拼音类
echo "start_time：".date('Y-m-d H:i:s') .PHP_EOL;
//检测代理
if(!NovelModel::checkProxyExpire()){
    exit("代理IP已过期，请重新拉取最新的ip\r\n");
}
$db_name = 'db_novel_pro';
$redis_key = 'img_pic_id';//redis的对应可以设置
// $redis_data->set_redis($redis_key,452);
$id = $redis_data->get_redis($redis_key);

$where_data = '  is_async = 1';
$limit= 50; //控制步长
$order_by =' order by pro_book_id asc';

if($id){
    $where_data .=" and pro_book_id > ".$id;
}
$sql = "select pro_book_id,title,author,cover_logo,story_link from ims_novel_info where ".$where_data;
$sql .= $order_by;
$sql .= " limit ".$limit;
echo "sql : " .$sql ."\r\n";
$info = $mysql_obj->fetchAll($sql,'db_slave');
if(!$info) $info = array();
$diff_data = array();

if(!empty($info)){
    //获取图片
    foreach($info as $value){

        $cover_logo = $value['cover_logo'] ?? '';
        $title = $value['title'] ?? '';
        $author = $value['author'] ?? '';
        $pro_book_id = intval($value['pro_book_id']);
        if(!$cover_logo || !$title || !$author) continue;

        //获取对应的名称信息,作为对象插入进去
        $img_name = NovelModel::getFirstImgePath($title,$author,$cover_logo ,$pinyin);
        //保存的图片路径信息
        $save_img_path  =  Env::get('SAVE_IMG_PATH') . DS. $img_name;
        if(!file_exists($save_img_path)){
        }else{
            $diff_data[] =[
                'title' => $title,
                'author'    =>$author,
                'link'      =>  $value['story_link'],
                'save_img_path'   =>  $save_img_path,
                'pro_book_id'    =>  $pro_book_id,
                'cover_logo'    =>  $cover_logo,
            ];
        }
    }
}else{
    echo "no data\r\n";
    exit();
}

$ids = array_column($info,'pro_book_id');
$max_id = max($ids);
$redis_data->set_redis($redis_key,$max_id);//设置增量ID下一次轮训的次数
echo "下次轮训的最大id起止位置 pro_book_id：".$max_id.PHP_EOL;

echo "ts_count exists img::".count($diff_data)."\r\n";
if(!empty($diff_data)){
    $num = 0;
    foreach($diff_data as $k => $v){
        $num++;
        $book_id = (int) $v['pro_book_id'];//图片ID
        $cover_logo =$v['cover_logo'] ??'';//图片的远程封面URL
        $save_img_path  = $v['save_img_path'] ?? '';//已经储存的图片路径
        $title = $v['title'] ??'';//标题
        $author = $v['author'] ??'';
        if(!$cover_logo) continue;
        //校验是否失败
        if (!@getimagesize($save_img_path)) {
            $t = NovelModel::saveImgToLocal($cover_logo , $title , $author,$pinyin);
            echo "index:{$num} 【本地图片】 pro_book_id : {$book_id} 损坏图片已修复 url: {$cover_logo} title：{$title}  author:{$author} path:{$save_img_path} \r\n";
        }else{
            echo "index:{$num} 【本地图片】 pro_book_id: {$book_id} title：{$title}  author:{$author}  path:{$save_img_path} 图片正常\r\n";
        }
    }
}
echo "count-num:".count($info)."\r\n";
echo "finish_time：".date('Y-m-d H:i:s') .PHP_EOL;
echo "over\r\n";
?>
