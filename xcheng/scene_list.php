<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once ($dirname."/library/mysql_class.php");
require_once ($dirname."/library/HttpClientRequest.php");

$mysql_obj = new Mysql_class();
$http_client = new HttpClientRequest();#初始化实例


$redis = new \Redis();
$redis->connect('localhost',6379);

 


$key = $_GET['key'] ?? '';
if(!$key){
	exit('输入当前需要处理的key');
}

$info = $mysql_obj->fetch("select * from trip_city where keywords='$key' limit 1",$mysql_obj->db_slave);

if(!$info){
	exit("error");
}
$city_site = trim($info['keywords']);
$cid =intval($info['id']);


$link_url = 'https://you.ctrip.com/sight/'.$city_site.'.html';





$redis_key = '';
$url_info = explode('/', $link_url);

if(isset($url_info[4])){
	$redis_key = substr($url_info[4], 0,-5);
}
$pager_key = 'pager_'.$redis_key;

$result = $redis->get($redis_key);
$result = json_decode($result,true);
if(!$result){

 	$datalist = $http_client->run($link_url,'get');
	$datalist =preg_replace("/[\t\n\r]+/","",$datalist);
	
	$redis->set($redis_key,json_encode($datalist));
	$result = $datalist;
}
/**
* @note 获取景区的分页列表
*
* @param $url 当前请求的url
* @return array
*/
     
function get_scene_list( $url = ''){
	if(!$url)
		return false;
	global $http_client;
	// $url = 'https://you.ctrip.com/sight/lanzhou231/s0-p8.html#sightname';
	$result = $http_client->run($url,'get');
	$result =preg_replace("/[\t\n\r]+/","",$result);
	#把其中的内容匹配出来
	preg_match('/<div class="list_wide_mod2">(.*)<div class="ttd_pager cf">/',$result, $match_res);

	if(!empty($match_res) && isset($match_res[0])){
		$plist = $match_res[0];
		
		//匹配出来链接保存每个应用的表示
		#超链接里得字符已经匹配出来了|
	 
		$res = preg_split('/<div class="list_mod2">/', $plist);
		
		if(isset($res[0])) unset($res[0]);
		$list= [];
		foreach($res as $k =>$v){
			//有strong标签说明有评分，其他的没有评分的产品不要
			//只匹配有评分的，有评分以后再去匹配对应的链接,
			if(preg_match('/<strong>(.*)<\/strong>/', $v,$score_ret)){
				 if(isset($score_ret[1]) && is_numeric($score_ret[1])){
				 	 //匹配链接
				 	 preg_match('/<a[^>]+href="(([^"]+)")/i',$v,$result2);
				 	 $result2 && isset($result2[2]) ? $list[] = $result2[2] : '';
				 }
			}
		}
		if($list){
			return array_merge_recursive(array(), $list);
		}

		// preg_match_all('/<a[^>]+href="(([^"]+)")/i',$plist,$result2);
		// if($result2 && isset($result2[1])){
		// 	foreach($result2[1] as $k => $v){
		// 		 //判断第一个节点的数据，只需同步有评分的节点数据
		// 		 if(strpos($result2[0][$k], 'score')){
		// 		 	 //需要双方同时验证。
		// 			if(preg_match('/^\/sight/', $v) ){
		// 				 $list[] = $v;
		// 			}
		// 		 }
		// 	}
			// $list = array_merge(array_unique($list),array());
			// foreach($list as $key =>$val){
			// 	if(strpos($val, '#')){
			// 		unset($list[$key]);
			// 	}else{
			// 		$list[$key] = substr($val, 0,-1);
			// 	}
			// }
			//处理list列表调用函数来处理
			//匹配出来分页数据方便做到时候批量便利分页数据内容
 			
	
		// }
	}else{
		return [];
	}
}

//从缓存的内容中进行匹配pager数据
preg_match('/<b class="numpage">(.*)<\/b>/',$result, $pager_ret);
	if(!empty($pager_ret) && isset($pager_ret[1]) ){
		$totalNum = (int) $pager_ret[1];
		
		if($totalNum>0){
			//存一下redis数据方便后面来做分页批量获取
			$url_info = explode('/', $link_url);
			if(isset($url_info[4])){
				if(!$redis->get($pager_key)){
					$redis->set($pager_key,$totalNum); //存一下后期能用到
				}
			}
		}
	}
	$scene_list = [];
	foreach(range(0,$totalNum-1) as $i){
		$url = 'https://you.ctrip.com/sight/'.$city_site.'/';
		$url .='s0-p'.($i+1).'.html';
		 if($i>0){//第二页开始需要添加一个标识
		      $url .='#sightname';
		}			     	
		$data =get_scene_list($url);
		if(!$data) continue;
		$scene_list[]=$data;
		unset($data); //用完释放变量
	}

	//同步数据
	if($scene_list){
		foreach($scene_list as $gk => $v){
			if(!$v || empty($v)) continue;
			$sql ="insert into trip_scene_list (cid,city_site,matches_url) values ";
			$tval=array();
			$tkey =array();
			//批量添加
			foreach($v as $key =>$val){
			 	$sql .="('".$cid."','".$city_site."','".$val."'),";
			}
			 $sql = rtrim($sql , ',');
     
			 $res = $mysql_obj->query($sql,$mysql_obj->db_master);
			 if(!$res){
			 	echo "index:{$gk} multi insert error <br />";
			 }
		}
	}
	echo "over";		 
?>