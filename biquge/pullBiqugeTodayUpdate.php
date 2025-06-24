<?php
/*
 * 笔趣阁囧今日更新数据拉取 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$exec_start_time = microtime(true);

$withRange = range(1,15);

foreach($withRange as $page){
	echo "current page = {$page}\r\n";
	//获取每日新书推荐
	$todayUpdateList = BiqugeService::getHostNewBookeList($page);
	echo "book-count-num = "  . count($todayUpdateList). "\r\n";
	if($todayUpdateList){
		//同步执行同步的脚本数据信息
		 BiqugeModel::synBookToLocalTable($todayUpdateList);
	}
}

$exec_end_time = microtime(true);
$endMemory = memory_get_peak_usage();
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "finish\r\n";
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>