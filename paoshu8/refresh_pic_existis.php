<?php
/*
 * 同步小说里的已存在的图片信息
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
$exec_start_time = microtime(true);

//检测代理
if(!NovelModel::checkImgKey()){
    exit("代理IP已过期，请重新拉取最新的ip\r\n");
}

$store_id = isset($argv[1])   ? $argv[1] : 0; //匹配对应的ID去关联
$db_name = 'db_novel_pro';
$redis_key = 'img_pic_id';//redis的对应可以设置
// $redis_data->set_redis($redis_key,500);
$id = $redis_data->get_redis($redis_key);

$where_data = '  is_async = 1';
$limit= 200; //控制图片拉取的步长
$order_by =' order by pro_book_id desc';

if($id){
    $where_data .=" and pro_book_id < ".$id;
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

echo "ts_count exists img::".count($diff_data)."\r\n";
$o_data  = [];
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
        $v['mobile_url'] = $cover_logo;
        if (!@getimagesize($save_img_path)) {
            $o_data[] = $v;
            //$t = NovelModel::saveImgToLocal($cover_logo , $title , $author,$pinyin);
            // echo "index:{$num} 【本地图片】 pro_book_id : {$book_id} 损坏图片已修复 url: {$cover_logo} title：{$title}  author:{$author} path:{$save_img_path} \r\n";
        }else{
             echo "index:{$num} 【本地图片】 pro_book_id: {$book_id} 图片正常  title：{$title}  author:{$author}  path:{$save_img_path} \r\n";
        }
    }
    if(!empty($o_data)){

        //下面是处理对应的为空的数据请求
        echo 'now is empty url init to async ...................'.PHP_EOL;
        //启用多线程去保存处理先关的数据
        $img_list = array_column($o_data,'cover_logo');
        $img_pro_type =5;

        $detail_proxy_type =4;//基础小说的代理IP
        $count_proxy_type= 2;//列表为空的代理IP
        $empty_proxy_type =3;//修补数据的代理IP
        $proxy_arr= array($detail_proxy_type, $count_proxy_type,$empty_proxy_type);
        $rand_str =$proxy_arr[mt_rand(0,count($proxy_arr)-1)];
        $data  =guzzleHttp::multi_req($img_list,'image');
        $t_num = 0;
        foreach($data as $gkey=> $img_con){
            $t_num++;
            $filename = $o_data[$gkey]['save_img_path'] ?? '';//保存的路径
            $img_link = $o_data[$gkey]['cover_logo'] ?? '';//小说封面
            $pro_book_id = $o_data[$gkey]['pro_book_id'] ?? 0;//第三方ID
            $title = $o_data[$gkey]['title'] ?? '';//小说标题
            $author = $o_data[$gkey]['author'] ?? ''; //小说作者
            $cover_logo = $o_data[$gkey]['cover_logo'] ?? ''; //小说封面
            //写入文件信息 ,需要判断图片是否还是存坏的
            // if (!@getimagesize($save_img_path)) {

            // }
            $rk = file_put_contents($filename , $img_con);
            //同步下来如果还是损坏的的说明网站的图已经坏了。
            if ( !@getimagesize($filename)) {
                  $img_default = readFileData(Env::get('NO_COVER_IMG_PATH'));
                  writeFileCombine($filename, $img_default);
                   echo "--下载后的图片在网站损坏,已用默认图替换\t";
             }
            echo "index:{$t_num} 【本地图片】 pro_book_id : {$pro_book_id} 损坏图片已修复 title：{$title}  author:{$author} \t url: {$cover_logo} \tpath:{$filename}  \r\n";
            sleep(1);

        }
    }
}

$ids = array_column($info,'pro_book_id');
$min_id = min($ids);
$redis_data->set_redis($redis_key,$min_id);//设置增量ID下一次轮训的次数
echo "下次轮训的起止pro_book_id起止位置 pro_book_id：".$min_id.PHP_EOL;

$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "原始数据长度:".count($info)."\r\n";
echo "finish_time：".date('Y-m-d H:i:s') .PHP_EOL;
echo "script execution time: ".sprintf('%.2f',($executionTime/60)) ." minutes \r\n";
echo "over\r\n";
?>
