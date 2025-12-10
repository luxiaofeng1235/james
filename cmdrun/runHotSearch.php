<?php
/*
 * 处理线上的热搜排行TOP10任务列表数据 ,每五分钟更新一次
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    
ini_set("memory_limit", Env::get('CLI.MEMORY_LIMIT', '8000M'));
set_time_limit((int) Env::get('CLI.TIME_LIMIT', 0));
require_once dirname(__DIR__).'/library/init.inc.php';
$table_name = Env::get('TABLE_MC_BOOK');
$db_conn = NovelModel::$db_conn;//获取连接句柄


$table_search_name = 'mc_book_search_rank';//搜索排行表
$rec_type= 'hot_search';
$limit =20; //默认20暂时先这样子
$delNum = 20;//删除的步长操作
cli_log('info', 'Top20 热搜榜单数据');
$condition = 'status = 1'; //暂时先查全部显示的的
//删除榜单的操作数据流程实例化
$searchCondition="1 and rec_type='{$rec_type}'";

//检查有重复的下架的书籍并进行删除
NovelModel::checkSearchBookClosed($searchCondition,$table_search_name);


$sql = "select count(1) as count from {$table_search_name} where {$searchCondition}";
$info= $mysql_obj->fetch($sql,$db_conn);
cli_log('debug', "count_sql={$sql}");
$searchCount = intval($info['count']);
if($searchCount>=$delNum){
		cli_log('info', '超出保留行数，开始清理多余排行');
	//如果有超过20个的，查出来所有的把20往后的先给删掉优先保存前20的
	$sql = "select id from {$table_search_name} where {$searchCondition} order by id asc";
		cli_log('debug', "list_sql={$sql}");
	$bookSearh = $mysql_obj->fetchAll($sql,$db_conn);
	$ids = array_column($bookSearh, 'id');
	if($ids){
		$deleteIds = array_slice($ids, $delNum);//删除20以上的自动删除
		if($deleteIds){
			cli_log('info', "删除多余排行，保留前{$delNum}", ['ids' => $deleteIds]);
			$delete_sql = "delete from {$table_search_name} where id in (".implode(',', $deleteIds).") and {$searchCondition}";
			cli_log('debug', "delete_sql={$delete_sql}");
			$mysql_obj->query($delete_sql,$db_conn);
		}else{
			cli_log('info', "未发现超过 {$delNum} 条的热搜数据，无需删除");
		}
	}
}

$sql = "select id,book_name,author,search_count from {$table_name} where {$condition} order by search_count desc  limit {$limit}";
cli_log('debug', "sql={$sql}");
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
			cli_log('info', "新增热搜记录", ['index' => $key, 'id' => $bookId, 'book_name' => $book_name, 'search_count' => $search_count]);
			$newdata['bid'] = $bookId;
			$newdata['search_count'] = $search_count;
			$newdata['created_at'] = time();
			$newdata['rec_type'] = $rec_type;//指定的搜索类型
			$bData[]=$newdata;
		}else{
			//更新对应的最后修改的时间
			$i_num++;
			cli_log('debug', "热搜已存在，更新计数", ['index' => $key, 'id' => $bookId, 'local_id' => $ret['id'], 'book_name' => $book_name, 'search_count' => $search_count]);
			$sql = "update {$table_search_name} set search_count = {$search_count} , updated_at= ".time() ." where id = {$ret['id']}";
			$mysql_obj->query($sql,$db_conn);
		}
	}
	cli_log('info', '热搜抓取完成', [
		'total' => count($bookList),
		'to_insert' => count($bData),
		'already_exists' => $i_num,
	]);
	//添加新增的数据信息
 	if ($bData) {
 		//添加需要同步的关联数据
		$ret = $mysql_obj->add_data($bData, $table_search_name, $db_conn);
		cli_log('info', '同步排行榜单数据完成');
 	}
}else{
	cli_log('warn', '未找到可用书籍数据');
}
cli_log('info', '任务结束', ['time' => date('Y-m-d H:i:s')]);

?>
