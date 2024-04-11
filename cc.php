<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
// header('Content-Type: text/html; charset=big5');


$content = webRequest('https://www.xsw.tw/fenlei2_1.html','GET');


// $t =readFileData('./new.txt');
$str = iconv('big5','utf8',$content);
$od = opencc_open("tw2sp.json");
$text = opencc_convert($str, $od);
echo $text;
echo "\r\n";
opencc_close($od);

?>