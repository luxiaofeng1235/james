<?php
/*
 * 笔趣阁同步每天的更新数据信息 ，从凌晨0点开始计算更新的数据信息
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 // */

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$exec_start_time = microtime(true);
$date = date('Y-m-d H:00:00',strtotime('-1 hour'));
$redis_key ="xhs_datainfo";
$id = $redis_data->get_redis($redis_key);
//只查询连载中的书
$condition  ="is_open =1 and  status!=1 and  (created_at>='{$date}' or updated_at>='{$date}' )";
// $condition ="is_open =1 and form=1 and last_site_id='xhs' and  updated_at>='2024-01-01 00:00:00'";
// if($id){
// 	$condition .= " and id> ".intval($id);
// }


$sql = "select id,name,author,last_chapter_name,last_site_id,ltype from book where ".$condition;    
$limit= 1000; //控制列表的步长
$sql .=" order by id asc";
$sql .=" limit {$limit}";
echo "sql : " .$sql ."\r\n";
$lists = $mysql_obj->fetchAll($sql,'db_bqg_source');

if($lists){
	$bookList  = [];
	foreach($lists as $key =>$val){
		$bookId = intval($val['id']);
		// if(!$val || !$sid){
		// 	continue;
		// }
		//处理输入字符并进行mysql的转义
		$name = BiqugeModel::stringHandleInput($val['name']);
		$author = BiqugeModel::stringHandleInput($val['author']);
		$bookList[]=[
			'site_id'	=>$val['last_site_id'],
			'book_id'	=> $bookId,//小说书的ID
			'name'	=> $name,
			'author'	=>$author,//小说作者
			'image'=>trim($val['image']),
			'ltype'	=> $val['ltype'] ?? 0,
		];	
	}
    
	    
	    
	    
	//获取最大的值
	if($bookList){
		//记录增加量的最大ID

		echo "同步下面的数据到ims_biquge_info中\r\n";
		echo "<pre>";
		//同步笔趣阁的基础数据细腻
		BiqugeModel::synBookToLocalTable($bookList);

		// $ids = array_column($bookList,'book_id');
		// $max_id = max($ids);
		// $redis_data->set_redis($redis_key,$max_id);//设置增量ID下一次轮训的次数

		// echo "current sql ={$sql}\r\n";
		// echo "********************************************************************************************\r\n";
		//  echo "下次轮训的最大id起止位置 biquge_book_id：".$max_id.PHP_EOL;
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