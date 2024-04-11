<?php

// mb_convert_encoding( $str, $encoding1,$encoding2 );
$str='轉換編碼文字';
$encoding1='目標編碼';//，如utf-8,big5，大小寫均可
$encoding2 ='原始編碼';//如UTF-8,BIG5，大小寫均可

$string = "轉碼測試功蓋銹abc轉碼測試";

$result = mb_convert_encoding($string,"utf-8","big5");
echo '<pre>';
var_dump($result);
echo '</pre>';
exit;
echo mb_convert_encoding($str, "UTF-8"); //編碼轉換為utf-8
echo "\r\n";
echo mb_convert_encoding($str, "UTF-8", "BIG5"); //原始編碼為BIG5轉UTF-8
echo "\r\n";
echo mb_convert_encoding($str, "UTF-8", "auto"); //原始編碼不明，通過auto自動檢測，轉換UTF-8
echo "\r\n";
exit;

$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
require_once($dirname.'/library/file_factory.php');
require_once($dirname.'/library/process_url.php');

$sql = "select title,author from ims_novel_info where pro_book_id = 2226";
$info = $mysql_obj->fetch($sql,'db_slave');
//自动扫描再拉取一遍取读取
$aa =ProcessUrl::selfRunUrls($info);
echo "over\r\n";


?>