<?php
#快速排序
function quickSort($arr=[]){
	if(!$arr)
		return false;
	$baseNum = isset($arr[0]) ? $arr[0] : 1;
	$leftArray = $rightArray = [];
	for ($i = 0;$i<count($arr);$i++){
		if($baseNum > $arr[$i]){
			$leftArray[] = $arr[$i];
		}else{
			$rightArray[] = $arr[$i];
		}
	}
	#递归来调用
	$leftArray = quickSort($leftArray);
	$rightArray = quickSort($rightArray);
	return array_merge($leftArray,array($baseNum) ,$rightArray);
}

#递归累加计数
function sum($n){
	if($n>1){
		return $n+sum($n-1);
	}else{
		return 1;
	}
}

#遍历本地文件夹
function my_scandir($dir)
{
	$files = array();
	if($handle = opendir($dir))
	{
		while (($file = readdir($handle))!== false)
		{
			if($file != '..' && $file != '.')
			{
				if(is_dir($dir."/".$file))
				{
					$files[$file]=my_scandir($dir."/".$file);
				}
				else
				{
					$files[] = $file;
				}
			}
		}
		closedir($handle);
		return $files;
	}
}

#取出来扩展名
function getExt($url = ''){
	if(!$url) return false;
	$arr = parse_url($url);
	$list = basename($arr['path']);
	$ext  = explode(',',$list);
	return $ext[count($ext)-1];
}

#冒泡排序
function bubble_sort($arr=[]){
	$count = count($arr);
//	$temp = 0;
	for ($i =0;$i<$count;$i++){
		for ($j=0;$j<$count-$i;$j++){
			if($arr[$j]>$arr[$j+1]){
				$temp = $arr[$j];
				$arr[$j] = $arr[$j+1];
				$arr[$j+1] = $temp;
			}
		}
	}
	return $arr;
}

#猴子选大王问题
function king($n ,$m){
	$king = range(1,$n);
	$i = 0;
	while (count($king)>1){
		$i+=1;
		$head = array_shift($king);
		if($i%$m!=0){
			array_unshift($king,$head);
		}
	}
	return $king[0];
}

#输入字符进行排序
function sortStr($str){
	$ret = str_split($str);
	$ret = array_flip(array_flip($ret));
	sort($ret);
	return implode('',$ret);
}

#计算菲波那切数列通过递归来实现
function diguiList($num){    //实际上是斐波那契数列
	return $num<2?1:diguiList($num-1)+diguiList($num-2);
}

#解决多线程文件的思路
function writeTask($files =''){
	if(!$files)
		return false;
	#多线程文件
	$fp = fopen($files,"w+");
	if (flock($fp,LOCK_EX)) {
		//获得写锁，写数据
		fwrite($fp, "write something");
		// 解除锁定
		flock($fp, LOCK_UN);
	} else {
		echo "file is locking...";
	}
	fclose($fp);
}

#无限分类实现
function tree($arr,$pid=0,$level=0){
	static $list = array();
	foreach ($arr as $v) {
		//如果是顶级分类，则将其存到$list中，并以此节点为根节点，遍历其子节点
		if ($v['pid'] == $pid) {
			$v['level'] = $level;
			$list[] = $v;
			tree($arr,$v['id'],$level+1);
		}
	}
	return $list;
}

#洗牌游戏
function wash_card($cards_num =54){
	$cards_num = intval($cards_num);
	if(!$cards_num || $cards_num!=54){
		$cards_num = 54; //输入判断
	}
	$cards = $temp = [];
	for ($i =0;$i<$cards_num;$i++){
		$temp[$i] =$i;
	}

	#开始洗牌，利用随机函数来实现，随机出来一个值插入到新的数组中。
	for($i = 0;$i < $cards_num;$i++){
		$index = rand(0,$cards_num-$i-1);
		$cards[$i] = $temp[$index];
		unset($temp[$index]);
		$temp = array_values($temp);
	}
	return $cards;
}