<?php
/*
 * 同步小说的主程序
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
//取出来所有未同步的数据信息

$list =file('./1000_3000_txt.txt');
foreach($list as &$v){
    $v = str_replace("\r\n",'',$v);
}

$sql = "select store_id from ims_novel_info where is_async= 0 and  pro_book_id in (".implode(',',$list).")";
$item = $mysql_obj->fetchAll($sql,'db_slave');


$ids = implode(',',array_column($item, 'store_id'));


$sql = "select store_id from ims_novel_info where is_async = 0 and store_id in (".$ids.")";
echo $sql;

?>