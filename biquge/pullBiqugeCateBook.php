<?php

/*
 * 拉取笔趣阁线上分类数据到本地 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$novel_table_name = Env::get('BQG.TABLE_NAME');
$db_conn = 'db_master';
// $cateIsd = 
$exec_start_time = microtime(true);

$cateIds = [
	//男生
	1=>[
		0,1,2,4,6,7,8,10,11
	],
	//女生
	2=>[
		1,2,3
	],
];

// $sex = 2; //男
// $cate_id = 2;//具体的某分类
// $page = 1; //从第一页开始

foreach($cateIds as $sex =>$val){
	foreach($val as $class_id){
		echo "this sex = {$sex} \t this cate id = {$class_id}\r\n";
		for ($i=0; $i <50 ; $i++) { 
			$page = $i+1;
			$url = sprintf("%sclassify/all/%s/%s/0/-1/%s.html",Env::get('BQG.BASE_URL'),$sex,$class_id , $page);
			echo $url."\r\n";
			//同步历史数据信息
			synBookList($sex,$class_id,$page);
		}
		echo "-------------------------------------------------------------------------\r\n";
		//拼装采集的url，里面还需要重新遍历一次批量采集 ，可以用swoole的请求去批量一次50个请求出来
	}
}

/**
* @note 处理分类列表的类目数据同步
* @param  $sex int 性别 1：男 2：女
* @param $cat_id int 分类ID
* @param $page int 页码
* @return array|bool
*/
function synBookList($sex,$cate_id,$page){
	if(!$sex || !$page){
		return false;
	}
	// global $mysql_obj,$novel_table_name,$db_conn;
	// $cateBook = BiqugeService::getBookyByCategory($sex,$cate_id,$page);
	
	return true;
}

$exec_end_time = microtime(true);
$endMemory = memory_get_peak_usage();
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "finish\r\n";
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>