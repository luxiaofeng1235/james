<?php
#快速排序
function quickSort($arr=[]){
	if(!$arr)
		return false;
	//把第一个值取出来和下面的来进行比较
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
function scanDir($dir)
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
                    $files[$file]=scanDir($dir."/".$file); //递归调用
                }else
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
	//解析URL对相关的数据进行判断优化，切割数据字符串。
	$arr = parse_url($url);
	$list = basename($arr['path']);
	$ext  = explode(',',$list);
	return $ext[count($ext)-1];
}

#冒泡排序
function bubble_sort($arr=[]){
	$count = count($arr);
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
function king(int $n ,int $m){
    //主要处理对应的猴子问题，对修改的问题进行优化
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
function sortStrNew($str){
    $ret= str_split($str);
    $result = array_flip(array_flip($ret));
    sort($result);
    return implode('',$result);
}

#斐波那契数列递归
function feibonaqie($num = 0){
    return $num < 2 ?  1 : feibonaqie($num-1)+feibonaqie($num-2);
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

#回文数判断：判断回文数是正反念都是一个数
//判断反转过来的字符串是否相等
function is_Palindrome($x = 0){
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

#插入排序
function insert_sort($arr=[])
{
	//区分哪部分是已经排序好的
	//哪部分是没有排序的
	//找到其中一个需要排序的元素
	//这个元素就是从第二个元素开始，到最后一个元素都是这个需要排序的元素
	//利用循环就可以标志出来
	//i循环控制，每次需要插入的元素，一旦需要插入的元素控制好了，
	//间接已经将数组分成了2部分，下标小于当前的（左边的），是排序好的序列
	for ($i = 1, $len = count($arr); $i < $len; $i++) {
		//获得当前需要比较的元素值
		$tmp = $arr[$i];
		//内层循环控制比较并插入
		for ($j = $i - 1; $j >= 0; $j--) {
			//$arr[$j],需要插入的元素，$arr[$j],需要比较的元素
			if ($tmp < $arr[$j]) {
				//发现插入的元素要小，互换位置
				//将后边的元素与前面的元素交换
				$arr[$j + 1] = $arr[$j];
				//将前面的数设置为当前需要交换的数
				$arr[$j] = $tmp;
			} else {
				//如果碰到不需要移动的元素
				//由于是已经排序好的数组，则前面的就不需要再次比较了
				break;
			}
		}
	}
}

#求三个值中的最大值计算
function getMaxNumber($x , $y , $z){
    return $x > $y ?($x>$z ? $x : $z) : ($y>$z ?$y :$z);
}