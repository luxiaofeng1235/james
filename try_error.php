<?php
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