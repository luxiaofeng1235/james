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


$target_url = 'http://www.jinxiang.com/brandmap.html';
phpQuery::newDocumentFile($target_url);
$list = pq('div#brandList')->find('li');
$i = 0;
$img_src_list = array();
foreach ($list as $item) {
   $i ++;
   $img = pq($item)->find('img')->attr('src');
   $img_src_list[] = $img;
}
var_dump($img_src_list);