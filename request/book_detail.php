<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_detail.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:获取最新的文章的标签
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;##引入querylist的采集器

if(is_cli()){
	$art_id = $argv[1] ?? 0;
}else{
	$art_id  = isset($_REQUEST['art_id']) ? intval($_REQUEST['art_id']) : 0;
}
if(!$art_id){
	echo '请选择要抓取的内容id';
	exit();
}
$table_name ='ims_category';
$table_novel_name ='ims_novel_info';
$info = $mysql_obj->get_data_by_condition('id = \''.$art_id.'\'',$table_name);
$url ='https://www.souduw.com';
if($info){
	//进行相关的匹配信息
	if(!empty($info[0]['article_url'])){
		$link_url = $url . $info[0]['article_url'];//需要抓取的网址

		//定义抓取规则
		$rules = array(
		    'cover_logo'       =>array('.jieshao img','src'),
		    'author'    => array('.jieshao .rt em:eq(0) a','text'),
		    'title'     =>array('.jieshao .rt>h1','text'),
		    'status'    =>array('.jieshao .rt em:eq(1)','text'),
		    'third_update_time'    =>array('.jieshao .rt em:eq(2)','text'),
		    'nearby_chapter'    =>array('.jieshao .rt em:eq(3) a','text'),
		    'intro' => array('.intro','html'),
		    'location'  =>  array('.place','text'),
		    'link_url'    =>array('.place a:eq(2)','href'),//当前书籍的url
		    'novel_url'   =>array('.info a:eq(2)','href'),//获取a连接里的值
		);
		$info_data=QueryList::get($link_url)->rules($rules)->query()->getData();
		$store_data = $info_data->all();
		if(!empty($store_data)){
			$store_data['status'] = str_replace('状态：','',$store_data['status']);
			preg_match('/novelid=([0-9]+)/',$store_data['novel_url'],$novelid); //匹配novelid方便进行存储
			$novelid && $store_data['novelid'] = $novelid[1] ?? 0;
			if(!empty($store_data['link_url'])){
				$link_data = explode('/' , $store_data['link_url']);//处理标签
				if($link_data && isset($link_data[2])){
					$store_data['english_name'] = str_replace('.html','',$link_data[2]);
				}
				//更新时间处理
				$update_time  = str_replace('更新时间：','',$store_data['third_update_time']);
				$update_time = strtotime($update_time);
				$store_data['third_update_time'] = $update_time;

				$store_data['intro'] = htmlspecialchars($store_data['intro']);
				unset($store_data['link_url']);//删除链接地址
				unset($store_data['novel_url']);//删除无用数据
				$store_data['cate_id'] = $info[0]['id'];
				$store_data['createtime'] = time();

				//执行插入操作
				$where_data = "novelid = '".$store_data['novelid']."'";

				$check_data = $mysql_obj->get_data_by_condition($where_data,$table_novel_name,'store_id');
				if(!empty($check_data)){
					$store_id = intval($check_data[0]['store_id']);
				}else{
					$store_id = $mysql_obj->add_data($store_data , $table_novel_name);
				}
				if(!$store_id){
					echo "add baseinfo error<br />";
					exit();
				}

				//定义章节的目录信息
				$list_rule = array(
				    'link_url'       =>array('a','href'),
				  	'link_name'		=>array('a','text'),
				);
				$range = '.mulu li';
				$rt = QueryList::get($link_url)->rules($list_rule)->range($range)->query()->getData();
				if(!empty($rt->all())){
					$chapter_detal = $rt->all();
					foreach($chapter_detal  as &$value){
						$value['store_id'] = $store_id;
					}
					$chapter_table_name= 'ims_chapter';
					$res = $mysql_obj->add_data($chapter_detal , $chapter_table_name);
					echo "基础信息和章节目录更新完成";
				}
			}
		}else{
			echo "未匹配相关数据\r\n";
		}
	}


}else{
	echo "no data";
}
?>
