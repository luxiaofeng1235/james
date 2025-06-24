<?php
/*
 * 同步迅搜的索引 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$condition = 'status = 1 and id>=590077';
$db_conn = 'db_novel_pro';
$sql = "select count(1) as num from mc_book WHERE {$condition}";
$info = $mysql_obj->fetch($sql,$db_conn);
$num = $info['num'] ?? 0;
$limit = 500;
$allCount = ceil($num / $limit);
echo "共有{$allCount}页数据 \r\n";
for ($i=0; $i <$allCount ; $i++) { 
	$sql = "select id,book_name,author from mc_book WHERE {$condition} order by id asc limit ".($i* $limit).",{$limit} ";
	echo $sql."\r\n";
	$list= $mysql_obj->fetchAll($sql,$db_conn);
	echo "current page = (".($i+1)."/{$allCount} )\r\n";
	if($list){
		foreach($list as $key =>$val){
			$book_name = $val['book_name'];
			$author = $val['author'];
			$book_id = $val['id'];
			// echo "index = {$key}\t name={$book_name}\t author={$author}\r\n";
			XunSearch::addDocumentCli($book_id , $book_name ,$author);
		}
	}else{
		echo "no book data page ={$i} \r\n";
	}
}
echo "over\r\n";

?>