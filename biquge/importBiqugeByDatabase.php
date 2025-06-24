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

$redis_key = 'biquge_all_incr_id:'.date('Ymd');//redis的对应可以设置
$id = $redis_data->get_redis($redis_key);
// $redis_data->del_redis($redis_key);
$date = date('Y-m-d',strtotime('-3 days')).' 23:59:59';
$condition="a.is_open = 1 
and a.form =1
and a.last_site_id is not null";
$condition .= " and b.serialize = 1 and  FROM_UNIXTIME(b.update_chapter_time)<='{$date}'";
$condition .=" and MD5(a.last_chapter_name) !=MD5(b.update_chapter_title)"; //两边的最后更新章节不相等就说明是需要更新的章节
if($id){
	$condition .= " and a.id > ".intval($id);
}
//查询断章的 
/*
查询条件：
1、最后更新的时间必须是三天以上的
2、状态为连载中
3、章节总数必须是100章以上的，低于100章的不考虑
*/
//查的条件：，而且是连载中的，已完结的不需要查询

$sql = "SELECT b.id as local_book_id,b.book_name,b.author,b.update_chapter_title,b.update_chapter_time,a.id as sid ,a.last_site_id,a.name,a.author as sauthor,a.last_chapter_name
from book as a 
inner join mc_book as b 
on a.`name` = b.book_name and a.author = b.author 
WHERE {$condition}
order by sid asc";    
$limit= 1000; //控制列表的步长
$sql .=" limit {$limit}";
echo "sql : " .$sql ."\r\n";
$lists = $mysql_obj->fetchAll($sql,'db_bqg_source');

if($lists){
	$bookList  = [];
	foreach($lists as $key =>$val){
		$sid = intval($val['sid']);
		$pro_book_id = intval($val['local_book_id']);
		//处理输入字符并进行mysql的转义
		$name = BiqugeModel::stringHandleInput($val['name']);
		$author = BiqugeModel::stringHandleInput($val['sauthor']);
		$bookList[]=[
			'site_id'	=>$val['last_site_id'],
			'pro_book_id'	=>$pro_book_id,
			'book_id'	=> $sid,//小说书的ID
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
		echo "下次轮训的最大id起止位置 biquge_book_id：".$max_id.PHP_EOL;
		echo "redis_key:{$redis_key} \r\n";
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