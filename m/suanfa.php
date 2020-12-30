<?php
#根据对应的位置找结果
function two_repeat($nums,$target){
	$arr= [];
	foreach($nums as $key =>$value){
		$num = $target -$value;
		if($num<0) continue;
		if(array_key_exists($num, $arr)){
			$arr[$value] = $key;
		}
	}
	return $arr;
}

#递归累加计数
function sum($n){
	if($n>1){
		return $n+sum($n-1);
	}else{
		return 1;
	}
}
?>

