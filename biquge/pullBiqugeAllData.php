<?php
/*
 * 笔趣阁导入所有书籍数据信息
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

$redis_key = 'biquge_incr_id';//redis的对应可以设置
$id = $redis_data->get_redis($redis_key);
$condition = "is_open =1 and form = 1 and last_site_id!=''";
if($id){
	$condition .= " and id > ".intval($id);
}
// $redis_data->set_redis($redis_key,5000);

    
$sql = "select id,name,author,image,ltype from book where {$condition} order by id asc";
$limit= 1000; //控制列表的步长
$sql .=" limit {$limit}";
echo "sql : " .$sql ."\r\n";
$lists = $mysql_obj->fetchAll($sql,'db_bqg_source');
if($lists){
	$bookList  = [];
	foreach($lists as $key =>$val){
		if(!$val || !isset($val['id'])){
			continue;
		}
		//处理输入字符并进行mysql的转义
		$name = BiqugeModel::stringHandleInput($val['name']);
		$author = BiqugeModel::stringHandleInput($val['author']);
		$bookList[]=[
			'book_id'	=> intval($val['id']),//小说书的ID
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

		$ids = array_column($bookList,'book_id');
		$max_id = max($ids);
		$redis_data->set_redis($redis_key,$max_id);//设置增量ID下一次轮训的次数

		echo "current sql ={$sql}\r\n";
		echo "********************************************************************************************\r\n";
		echo "下次轮训的最大id起止位置 pro_book_id：".$max_id.PHP_EOL;
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