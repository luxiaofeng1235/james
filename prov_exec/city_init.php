<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2020年12月18日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :city_init.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:同步省市区数据用
// ///////////////////////////////////////////////////
//F:/phpstudy_pro/Extensions/php/php7.3.4nts
ini_set("memory_limit", "3000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once ($dirname."/library/init.inc.php");

$str = '社会主义制度';
//echo money_format("$%i",$str);
//die;
//PHP判断全为中文的正则判断
if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $str)){
	print("该字符串全部是中文");
}else{
	print("该字符串不全部是中文");
}

// #引入第三方类库
// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;
// use Monolog\Handler\FirePHPHandler;
// // use Monolog\Formatter\Formatter;

// $logger = new Logger('my_logger');
// #添加处理器
// $logger->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Logger::INFO));
// $logger->pushHandler(new FirePHPHandler());
// #记录日志变化
// $logger->info("My Logger is now today");
// $logger->info("write a user ",['username'=>'jack']);
//
// for ($i=0; $i <10 ; $i++) {
// 	$logger->info("can it do the cards of numbber",array('1'.$i=>$i));
// }
// $logger->warning('Fii');
// $logger->error('error');



//利用他来构建相关的日志
#加载第三方类

 
 
$country_info = COUNTRY_INFO();
// echo '<pre>';
// print_R($country_info);
// echo '</pre>';
// exit;
$where_data = array(
	  'condition' =>array('city_code'=>'110000'),
 // 	 'sort_set'	=>'city_code',
	//  'sort_range'	=>'desc',
	// // 'group_by'	=>'a_id',
	//  'limit' 	=>5,
);


$city_items = $mysql_obj->get_data_by_condition($where_data,'jishigou_common_distrinct','*');
echo '<pre>';
print_R($city_items);
echo '</pre>';
exit;


//四个直辖市方便下面做铺垫
$special_codes = [110000,120000,310000,500000]; 

//导入的sql
function import_sql($data,$table_name=''){
	if(!$data || !$table_name)
		return false;
	$sql .="insert into ".$table_name." (`city_code`,`name`,`upid`,`ctime`) values ";
	$val_save ='';
	foreach($data as $key =>$val){
		 $arr = array();
		 foreach($val as $k =>$v){
		 		$arr[]  =  "'$v'";
		 }
		 $val_save.="(".implode(',', $arr)."),";
	}
	$val_save  = rtrim($val_save,',');
	if($val_save){
		$sql .= $val_save;
	}
	return $sql;
}

//检验是否已经导入了
function get_city_code($upid ='',$table_name=''){
	$upid = intval($upid);
	$table_name = trim($table_name);
	if(!$upid)
		return false;
	global $mysql_obj;
	$sql = "select * from ".$table_name." where upid=".$upid;	 

	$rows = $mysql_obj->fetchAll($sql,$mysql_obj->db_slave);
	return $rows;
}
// config('database.prefix');
// define('IS_GET',Request::instance()->isGet()); 
// define('IS_POST', Request::instance()->isPost());
$table_name ='jishigou_common_distrinct';
#获取对应的编码格式
$list = $mysql_obj->fetchAll("select prov.provinceid as city_code,prov.province as name,common.id as upid  from  bs_provinces as prov left join jishigou_common_distrinct as common on prov.provinceid=common.city_code",$mysql_obj->db_slave);
if($list && is_array($list)){
	$now_time = date('Y-m-d H:i:s');
	foreach($list as $key =>$val){
		if(!$val) continue;
		$city_code = trim($val['city_code']);
		if($city_code && in_array($city_code,$special_codes)){
			 $condition = " provinceid ='".$city_code."' or SUBSTR(provinceid,1,4)='".substr($city_code, 0,4)."'";
		}else{
			$condition= " provinceid = '$city_code'";
		}
		//市的数据
		$sql = "select * from cities where ".$condition;
		 $city_list = $mysql_obj->fetchAll($sql,$mysql_obj->db_slave);
		 if(!$city_list)
		 	continue;
		 $cityArr = []; //每次都需要置空 
		 foreach($city_list as $city){
		 	$cityArr[] = [
		 		'city_code'		=>	trim($city['cityid']),
		 		'name'			=>	trim($city['city']),
		 		'upid'			=>	intval($val['upid']),
		 		'ctime'			=>	$now_time,
		 	];

		 	//读取相关的区域列表
		 	$sql = "select area.*,common.id as upid from areas as area left join ".$table_name." as common on area.cityid =common.city_code where  area.cityid = '".$city['cityid']."'";
		 	//查区域数据
		 	$area_list = $mysql_obj->fetchAll($sql,$mysql_obj->db_slave);
		 	$area_import =[];
		 	$upid = 0;
		 	if(!$area_list)
		 		$area_list = array();
		 	foreach ($area_list as $key => $value) {
		 		if(!$value) continue; //有个别的没有
		 		$area_import[]=[
		 			'city_code'		=>	trim($value['areaid']),
			 		'name'			=>	trim($value['area']),
			 		'upid'			=>	intval($value['upid']),
			 		'ctime'			=>	$now_time,
		 		];
		 		$upid = intval($value['upid']);
		 	}


		 	//查是否已经同步过区域了
		 	$area_exists = get_city_code($upid,$table_name);
		 	if(!$area_exists && $area_list){
		 		//处理插入数据
		 		$sql = import_sql($area_import,$table_name);

		 		$result = $mysql_obj->query($sql,$mysql_obj->db_master);
		 		if($result){//开始插入
		 			echo $city['city']." count:".count($area_list)." code:".$city['cityid']." done success<br />";
		 		}else{
		 			echo  $city['city']." faild <br >/";
		 		}
		 	}else{
		 		echo "area-data ".$city['city']." code:".$city['cityid']." upid:".$upid." 已经同步过了<br />";
		 		
		 	}
		 	
		 }

		 echo "-----------------------------------下面是同步过的市级数据<br />";
		 //判断当前市的数据是否已经同步
		 $have_data = get_city_code($val['upid'],$table_name);
		 if(!$have_data){
		 	$sql = import_sql($cityArr,$table_name);
		 	//同步数据
		 	$ret = $mysql_obj->query($sql,$mysql_obj->db_master);
		 	echo  $val['name']."  插入成功<br/>";
		 }else{
		 	echo $val['name'].":".$val['city_code']." upid:".$val['upid']."  已经同步了<br/>";
		 }
	}

}
?>