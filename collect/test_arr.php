<?php
ini_set("memory_limit", "5000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');



$sql ="select chapter_id,link_url from ims_chapter where novelid =0 limit 60000";
$list = $mysql_obj->fetchAll($sql,'db_master');
foreach($list as $key =>$val){
   $urls = explode('/',$val['link_url']);
   $id = $urls[2] ?? '';
   $novelid =(int) str_replace('.html','',$id);
   $sql ="update ims_chapter set novelid =".$novelid." where chapter_id = ".$val['chapter_id']." limit 1";
  $mysql_obj->query($sql,'db_master');
  echo "chapter_id=".$val['chapter_id']."===================url:".$val['link_url']."\r\n";
}
echo "over\r\n";
?>