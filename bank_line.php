<?php
ini_set("memory_limit", "3000M");
set_time_limit(300);
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
require_once ($dirname."/library/init.inc.php");

//SELECT distinct(city_name) from weliam_unionpay_bank_line WHERE city_code=''
$where_data = array(
	  'condition' =>array('city_code'=>'')
	// 	 'sort_set'	=>'city_code',
	//  'sort_range'	=>'desc',
	// // 'group_by'	=>'a_id',
	//  'limit' 	=>5,
);


$update_table = 'weliam_unionpay_bank_line';
$city_items = $mysql_obj->get_data_by_condition($where_data,$update_table,'distinct(city_name) as city_name');
if(!empty($city_items)){
	$table_name ='jishigou_common_distrinct';
	foreach($city_items as $v){
		$city_name =trim($v['city_name']);
		$city_where = array(
	  		'condition' =>array('name'=>$city_name,'level'=>'3'),
		);
		$cityInfo  =$mysql_obj->get_data_by_condition($city_where,$table_name,'name,city_code,pin_yin,level');
		$city_code ='';
		if($cityInfo){
			$city_code=trim($cityInfo[0]['city_code']);
			$city_code = substr($city_code, 0,4);

			$res = $mysql_obj->update_data(['city_code'=>$city_code],"city_name='$city_name' and city_code=''",$update_table);
			echo $city_name.'-更新成功';
			echo "<br/>";
		}else{
			echo $city_name."未匹配到数据";
		}
		echo "<br/>";
	}
}else{
	echo "暂无数据分行数据更新";
}

?>