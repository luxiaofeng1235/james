<?php
/*
 * 同步小说的数据到gofound索引中去
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';



echo "start_time：".date('Y-m-d H:i:s') .PHP_EOL;
$db_conn_novel = 'db_master';
$table_novel_table = "mc_book";

$exec_start_time = microtime(true);
$where_data = 'status = 1';
$limit= 500; //控制图片拉取的步长
$id = isset($argv[1]) ? $argv[1] : 0;
if($id){
    $where_data .=" and id  = ".$id;
    // $where_data .= " and pro_book_id = 125119";
}

$order=" order by id asc";
$ts_sql = "select count(id) as num from {$table_novel_table} where {$where_data}";
$ts_count = $mysql_obj->fetch($ts_sql,$db_conn_novel);
$count = $ts_count['num'] ?? 0;
echo "ts-sql:{$ts_sql}\r\n";
echo "total-book:{$count}\r\n";
if(!$count){
	exit("no data\r\n");
}
$allPage = ceil($count/$limit);
for ($i=0; $i < $allPage ; $i++) { 
	echo "current Page = ({$allPage}/".($i+1).")\r\n";
	$sql = "select id,book_name,author  from  {$table_novel_table} where ".$where_data;
	$sql .= $order;
	if($limit){
		$sql .=" limit ".($i*$limit).",{$limit}";
	}
	echo "sql = {$sql} \r\n";
	$list = $mysql_obj->fetchAll($sql,$db_conn_novel);
	if($list){
		synsearchData($list);
	}else{
		echo "no this book recoreds \r\n";
	}
	sleep(1);
	// dd($list);
}

/**
* @note 获取搜索引擎的索引
* @param $book_id  int  小说ID
* @return string
*/
function synsearchData($list = []){
	if($list){
		global $redis_data;
		// $gofound = new GoFound();
		foreach($list as $key =>$val){
			if(!$val) continue;
			//先判断文件中是否有对应的key信息
			$bookKey  = NovelModel::getGofoundKey($val['id']); //获取对应的缓存key
			// $redis_data->del_redis($bookKey);
			// echo 3;die;
			// $bookInfo = NovelModel::getSearchBookData($bookKey); //取出来对应的缓存数据
			// if(!$bookInfo){
				//同步添加res数据信息
			// $res = NovelModel::synGofundAdd($gofound,$val);
			$ret = XunSearch::addDocument($val['id'] , $val['book_name'] ,$val['author']);
			//同步数据信息
			if($ret == true){
				echo "index = {$key}\t  id = {$val['id']}\ttitle = {$val['book_name']}\t author={$val['author']}\txunsearch: success\r\n";
			}else{
				dd($res);
				echo "index = {$key}\t id = {$val['id']}\ttitle = {$val['book_name']}\t author={$val['author']} \t xunsearch:error\r\n";
			}
			// 	$redis_data->set_redis($bookKey,1); //同步对应的索引文件更新
			// }else{
			// 	//已同步
			// 	echo "index = {$key}\t id = {$val['id']}\ttitle = {$val['book_name']}\t author={$val['author']} \t gofondRes: has syn data !!!\r\n";
			// }
		}
	}else{
		echo "no data\r\n";
		return false;
	}
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "finish_time：".date('Y-m-d H:i:s') .PHP_EOL;
echo "script execution time: ".sprintf('%.2f',($executionTime/60)) ." minutes \r\n";
echo "over\r\n";
?>