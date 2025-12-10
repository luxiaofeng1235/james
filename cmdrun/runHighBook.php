<?php
/*
 * 处理线上的高峰佳作的100本 小说
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

$rec_type = 'high';
$table_search_name = 'mc_book_search_rank';//搜索排行表
$highCondition ="true and rec_type='{$rec_type}'"; //小表的搜索数据
$limit =100; //默认20暂时先这样子
cli_log('info', 'Top100 高分佳作小说数据');


//检查有重复的下架的书籍并进行删除
NovelModel::checkSearchBookClosed($highCondition,$table_search_name);


$condition = 'status = 1 AND (is_rec = 1 and score >=8)'; //暂时先查全部显示的的
$sql = "select id,book_name,author,score from {$table_name} where {$condition} order by search_count desc  limit {$limit}";
cli_log('debug', "sql={$sql}");
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
			cli_log('info', '新增高分记录', ['index' => $key, 'id' => $bookId, 'book_name' => $book_name, 'score' => $score]);
			$info['bid'] = $bookId;
			$info['score'] = $score;
			$info['created_at'] = time();
			$info['rec_type']  = $rec_type;
			$bData[]=$info;
		}else{
			//更新对应的最后修改的时间
			$i_num++;
			cli_log('debug', '高分记录已存在，更新评分', ['index' => $key, 'id' => $bookId, 'local_id' => $ret['id'], 'book_name' => $book_name, 'score' => $score]);
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
			cli_log('info', "已满足 {$limit} 本高分书籍，无需追加");
			exit;
		}

		cli_log('info', '高分抓取完成', [
			'total' => count($bookList),
			'to_insert' => count($bData),
			'already_exists' => $i_num,
		]);
	//添加新增的数据信息
 	if ($bData) {
 		//添加需要同步的关联数据
			$ret = $mysql_obj->add_data($bData, $table_search_name, $db_conn);
			cli_log('info', '同步高分佳作TOP100完成');
	 	}
	}else{
		cli_log('warn', '未找到可用书籍数据');
	}
	cli_log('info', '任务结束', ['time' => date('Y-m-d H:i:s')]);

?>
