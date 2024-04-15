<?php

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once(__DIR__.'/library/init.inc.php');
use QL\QueryList;


$html = QueryList::get('https://www.xsw.tw/book/230000/',null,[
            'cache' => '/tmp/',
            'cache_ttl' => 87600 // 缓存有效时间，单位：秒，可以不设置缓存有效时间
        ])->getHtml();
$aa =traditionalCovert($html);
dd($aa);
echo '<pre>';
print_R($html);
echo '</pre>';
exit;
echo '<pre>';
print_R($rt);
echo '</pre>';
exit;

     echo '<pre>';
     print_R($item);
     echo '</pre>';
     exit;
//https://bapi.51daili.com/unlimitedip/getip?linePoolIndex=1&packid=17&time=5&qty=10&port=2&format=json&field=ipport,expiretime,regioncode,isptype&pid=6cb029dd7abb42a7b87e766d11132e43&usertype=17&uid=43558
$num =135;

$url  ='https://www.xsw.tw/book/12345';
$id = NovelModel::getUrlById($url);
echo '<pre>';
print_R($id);
echo '</pre>';
exit;

$t = ceil($num/10);
$list = [];
for ($i=0; $i <$t ; $i++) {
	 $info = webRequest('https://bapi.51daili.com/unlimitedip/getip?linePoolIndex=1&packid=17&time=5&qty=10&port=2&format=json&field=ipport,expiretime,regioncode,isptype&pid=6cb029dd7abb42a7b87e766d11132e43&usertype=17&uid=43558','GET');
	 var_dump($info);
	$data = json_decode($info,true);
	$return =$data['data'] ?? [];
	if(!$return) $return = [];
	$list = array_merge($list , $return);
	echo " num = " .($i+1). "\r\n";
	// sleep(1);
	// echo '<pre>';
	// print_R($return);
	// echo '</pre>';
	// echo "\r\n";
}
// exit;
echo '<pre>';
print_R($list);
echo '</pre>';
exit;

?>
