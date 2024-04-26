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

$store_id = isset($argv[1])   ? $argv[1] : 0; //匹配对应的ID去关联
$db_name = 'db_novel_pro';
$redis_key = 'img_pic_id';//redis的对应可以设置
$id = $redis_data->get_redis($redis_key);

$where_data = '  is_async = 1 and pic is not null ';
$limit= 5000; //控制图片拉取的步长
$order_by =' order by pro_book_id desc';

if($id){
    $where_data .=" and pro_book_id < ".$id;
    // $where_data .= " and pro_book_id = 125119";
}
$sql = "select pro_book_id,i.title,m.author,cover_logo,story_link,m.pic from book_center.ims_novel_info as i left join novel.mc_book m on i.pro_book_id = m.id  where ".$where_data;
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
        $title = trimBlankSpace($value['title']); //去除收尾空格
        $author = trimBlankSpace($value['author']);//去除收尾空格
        $pro_book_id = intval($value['pro_book_id']);
        if(!$cover_logo || !$title) continue;

        //获取对应的名称信息,作为对象插入进去
        // $img_name = NovelModel::getFirstImgePath($title,$author,$cover_logo ,$pinyin);
        $img_name = $value['pic']; //用这个当图片名
        //保存的图片路径信息
        $save_img_path  =  $img_name;
        if(file_exists($save_img_path)){
            //获取图片尺寸
             $imgSize =getImageSpace($save_img_path);
             //如果小于10KB的话就直接拿出来重新下载
             if($imgSize>0 && $imgSize < 3){
                $diff_data[] =[
                    'title' => $title,
                    'author'    =>$author,
                    'link'      =>  $value['story_link'],
                    'save_img_path'   =>  $save_img_path,
                    'pro_book_id'    =>  $pro_book_id,
                    'cover_logo'    =>  $cover_logo,
                    'size'  =>  $imgSize.'KB',
                ];
             }
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
         $o_data[] = $v;
        // if (!@getimagesize($save_img_path)) {

        //     //$t = NovelModel::saveImgToLocal($cover_logo , $title , $author,$pinyin);
        //     // echo "index:{$num} 【本地图片】 pro_book_id : {$book_id} 损坏图片已修复 url: {$cover_logo} title：{$title}  author:{$author} path:{$save_img_path} \r\n";
        // }else{
        //      echo "index:{$num} 【本地图片】 pro_book_id: {$book_id} 图片正常  title：{$title}  author:{$author}  path:{$save_img_path} \r\n";
        // }
    }
    if(!empty($o_data)){

        //下面是处理对应的为空的数据请求
        echo 'now is repeat image init to async num = '.count($o_data).'...................'.PHP_EOL;
        //启用多线程去保存处理先关的数据
        // $img_list = array_column($o_data,'cover_logo');
        // $data  =curl_pic_multi::Curl_http($img_list,1);
        $t_num = 0;
        foreach($o_data as $gkey=> $gval){
            $t_num++;


            $filename = $o_data[$gkey]['save_img_path'] ?? '';//保存的路径
            $img_link = $o_data[$gkey]['cover_logo'] ?? '';//小说封面
            $pro_book_id = $o_data[$gkey]['pro_book_id'] ?? 0;//第三方ID
            $title = $o_data[$gkey]['title'] ?? '';//小说标题
            $author = $o_data[$gkey]['author'] ?? ''; //小说作者
            $cover_logo = $o_data[$gkey]['cover_logo'] ?? ''; //小说封面
            $img_con = webRequest($gval['cover_logo'],'GET');
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
             $size =getImageSpace($filename);
            echo "index:{$t_num} 【本地图片】 pro_book_id : {$pro_book_id} 损坏图片已修复 title：{$title}  author:{$author} \t url: {$cover_logo} \tpath:{$filename} \t size:{$size} KB \r\n";
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
