<?php
/*
 * 检查重复的小说并执行删除操作

 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
require_once dirname(__DIR__) . '/library/init.inc.php';
echo "start_time：".date('Y-m-d H:i:s')."\r\n";
$db_conn_novel = 'db_novel_pro';
$table_novel_table = "mc_book";

$sql = "SELECT count(id) as num,md5(CONCAT(book_name,author)) as book_sign,book_name,author from {$table_novel_table} WHERE 1 and 
( book_name!='' or author!='')
group by  book_sign 
HAVING num>1";
echo "sql = {$sql} \r\n";
$list = $mysql_obj->fetchAll($sql,$db_conn_novel);

if($list){
	foreach($list as $key =>$val){
		if(!$val) continue;
		$sql = "select id from {$table_novel_table} where book_name ='{$val['book_name']}' and author='{$val['author']}' order by id asc";
		$bookList = $mysql_obj->fetchAll($sql , $db_conn_novel);
		    
		if($bookList){
		    $bookIds = array_column($bookList,'id');
		    $live_book_id = end($bookIds);
		    array_pop($bookIds);
		    if($bookIds){
		    	$sql= "delete from {$table_novel_table} where id in (".implode(',' ,$bookIds).")";
		    	echo "key = {$key}\t保留book_id ={$live_book_id}\t删除重复的ids=".implode(',', $bookIds)."\tsql = {$sql}\r\n";
		    	$mysql_obj->query($sql,$db_conn_novel);
			}else{
				echo "key = {$key}\t保留book_id ={$live_book_id}\t未发现有两个以上测重复ID\r\n";
			}
		}
		    // 
	}
}else{
	echo "no find books\r\n";
}
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
echo "over\r\n";
?>