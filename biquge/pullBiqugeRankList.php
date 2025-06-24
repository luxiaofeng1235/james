<?php


/*
 * 笔趣阁榜单数据拉取 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$novel_table_name = Env::get('BQG.TABLE_NAME');
$db_conn = 'db_master';
$exec_start_time = microtime(true);
//获取榜单数据信息
$rankList = BiqugeModel::getRankList();
if($rankList){
	$index = 0;
	foreach($rankList as $key=> $value){
		$index = $key +1;
		if(!$value || !$value['page_id'])
			continue;
		$rank_name= $value['name']; //排行榜名称
		$page_id = $value['page_id'];
		echo "index=【{$index}】\trank_name= {$rank_name} \t page_id = {$page_id} \r\n";
		//拉取笔趣阁的书单信息
		$bookList = BiqugeService::getRankListByPageId($page_id);
		if(!empty($bookList)){
		    echo "book-total - num = ".count($bookList).PHP_EOL;
		    //同步关联的数据
		    BiqugeModel::synBookToLocalTable($bookList);
		}else{
			echo "index=【{$index}】\trank_name= {$rank_name}\t no book data\r\n";
		}
	}
	    
}else{
	echo "no data \r\n";
}
$exec_end_time = microtime(true);
$endMemory = memory_get_peak_usage();
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "finish\r\n";
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";

?>