<?php

$str =strstr('langwan@thizlinux.com.cn','@');

echo '<pre>';
print_R($str);
echo '</pre>';
exit;
echo '<pre>';
var_dump($str);
echo '</pre>';
exit;
$x= 1;
++$x; #表达式 2
$y =$x++;
echo '<pre>';
var_dump($y);
echo '</pre>';
exit;
$number = 1234.56; //设置对应的内部函数
$a= '1,2,3,4,5';
echo str_word_count($a);
exit;
$list = 'www.baidu.com';
echo '<pre>';
var_dump(strrev($list));
echo '</pre>';
exit;
// 让我们打印 en_US locale 的国际化格式
// $number = 1234.56;
setlocale(LC_MONETARY,"en_US");
echo money_format("The price is %i", $number);
exit;
echo '<pre>';
var_dump($dt);
echo '</pre>';
exit;
$a= "abc123ccd"+5;
echo '<pre>';
var_dump($a);
echo '</pre>';
exit;
@unlink('res.txt'); #不存在不会报错
@print($aaa);##也不会报错
@include("/tmp/access.log"); #不会报错
$plist =array();
// @foreach($plist as $key =>$val){ 
// 	echo '<pre>';
// 	var_dump($val);
// 	echo '</pre>';
// 	echo "<hr/>";ddhesd
// }
// unset($list);
exit;
define('A', '1');
define('A', '2');

echo '<pre>';
var_dump(A);
echo '</pre>';
exit;

function getResutInfo($month=0){
	$one = 1;
	$two = 1;
	$sum= 0;
	if($month<3)
		return false;
	for ($i=2; $i <$month ; $i++) { 
		$sum = $one+$two;
		$one = $two;
		$two = $sum;
	}
	echo $month."个月后有".$sum."对兔子";
}
//根据数组对age数组进行排序做降序处理
$arr= array(
	array('name'	=>'张三','age'	=>12),
	
	array('name'	=>'jamos','age'	=>80),
	array('name'	=>'data','age'	=>34),
	array('name'	=>'list','age'	=>10),
	array('name'	=>'lily','age'	=>18),
	array('name'	=>'liuck','age'	=>19),
);
$packNum = array();
array_map(function($item) use(&$packNum){
	$packNum[] = $item['age'];
},$arr);

$sql ="select ifnull( (select distinct disce from xx limit 1,1) , null) as secondearvae";
//删除重复的电子邮箱，保留最小的那个
$sql ='delete from persion where id not in (select id from (select min(id) as id from persion group by email) as t )';

// kill -9 $(ps -ef|grep "php-fpm"|awk 'print ${1}') 杀掉当前的PHP进程，全部退出
array_multisort($packNum,SORT_DESC,$arr); //根据数组大小来进行排序
array_multisort($packNum,SORT_DESC,$arr);
// array_multisort($packNum,SORT_DESC,$arr);
var_dump($arr);
// echo '<pre>';
// var_dump($arr);
// echo '</pre>';
// exit;

//使用cdn加速 单独服务器存储 图片分域存储 第三方七牛服务器
//
//做三个集合 粉丝表 db.fans  关注表db.attatech  互粉 db.floower
//redis做交集处理 zmembets test1 a zmemebtg test 1aa  sinter tet1 tes 1 sunion test1 test2 
//lpush product  1 
//lpurhs product 2
//lrange product 2,3
//处理对应的根据数组计算位置
//https的端口号为443,redis 6379 sphinx 9312
//利用sql的eplain slow process_log 线上慢查询日志 以及网络监测工具来进行
//	
//	multi exec discard watch
////用户表 角色表 菜单表  用户角色表 菜单角色表 user role menu  UserRole MenuRole
//测试的公募 watchtes和xproxy来进行 主要看cpu的时间 wall时间 call等待次数等
////有序集合添加相同的值 他的值不会变
function two_secode($nums,$target){
	$arr= [];
	// gethostname();
	foreach($nums as $key =>$value){
		$num = $target -$value;
		if($num<0) continue;
		if(array_key_exists($num, $arr)){
			$arr[$value] = $key;
		}
	}
	return $arr;
}



//递归嗲用累加，如果原始就用for或者其他的来处理
function sum($n){
	if($n>1){
		return $n+sum($n-1);
	}else{
		return 1;
	}
}
?>

