<?php
/*
 * 处理线上的热搜排行TOP10任务列表数据 ,每五分钟更新一次
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


$table_search_name = 'mc_book_search_rank';//搜索排行表
$rec_type= 'hot_search';
$limit =20; //默认20暂时先这样子
$delNum = 20;//删除的步长操作
echo "----------------------- Top20 热搜榜单数据--------------------------------- \r\n";
$condition = 'status = 1'; //暂时先查全部显示的的
//删除榜单的操作数据流程实例化
$searchCondition="1 and rec_type='{$rec_type}'";

//检查有重复的下架的书籍并进行删除
NovelModel::checkSearchBookClosed($searchCondition,$table_search_name);


$sql = "select count(1) as count from {$table_search_name} where {$searchCondition}";
$info= $mysql_obj->fetch($sql,$db_conn);
echo "count_sql={$sql} \r\n";
$searchCount = intval($info['count']);
if($searchCount>=$delNum){
	echo "*******************************************************************\r\n";
	//如果有超过20个的，查出来所有的把20往后的先给删掉优先保存前20的
	$sql = "select id from {$table_search_name} where {$searchCondition} order by id asc";
	echo "list-sql = {$sql} \r\n";
	$bookSearh = $mysql_obj->fetchAll($sql,$db_conn);
	$ids = array_column($bookSearh, 'id');
	if($ids){
		$deleteIds = array_slice($ids, $delNum);//删除20以上的自动删除
		if($deleteIds){
			echo "存在 {$delNum} 以上的关联数据信息，需要进行自动删除， ids :【 ".join(',',$deleteIds)." 】 （保留TOP20数据） ~~ \r\n";
			$delete_sql = "delete from {$table_search_name} where id in (".implode(',', $deleteIds).") and {$searchCondition}";
			echo "delete_sql ={$delete_sql} \r\n";
			$mysql_obj->query($delete_sql,$db_conn);
		}else{
			echo "暂未发现匹配到{$delNum}以上的排行热搜数据，不需要删除\r\n";
		}
	}
}

$sql = "select id,book_name,author,search_count from {$table_name} where {$condition} order by search_count desc  limit {$limit}";
echo "sql = {$sql} \r\n";
$bookList = $mysql_obj->fetchAll($sql , $db_conn);

if(!empty($bookList)){
	$bData= [];
	$i_num = 0;
	foreach($bookList as $key=> $val){
		$bookId = intval($val['id']); //书籍ID
		$book_name = trim($val['book_name']);
		$search_count = intval($val['search_count']);//搜索排行
		if(!$bookId){
			continue;
		}
		$sql = "select * from {$table_search_name} where bid = {$bookId} and {$searchCondition}";
		$ret = $mysql_obj->fetch($sql,$db_conn);
		if(empty($ret)){
			echo "index={$key}\t id = {$bookId}\t book_name = {$book_name}\t search_count={$search_count}\t product to add data\r\n";
			$newdata['bid'] = $bookId;
			$newdata['search_count'] = $search_count;
			$newdata['created_at'] = time();
			$newdata['rec_type'] = $rec_type;//指定的搜索类型
			$bData[]=$newdata;
		}else{
			//更新对应的最后修改的时间
			$i_num++;
			echo "index={$key} \t id = {$bookId}\t local_id={$ret['id']}\tbook_name = {$book_name}\tsearch_count={$search_count}\texists\r\n";
			$sql = "update {$table_search_name} set search_count = {$search_count} , updated_at= ".time() ." where id = {$ret['id']}";
			$mysql_obj->query($sql,$db_conn);
		}
	}
	echo "------------共拉取下来的的排行小说有". count($bookList)."本 \r\n";
	echo "===========实际待需要插入的哦排行有有 " . count($bData) . "本，会自动同步\r\n";
	echo "******************已存在的排行小说有 {$i_num}本\r\n";
	echo "deal success \r\n";
	//添加新增的数据信息
 	if ($bData) {
 		//添加需要同步的关联数据
		$ret = $mysql_obj->add_data($bData, $table_search_name, $db_conn);
		echo "同步排行榜单数据完成 \r\n";    
 	}
}else{
	echo "no book data \r\n";
}
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";

?>