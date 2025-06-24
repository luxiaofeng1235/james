<?php
/*
 * 同步泡书吧的链接，方便进行采集 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$file = file('./hotbook.txt');
$bookList = [];
foreach($file as &$v){
	$v =  preg_replace('/\s*/', '', $v);
	$bookList[]=$v;
}
foreach($bookList as $key =>$book_name){
	    
	$keywords = urlencode($book_name);
	$url = "http://www.paoshu8.info/modules/article/search.php?searchkey={$keywords}";
	// echo "search-keyword-url ={$url}\r\n";
	$html = webRequest($url,'GET');
	preg_match('/<table class=\"grid\".*?>.*<\/table>/ism', $html, $list);
	$contents = $list[0] ??'';
	//匹配出来对应的url信息
	$range = '#nr';
	$data = \QL\QueryList::html($contents)
					->rules([
						'book_name'=>['td:eq(0) a','text'],//书名
						'link_url'=>['td:eq(0) a','href'],//请求的url
						'author'=>['td:eq(2)','text'],//作者名
					])
					->range($range)
	    			->query()
	    			->getData();
	$info_data = $data->all();
	$story_link = '';
	foreach($info_data as $k =>$v){
		//只判断书名不然书太多了
		$search_book_name = $v['book_name'] ??'';
		if($book_name == $search_book_name){
			$story_link = $v['link_url'] ??'';
			break;
		}
	}
	if(!empty($story_link)){
		// echo $key."\t".$book_name."\t".$story_link."\r\n";
		echo $story_link."\r\n";
	}else{
		
		echo $key."\t".$book_name."\t未匹配到相关连接诶\r\n";
	} 
	    
}


?>