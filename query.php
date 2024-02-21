<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:利用phpquery进行数据采集
// ///////////////////////////////////////////////////

$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
require_once './vendor/autoload.php';
set_time_limit(500);
ini_set('memory_limit','5000M');

$target_url = 'http://www.souduw.com';
phpQuery::newDocumentFile($target_url);

//通过对应的标签来获取对应的状态数据信息
$list = pq('div.fengtui')->find('dl');
$i = 0;
$data_img_list = array();
foreach ($list as $item) {
   $i ++;
   $img = pq($item)->find('img')->attr('src');#小说的具体内容
   $title = pq($item)->find('h3')->text();#小说的标题
   $description = pq($item)->find('p')->text();#小说的描述
   $href = pq($item)->find('span a')->attr('href');#获取小说的连接
   $author = pq($item)->find('span a')->text();//获取作者名称
   // $img_src_list[] = $img;
   $data_img_list[] =[
        'img'   =>  $img,
        'title' =>$title,
        'description'   =>$description,
        'href'  =>  $href,
        'author'    =>  $author,
   ];
}
echo "<pre>";
print_R($data_img_list);
echo "</pre>";