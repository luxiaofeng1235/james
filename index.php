<?php
/**
* @note 入口文件
*
* @param 
* @return 
*/
//处理函数内部实现调用
function addNameList($list=array()){
	if(!is_array($list))
		return false;
	if($list && isset($list[0])){
		$diff_data=[];
		foreach($list[0] as $v){
			$diff_data = array_merge($v,$diff_data);
		}
		if(count($diff_data)>0){
			foreach($diff_data as $gk => $gv){
				 if(is_array($v)){
				 	 $oderList[]=$gv;
				 }
			}
		}
		return $oderList;
	}
	return [];
}

/**
* @note 翻转字符串算法
*
* @param $num int 输入的字符串
* @return 
*/
function reserveData($num=0){
	if(!$num || $num<0)
		return false;
	$n = 0;
	while ($num!=0) {
		$temp = $num;
		$n = $n%10+$n/10;
		$num = $num/10;
		if($temp!=$n/10)
			return 0;
	}
	return $temp;
}

//判断反转过来的字符串是否相等
function isPalindrome($x){
	if(!$x)
		return false;
	$temp = 0;
	$y = $x; //先赋值进行最后的比较
	while ($x!=0) {
		$temp =$temp*10+$x/10;
		$x = $x/10;
	}
	return intval($temp) == $x ? true: false;
}

//快速排序
function quickSort($arr){
	if(!$arr)
		return false;
	$basenum = $arr[0];
	$count = count($arr);
	$left_array = $right_array = array();
	for ($i=0; $i <$count ; $i++) { 
		if($basenum>$arr[$i]){
			$left_array[] = $arr[$i];
		}else{
			$right_array[] = $arr[$i];
		}
	}
	$right_array = quickSort($right_array);
	$left_array = quickSort($left_array);
	return array_merge($left_array,array($basenum),$right_array);
}

//从一个扩展名中获取对应的后缀
function getExt($url){
	$result = parse_url($url);
	$data = basename($result['path']);
	$ret = explode('.', $data);
	return $ret[count($ret)-1];
}

//n为猴子 m是需要判断的那个数
function king($n,$m){
	$money = range(1,$n);
	$i = 0;
	while (count($money)>1) {
		$i+=1; //进来一次累加一次
		//让猴子出列
		$head = array_shift($money);
		//判断是否为n的倍数
		if($i%$m!=0){
			array_push($money, $head);
		}
	}
	return $money[0]; //返回第一个猴子的数量
}

//定义一个数组
$list = [
	[ 
		1=>[3,5],
		2=>[
				[1,3],
				[4,5]]
			],
	[3,5] ,
];


$b= $a=5;
$f = $a++; //现在c的值为
echo $a;
echo "<br/>";
echo $f;die;
$dirname = str_replace('\\','/',dirname(__FILE__));

require_once $dirname.'/function.php';

//截取无乱码 mb_substr
$aaa  = checkdata();
 


###函数嗲用引用 
$arr = addNameList($list);
echo '<pre>';
print_R($arr);
echo '</pre>';
echo "<hr/>";
$aa = "123abc";
//翻转字符
$str = strrev($aa);
echo '<pre>';
print_R($str);
echo '</pre>';
if(preg_match("/\d{1,10}/", $aa,$matches)){
	if($matches && isset($matches[0])){
		echo '<pre>';
		print_R($matches[0]);
		echo '</pre>';
		 
	}
}

$i = 2;

$a=++$i;
$b = $i++;
echo $a;
echo "<br/>";
echo $b;
echo "<hr/>";

$check_str= "135";
$res = isPalindrome($check_str);
echo '<pre>';
var_dump($res);
echo '</pre>';
exit;


?>