<?php
/*
 * 处理线上的高峰佳作的100本 小说
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$table_name = Env::get('TABLE_MC_BOOK');
$db_conn = NovelModel::$db_conn;//获取连接句柄

$rec_type = 'high';
$table_search_name = 'mc_book_search_rank';//搜索排行表
$highCondition ="true and rec_type='{$rec_type}'"; //小表的搜索数据
$limit =100; //默认20暂时先这样子
echo "----------------------- Top100 高分佳作小说数据--------------------------------- \r\n";


//检查有重复的下架的书籍并进行删除
NovelModel::checkSearchBookClosed($highCondition,$table_search_name);


$condition = 'status = 1 AND (is_rec = 1 and score >=8)'; //暂时先查全部显示的的
$sql = "select id,book_name,author,score from {$table_name} where {$condition} order by search_count desc  limit {$limit}";
echo "sql = {$sql} \r\n";
$bookList = $mysql_obj->fetchAll($sql , $db_conn);
if(!empty($bookList)){
	$bData= [];
	$i_num = 0;
	foreach($bookList as $key=> $val){
		$bookId = intval($val['id']); //书籍ID
		$book_name = trim($val['book_name']);
		$score = floatval($val['score']);//分值排行
		if(!$bookId){
			continue;
		}
		$sql = "select * from {$table_search_name} where {$highCondition} and bid = {$bookId}";
		$ret = $mysql_obj->fetch($sql,$db_conn);
		if(empty($ret)){
			echo "index={$key} \t id = {$bookId}\t book_name = {$book_name}\t score={$score}\t insert add data\r\n";
			$info['bid'] = $bookId;
			$info['score'] = $score;
			$info['created_at'] = time();
			$info['rec_type']  = $rec_type;
			$bData[]=$info;
		}else{
			//更新对应的最后修改的时间
			$i_num++;
			echo "index={$key} \t id = {$bookId}\t local_id={$ret['id']}\tbook_name = {$book_name}\tscore={$score}\texists\r\n";
			$sql = "update {$table_search_name} set score = {$score} , updated_at= ".time() ." where id = {$ret['id']}";
			$mysql_obj->query($sql,$db_conn);
		}
	}


	//检查库里如果数据就先删掉。只保留20个
	$sql = "select count(1) as count from {$table_search_name} where {$highCondition}";
	$info= $mysql_obj->fetch($sql,$db_conn);
	$oldcount = intval($info['count']); //已经同步过的统计
	$newCount = count($bData); //新增的数据信息
	//已同步+未同步的总次数统计
	$allCount = $oldcount+$newCount;
	//判断当前如果有满足新的100本书籍的时候就不用再去插入同步了，只要最新的100本书籍即可。
	if ($allCount>$limit){
		echo "已经满足有count = {$limit} 本书了，不需要再去重复同步了\r\n";
		exit;
	}

	echo "------------共拉取下来的的高分小说说有". count($bookList)."本 \r\n";
	echo "===========实际待需要插入的高分小说有 " . count($bData) . "本，会自动同步\r\n";
	echo "******************已存在的高分小说有 {$i_num}本\r\n";
	echo "deal success \r\n";
	//添加新增的数据信息
 	if ($bData) {
 		//添加需要同步的关联数据
		$ret = $mysql_obj->add_data($bData, $table_search_name, $db_conn);
		echo "同步高分佳作TOP100完成 \r\n";    
 	}
}else{
	echo "no book data \r\n";
}
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";

?>