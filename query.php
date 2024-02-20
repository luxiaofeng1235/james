<?php

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
// phpQuery::newDocumentFile('http://news.sina.com.cn/china');
// $aa= pq(".main-text li a")->text();
// $dd = pq(".main-text li a")->attr('href');
// echo '<pre>';
// var_dump($dd);
// echo '</pre>';
// exit;
// die;

// $html = webRequest('https://www.souduw.com/api/novel/chapter/transcode.html?novelid=320105&chapterid=5&page=1','GET',[],[]);
// $doc = phpQuery::newDocument($html,'utf-8'); //创建html对象