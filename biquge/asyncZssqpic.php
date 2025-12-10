<?php
/*
 * 同步追书神器的图片 补充本地没有图片的自动替换并更新
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';


$exec_start_time = microtime(true);
$db_conn = 'db_master';
$sql = "select id,source_url,book_name,author,source_url from mc_book WHERE  instr(source_url,'biquge34')>0";
$lists =$mysql_obj->fetchAll($sql,$db_conn);
$picReferer = Env::get('BQG.PIC_URL');

foreach($lists as $val){
	$pic = $val['pic'] ??'';
	$book_name = $val['book_name']??'';
	$author = $val['author'] ?? '';
	$id = intval($val['id']);
	if(!file_exists($pic) ||  !@getimagesize($pic)){
		echo "id = {$id}\t name= {$book_name}\t author= {$author}\turl= {$pic}\t图片缺失，匹配图片抓取\r\n";
		$sql = "select * from book where name ='{$val['book_name']}'";
		$bqginfo = $mysql_obj->fetch($sql,'db_bqg_source');
		if(empty($bqginfo)){
			echo "未获取到 name = {$book_name}\t对应的信息\r\n";
		}
 		$cover_logo = $bqginfo['image'] ?? '';
 		    
		//拼接图片路径		
		$image =$picReferer.$cover_logo;
		$bookId = $bqginfo['id'] ?? 0;
		$site_id= $bqginfo['last_site_id'] ?? '';
		//检查图片的状态是否为404 或者是图片损坏
		if(!@check_url($image) ){
			echo "id = {$id} \tcover_logo = {$image}\t  name= {$book_name}\tauthor= {$author}\t   url = {$image}\t图片资源不存在重新请求\r\n";
			//从数据库再发反查一遍
			$image = BiqugeModel::getCoverInfoByDataBase($bookId,$site_id,$picReferer);
			//如果从库里查询的也不存在
			if(!@check_url($image)){
				//从跑书吧在同步一次同步一次图片
				$image = BiqugeModel::getCoverLogoByPaoshu8($info_data['name'],$info_data['author']);
				if(!$image){//如果三个地方都找不到图片资源，就给一个默认的把
					$image= Env::get('APICONFIG.DEFAULT_PIC'); //如果最终还是为空就给默认
				}
			}
		}
		    
		$res = webRequest($image,'GET');
		$img_con = $res ?? '';
		//按照对于书名+作者重新拼装路径
		$filename= NovelModel::getNovelToPic($book_name,$author ,$image);
		//处理更新的源URL
		
		$sql = "update mc_book set  pic = '{$filename}' where id = {$id}";
		$mysql_obj->query($sql,$db_conn);
		//更新对应的图片
		writeFileCombine($filename, $img_con);	
	}else{
		echo "id = {$val['id']}\tcover_logo = {$image}\t  name= {$book_name}\tauthor= {$author}\t url = {$pic}\t有图片资源 不需要抓取\r\n";
	}
}

echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";

?>