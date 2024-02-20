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
		$detail = webRequest($link_url , 'GET' , [],[]);
		//$list = MultiHttp::curlGet(array($link_url));
		// $detail  = $list[0] ?? [];

		preg_match("/<div class=\"jieshao\".*?>.*?<\/div>/ism",$detail,$matchesRes);
		preg_match('/<img.*?src="([^"]+)"/',$matchesRes[0],$m);
		$store_data['cover_logo'] = $url .$m[1]??'';

		preg_match('/novelid=([0-9]+)/',$detail,$lid); //匹配novelid方便进行存储
		$store_data['novelid'] = isset($lid[1]) ? $lid[1] : null;

		//获取标题
		preg_match("#<h1>([^<]*)</h1>#",$detail,$title_data);
		$store_data['title'] = $title_data[1] ?? '';
		//获取连载和更新时间、作者、最后的一节的章节
		preg_match("/<div class=\"msg\".*?>.*?<\/div>/ism",$detail,$all_data);
		$pattern = '/[\s]+/';
		$c_data= preg_split($pattern, $all_data[0]);

		if(isset($c_data[5])){
			$author = filterHtml($c_data[5]);
			$author_info = explode('>',$author);
			$store_data['author'] = $author_info[1] ?? '';
		}
		if($c_data){
			$en_preg = "/[\x7f-\xff]+/";//匹配中文
			//匹配对应的状态信息
			if(isset($c_data['6'])){
				$a= explode('：',$c_data[6]);
				preg_match($en_preg,$a[1],$status_data);
				$store_data['status'] = $status_data[0] ??'';
			}
			//处理更新时间
			if(isset($c_data[7])){
				list($c_name,$date) =explode('：',$c_data[7]);
				$up_time =$date.' '.$c_data[8] ??'';
				$up_time = filterHtml($up_time);
				$store_data['third_update_time'] = strtotime($up_time);
			}
			print_R(preg_match('/target="_blank">.*/',$c_data[14],$t));
			//处理最后的章节
			if(isset($c_data[15])){
				$aa = $c_data[15];
				$nearyby_item = $t[0].' '.$aa;
				$html =str_replace('target="_blank">','',$nearyby_item);
				$html = str_replace('</a>','',$html);
				$html =str_replace('</em>','' ,$html);
			}else{
				//如果没有匹配到从12中去获取
				$html = $c_data[12] ?? '';
				$html = str_replace('"','',$html);
			}

			$store_data['nearby_chapter'] = $html;
		}

		$chapter_detal  = [];
		//匹配章节目录信息
		preg_match("/<div class=\"mulu\".*?>.*?<\/div>/ism",$detail,$matchesRes);
		if(isset($matchesRes[0]) && !empty($matchesRes[0])){
			$pat = '/href=[\"|\'](.*?)[\"|\']/i';
			$newdata = preg_match_all("/<li.*?>.*?<\/li>/ism" , $matchesRes[0],$aaa);
			//判断相关的流程部署
			if(!empty($aaa)){
				foreach($aaa[0] as $link_value){
					preg_match($pat , $link_value ,$link_info);
					$chapter_detal[]=[
						'link_url'  =>  $link_info[1] ?? '',
					];
				}
			}
		}
		$store_data['cate_id'] = $art_id;
		$store_data['createtime'] = time();
		//执行插入操作
		$store_id = $mysql_obj->add_data($store_data , $table_novel_name);
		if(!$store_id){
			echo "add baseinfo error<br />";
			exit();
		}
		//关联插入的ID信息
		foreach($chapter_detal as &$v){
			$v['store_id']  =   $store_id;
			$v['createtime'] = time();
		}
		$chapter_table_name= 'ims_chapter';
		$res = $mysql_obj->add_data($chapter_detal , $chapter_table_name);
		echo "基础信息和章节目录更新完成";
	}
}else{
	echo "no data";
}
?>
