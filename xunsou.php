<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once __DIR__.'/library/init.inc.php';


$id = isset($argv[1]) ? intval($argv[1]) : 0;
if(!$id){
	exit("no id params \r\n");
}

$table_novel_table = "mc_book";
$db_conn_novel = 'db_novel_pro';


$sql = "select id,book_name,author  from  {$table_novel_table} where  id = ".$id;
echo "sql = {$sql}\r\n";
$info = $mysql_obj->fetch($sql , $db_conn_novel);
if($info){
	$res = XunSearch::addDocument($id,$info['book_name'],$info['author']);
	echo "id = {$id}\ttitle = {$info['book_name']}\t author = {$info['author']} syn success\r\n";
	$docs = XunSearch::getSearchList('废土世界');
	dd($docs);
}else{
	echo "no this recoreds \r\n";
}




    

//创建文档对象，默认字符集utf-8
// $doc = new XSDocument;
// $doc->setFields($data);

// // $index->openBuffer(); 
// // 添加到索引数据库中
// // $res= $index->add($doc);
// $res = $index->add($doc);
// 告诉服务器重建完比
// $index->closeBuffer(); // 关闭缓冲区，必须和 openBuffer 成对使用


// $search = $xs->search; // 获取 搜索对象
// $docs = $search
// 		->setQuery($keyword)
// 		// ->setLimit(0,5)
// 		->setSort('chrono')
// 		->search();
// dd($docs);
// if(!empty($docs)){
// 	foreach ($docs as $doc)
// 	{
// 	   // 其中常用魔术方法：percent() 表示匹配度百分比, rank() 表示匹配结果序号
// 	   echo $doc->rank() . '. ' . $doc->subject ." [" . $doc->percent() . "%] - ";
// 	   echo date("Y-m-d", $doc->chrono) . "\n" . $doc->message . "\n";
// 	}
// }else{
// 	echo "no data\r\n";
// }

?>