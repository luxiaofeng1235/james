<?php

$merOrderId = '31943333222';
!empty($merOrderId) && $initials = substr($merOrderId, 0, 4);

if($initials!='3194'){
    echo "error";
}else{
    echo "ok";
}
die;

//==只对值进行比较 ===值和类型都需要比较
function getRelativePath($a ,$b){
	$a1Array = explode('/', $a);
	$bArray = explode('/', $b);
	$strPath ='';
	for ($i=0; $i <count($bArray)-2 ; $i++) {
		$strPath.=$a1Array[$i] == $bArray[$i] ? '../' : $bArray[$i].'/';
	}
	return $strPath;
}

//记住一点 静态成员属于类 普通成员属于对象
// echo "\\"; //输出\
$A ='hello12';
function print_A(){
	$A='mysql-admin';
	global $A;
	echo $A;	//重新声明不会就算赋值了也不会改变
}
echo $A;
echo "<br/>";
print_A();

// $redis = new \Redis();
// $redis->connect('localhost',6379);
// $redis->select(10);

// $key = $redis->set('luxiaofeng','1112');


echo "<hr/>";

$a='aa';
$aa= 'bb';
echo $$a;
echo "<br/>";
echo "<hr/>";

$attr = array(1,2,3,4);
while(@list($key,$value) = each($attr))    {
	echo $key."=>".$value."<br>";
}
//只能输出四行
#
// $a = "cc";
// $cc = "dd";
// echo $a=="cc"?"{$$a}":$a;
echo "<hr/>";
$a= 'cc';
$cc ='dd';
echo $a=='cc' ? "{$$a}" : $a;
echo "<hr/>";

$x= 15;
echo $x++; //先赋值再相加 $x= $x+1;
$y = 20;
echo "<br/>";
echo ++$y;
echo "<br/>";
//或者php版本
echo PHP_VERSION;
echo "<br/>";
$a= PHP_INT_MAX+1;
echo $a;
echo "<hr/>";

$a = 0;
while ($a<5){
     switch ($a){
        case 0:
        case 3:$a=$a+2;
        case 1:
        case 2:$a=$a+3;
        default:$a=$a+5;
    }
}
echo $a; //输出应该是10

//采用哈希用户用户来做存储 也可以用range来去范围的数据
function get_hash_table($table,$uid){
	if(!$uid)
		return false;
	$str = crc32($uid); //对数据进行转换32位切换
	if($str<0){//采用哈希的算法来记性
		$hashstring = '0'.substr(abs($str), 0,1);
	}else{
		$hashstring = substr($str, 0,2);
	}
	//返回对应的顺序格式
	return $table.'_'.$hashstring;
}

//处理用户的数据信息切入

//通过range的haxi来进行获取
function range_insert($id,$tbName){
	$dbNum = 1;
	$tbNum = 30;
	//按照一定的规则生成range规则
	$tb = $id%($dbNum*$tbNum)+1;
	if(!$tb) $tb  =0;
	return $tbName.'_'.$tb;
}
echo "<br/>";
#获取表名称根据uid来拆分表
$table_name = range_insert(102368,'uc_members');
echo '<pre>';
print_R($table_name);
echo '</pre>';

//或者我们用md5生成随机算法来计算
$str= '1234561aa3';
// $a =preg_split("/\r\n/", $str);
// echo '<pre>';
// var_dump($a);
// echo '</pre>';
// exit;
echo "<hr/>";
echo get_hash_table('users_member','1');

echo "<hr/>";

$a=0; //赋值判断
if($a++){echo 1;}else{echo 0;}


/**
* @note
*
* @param $n array 初始值计算器
* @return
*/

 //logs_20201121
 //logs_20201123
 //logs_202011
 //logs_2020
function getDataResult($n,$i,$x){
	// $n = $i =$x = 0; //重新赋值
	$a =array();//设置最终的兔子数据
	for ($i=0; $i <11 ; $i++) {
		//穷举搜索
		$a[$i] = 1; //默认是有数据的
		for ($i=0; $i <1000 ; $i++) {
			$n+=$i+1;
			$x= $n%10;
			$a[$x] = 0 ;//表示未找到的d的位置置为0
		}
	}
	for ($i=0; $i <10 ; $i++) {
		if($a[$i]){
			echo "可能在第i个洞";
		}
	}
	return 0;
}

