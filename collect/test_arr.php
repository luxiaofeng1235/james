<?php
ini_set("memory_limit", "5000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

$sql = "select story_id from ims_novel_info ";
$list = $mysql_obj->fetchAll($sql,'db_slave');
foreach($list as $key =>$val){
    $sql = "select count(1) as num from ims_chapter where story_id ='".$val['story_id']."' limit 1";
    $info = $mysql_obj->fetch($sql,'db_slave');
    $ts_count = $info['num'] ?? 0;
    if(!$ts_count){
        echo "story_id：".$val['story_id']."</br>";
    }
}
echo "over\r\n";
?>