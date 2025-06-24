<?php
/*
 * 更新热销的表的状态 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$sql ="update ims_novel_info_bak set is_async  = 0, syn_chapter_status = 0 where is_async = 1";
echo "update_sql = {$sql} \r\n";
$db_conn = "db_master";
$ret = $mysql_obj->query($sql,$db_conn);
echo "<pre>";
var_dump($ret);
echo "</pre>";
echo "finish_time：".date('Y-m-d H:i:s').PHP_EOL;

?>