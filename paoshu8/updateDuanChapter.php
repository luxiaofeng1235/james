<?php
/*
 * 断章更新埋点处理
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */


ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
require_once dirname(__DIR__) . '/library/file_factory.php';

use QL\QueryList;
$db_conn_novel = 'db_master';
$table_novel_table = "mc_book";
$table_ims_table = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
$db_conn = 'db_master';
$factory = new FileFactory($mysql_obj, $redis_data);
$date = date('Y-m-d',strtotime('-3 days')).' 23:59:59'; //截止晚上的时间判断
$sql = "select id,book_name,author,source_url ,update_chapter_title,update_chapter_time from {$table_novel_table} where  chapter_num>=100 and serialize = 1 and FROM_UNIXTIME(update_chapter_time)<='{$date}'";
echo $sql;exit;
//查询需要检索的断更的渠道来源
$where_condition = NovelModel::getDuanUrlReferCondition();
if($where_condition!=""){
	$sql .= ' and '.$where_condition;
}
echo "sql = {$sql} \r\n";
exit;
$list = $mysql_obj->fetchAll($sql , $db_conn_novel);
echo "total-num =" .count($list) . "\r\n";
$insert_num =$have_num = 0;
$insertData = $haveData = [];

if($list){
	foreach($list as $key =>$value){
		 if(!$value){
		 	continue;
		 }
		 $info = getNovelByNameInfo($value['book_name'],$value['author']);
		 //判断当前是否存在
		 if($info){
		 	$have_num++;
		     $haveData[]= $value['id'];
		     echo "key = {$key}\ttitle={$value['book_name']}\tstore_id={$info['store_id']}\tid = {$value['id']}\turl= {$value['source_url']}\thave data\r\n";
		     $sql = "update {$table_ims_table} set is_async = 0,syn_chapter_status = 0,note = '断更章节处理',story_link ='{$value['source_url']}' where store_id ={$info['store_id']}";
		     $ret = $mysql_obj->query($sql,$db_conn);
		         
		 }else{//数据插入同步数据
	 		$insert_num++;
	 	    $source_url = trim($value['source_url']);
	 	    if(strstr($source_url, 'banjiashi')){
	 	    	//处理url存储问题
	 	    	$source_url = str_replace('xiaoshuo','index',$source_url);
	 	    	$source_url .='1/';//处理对应的存储问题
	 	    }else if(strstr($source_url, 'ipaoshuba')){
	 	    	$source_url = str_replace('Book','Partlist',$source_url);
	 	    }
	 	    $source = NovelModel::getSourceUrl($source_url);
	 		$insertData= [
	 			'pro_book_id'	=>$value['id'], //小说ID
	 			'title'	=>$value['book_name'],//标题id
	 			'author'	=>$value['author'], //更新数据
	 			'story_link'	=>$source_url,//章节ID
	 			'source'	=> $source,
	 			'is_async'	=>0,
	 			'note'=> '断更章节处理',
	 			'syn_chapter_status'	=>0,
	 			'createtime'	=>time(),
	 		];
		 	$sid = $mysql_obj->add_data($insertData, $table_ims_table, $db_conn);
		 	echo "key = {$key}\ttitle={$value['book_name']}\tstore_id={$sid}\tid = {$value['id']}\turl= {$value['source_url']}\tinsert data\r\n";
		 }
	}

	//处理采集源更新问题和本地的不一样的问题
	// if ($referer == 'ipaoshuba'){
	// 	foreach($list as $key => $val){
	// 		$url = $val['source_url'] ?? '';
	// 		$html = webRequest($url,'GET');
	// 		global $urlRules;
	// 		$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['ipaoshuba_append'];
	// 		$info_data = QueryList::html($html)
	// 			->rules($rules)
	// 			->query()
	// 			->getData();
	// 		if($info_data['keywords']){
	// 			 list($title,$author) = explode(',',$info_data['keywords']);
	// 			 if($val['book_name']!=$title || $val['author'] !=$author){		
	// 			 	  $sql = "update {$table_novel_table} set book_name = '{$title}',author='{$author}' where id = {$val['id']}";
	// 			 	  echo "index = ".($key+1)."\t修改【ID ={$val['id']}】变更数据同步 book_name ：from 【{$val['book_name']}】 to 【{$title}】 \t author : from 【{$val['author']}】 to 【{$author}】 \t url ={$val['source_url']}\r\n"; 
	// 			 	  $mysql_obj->query($sql , $db_conn_novel);
				 	        
	// 			}
	// 		}
	// 	}
	// }
}

///获取关联的小说信息
function getNovelByNameInfo($book_name='',$author ='', $field = 'store_id,title,author,story_link'){
	if (!$book_name || !$author) {
		return  false;
	}
	global $mysql_obj,$db_conn;
	//整体思路
	$sql = "select {$field} from " . Env::get('APICONFIG.TABLE_NOVEL') . " where title='{$book_name}' and author='{$author}'"; //
	$info = $mysql_obj->fetch($sql,$db_conn);
	return $info ?? [];

}


echo "------------共需要处理的断章小说有". count($list)."本 \r\n";
echo "===========实际待需要插入的小说有 " . $insert_num . "本，会自动同步\r\n";
echo "******************已存在的小说有 ".$have_num."本，状态会自动处理为待同步\r\n";
?>