//获取求最大数
function getMaxData($a,$b,$c){
	return $a > $b ? ($a>$c ? $a : $c) : ($b>$c ? $b : $c);
}

define('PAI','3.14');
define('PAI','5');
echo '<pre>';
var_dump(PAI);
echo '</pre>';
// exit;
$cc = 3;
$dd =5;
list($cc,$dd) =array($dd,$cc);
echo '<pre>';
var_dump($dd);
echo '</pre>';
// echo count("abc");
$val=max('string',array(2,5,7),42);
echo '<pre>';
var_dump($val);
echo '</pre>';

//app_2010
//app_2020_12
//app_2020_11
//还回到这个分表问题上，按照用户的hash来进行分表存储，

$uid = 126 ;
echo $uid%25;
// ob_start();
// echo "11222234";
// $buffer =ob_get_contents();
// file_put_contents('access.log', $buffer,FILE_APPEND);

 // curl_setopt($ch, CURLOPT_HEADER, 1);
 // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 // curl_setopt($ch, CURLOPT_BUFFERSIZE, 1230000);
 // curl_setopt($ch ,CURLOPT_BINARYTRANSFER,true);
 // curl_setopt($ch ,CURLOPT_CONNECTTIMEOUT,60);
 // curl_setopt($ch ,CURLOPT_FOLLOWLOCATION, true);
 //
 function addRet(int $a,int $b):int{
 	return $b+$a;
 }
 echo "<br/>";

 echo addRet(41.5, 2.5);

 $amounts_func = function(){return 'function112';};

 echo $amounts_func();
//列表推导法
 $list= [
 	2=>['a','b','c'],
 	3=>['d','e','f'],
 	4=>['g','h','i'],
 	5=>['j','k','l'],
 	6=>['m','n','o'],
 	7=>['p','q','r','s'],
 	8=>['t','u','v'],
 	9=>['w','x','y','z'],
 ];
$t =array_keys($list);
$num1 = 3;
$num2 = 9;
if($num1 && in_array($num1, $t)){
	$diff1 = $list[$num1];
}
if($num2 && in_array($num2, $t)){
	$diff2 = $list[$num2];
}
echo "<hr/>";
for ($i=0; $i <count($diff1) ; $i++) {
	for ($j=0; $j <count($diff2) ; $j++) {
		echo "组合方式：".$diff1[$i].$diff2[$j];
		echo "<br/>";
	}
	echo "<hr/>";
}

$arr = [1,1,2];
$arr= array_unique($arr);
//根据php7来进行参数h
function foo($x){
	$x++;
	echo func_get_arg(0);
}
foo(1);
#对象赋值
getenv('REMOTE_ADDR');
$str = 'xy';
list($x,$y) = (object) new ArrayObject([0,1]);
echo '<pre>';
var_dump($y);
echo '</pre>';
#*/10 2-20 * * *
#netstat -anpl|grep 'php-fpm'|wc -l #查服务器php-fpm进程数量
$aaa  ='usernamearr';

/*
$file = './hello.ext';
$fp = fopen($file, 'r');
$content = fread($fp, filesize($file)); //读取他的长度
$content.='hello,world'.$content;
fclose($fp);
$handle = fopen($file, 'w');
fwrite($handle, $content);
fclose($handle);
*/

//直角三角形
for ($i=1; $i <=9 ; $i++) {
	for ($j=1; $j <=$i ; $j++) {
		echo '*';
	}
	echo "<br />";
}


//杨辉三角形
echo "<hr/>";
for ($i=1; $i <=9 ; $i++) {
	for ($j=1; $j <=(2*$i)-1 ; $j++) {
		echo "&nbsp;&nbsp;".'*';
	}
	echo "<br />";
}

$x =15;
echo $x++; //先复制在计算，索引并没有改变x的值
echo "<br/>";
$y = 20;
echo ++$y; //先计算，所以为21】
echo "<hr/>";

$a = 1;
$b = 2;
function Sum()
{
global $a, $b; //在里面声明为全局变量
$b = $a + $b;
// return $b;
//缺少返回值
}
echo $b;
// unserialize()
// zend疫情 extendsion sapi  application
//
?>