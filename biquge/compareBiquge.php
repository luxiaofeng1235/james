<?php
require_once dirname(__DIR__).'/library/init.inc.php';

$exec_start_time = microtime(true);
$db_conn_base ='db_master';
$db_conn_source ='db_bqg_source';
$table_sync_table = "ims_novel_info_bak";
$tbale_biquge_table='ims_biquge_info';

$sql = "SELECT  * from ims_novel_info_bak WHERE  1";
$list = $mysql_obj->fetchAll($sql,$db_conn_base);



foreach($list as $key =>$val){
	$book_name = $val['title']??'';
	$author = $val['author'] ?? '';
	$store_id = intval($val['store_id']);
	$sql= "select * from book where name = '{$book_name}' and author ='{$author}'";
	$bookInfo = $mysql_obj->fetch($sql,$db_conn_source);
	if($bookInfo){
		    
		$book_id = intval($bookInfo['id']);
		echo "index = {$key}\t store_id = {$store_id}\tbook_name  = {$book_name}\tauthor={$author}\tmatches book_id={$book_id}\r\n";
		$string_key = getFirstThreeDigits($book_id);
		$source_url = sprintf("%ssource/%s/%s.html",Env::get('BQG.BASE_URL'),$string_key, $book_id);
		$insertData['story_link'] = $source_url;
		$insertData['title'] = $book_name;
		$insertData['author'] = $author;
		$insertData['pro_book_id'] = $val['pro_book_id'];
		$insertData['story_id'] = $book_id;
		$mysql_obj->add_data($insertData, $tbale_biquge_table, $db_conn_base);
		//顺便更新下状态： 0回滚初始状态吧，保持统一好维护
		$sql = "update ims_novel_info_bak set empty_status = 0 where store_id = {$store_id}";
		echo "sql = {$sql} \r\n";
    	$mysql_obj->query($sql,$db_conn_base);//更新为空的状态
		
	}else{
		echo "index = {$key}\t store_id = {$store_id}\tbook_name  = {$book_name}\tauthor={$author}\tnot match\r\n";
	}
}
echo "over\r\n";
?>