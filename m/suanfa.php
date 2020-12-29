<?php
###本文件主要涉及一些算法的常用操作计算

#获取当前的文件的相对路径
function getRelativePath($a, $b){
    $aArray = explode('/' ,$a);
    $BArray = explode('/', $b);
    $realPath='';
    for ($i = 0 ;$i<count($BArray);$i++){
        $realPath .=$aArray[$i] = $BArray[$i] ? '../' : $BArray[$i].'/';
    }
    if(!$realPath)
        return false;
    return $realPath;
}

#从当前连接中获取扩展名
function getExt($url=''){
    if(!$url)
        return false;
    $arr = parse_url($url);
    $data = basename($arr['path']);
    $extension = explode('.' , $data);
    return $extension[count($extension)-1];
}

#通过range的来进行分表的一个规则
function range_insert($id,$tbName){
    $dbNum = 1;
    $tbNum = 30;//定义三十张表
    //按照一定的规则生成range规则
    $tb = $id%($dbNum*$tbNum)+1;
    if(!$tb) $tb  =0;
    return $tbName.'_'.$tb;
}

#获取三个数中的最大值的快捷计算
function getMaxVal($a , $b , $c){
    return $a>b ? ($a >$c ? $a : $b) :($b > $c ? $b : $c);
}

#累加的递归调用
function getSumData(int $n =0){
    if($n>1){
        return $n+getSumData($n-1);
    }else{
        return 1;
    }
}

#判断当前的是否为回文数
function dataIsPeriod($x =0){
    if(!$x)
        return false;
    $temp = 0;
    $y = $x; //先赋值方便来比较
    while($x!=0){
        $temp+=$temp*10+$x/10;
        $x =$x/10;
    }
    return intval($temp) == $y ? true : false;

}

#猴子问题
function king($n , $m){
    $monkey = range(1,$n);
    $i = 0; //设置一个计数器
    while (count($monkey)>1){
        $i+=1;//进来一次累加一次
        $head = array_shift($monkey); //每次都默认踢出去一个
        if($i % $m !=0){
            array_unshift($monkey,$head);
        }
    }
    if(isset($monkey[0])){
        return $monkey[0];
    }else{
        return false;
    }
}

#快速排序的方法
function quickSort($list=[]){
    if(!$list)
        return false;
    $baseNum = $list[0];
    $leftArray = $rightArray = [];
    for($i = 0;$i <count($list);$i++){
        if($baseNum[$i]>$list[$i]){
            $leftArray[] = $list[$i];
        }else{
            $rightArray[] = $list[$i];
        }
    }
    $leftArray = quickSort($leftArray);
    $rightArray = quickSort($rightArray);
    return array_merge($leftArray,array($baseNum),$rightArray);
}

// 冒泡排序
function bubble_sort(&$arr){
    if(!$arr) return [];
    for ($i=0,$len=count($arr); $i < $len; $i++) {
        for ($j=1; $j < $len-$i; $j++) {
            if ($arr[$j-1] > $arr[$j]) {
                $temp = $arr[$j-1];
                $arr[$j-1] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
    }
}

//输入一串字符，并按照顺序进行排序
function strSort($str){
    $arr = str_split($str);
    $res = array_flip(array_flip($arr));
    sort($res);
    return $res;
}


//遍历文件夹
function listDir($dir =''){
    if(!$dir)
        return false;
    $files = [];
    if($handle =opendir($dir)){
        //读取文件夹判断
        while ($file =readdir($dir)!==false){
            if(is_dir($file)){
                if($file!='.' && $file!='..'){
                    $files[]=listDir($dir.'/'.$file);
                }else{
                    $files[] = $file;
                }
            }
        }
        closedir($handle);//关闭目录
    }
    return $files;
}

#递归无限分类的主要处理过程
function tree($arr=[],$pid=0,$level=0){
    static $cateArr = [];
    foreach ($arr as $v) {
        if($v['pid'] == $pid){
            $v['level'] = $level;
            $cateArr[] = $v;
            tree($arr, $v['id'] , $level+1);
        }
    }
    return $cateArr;
}
#利用条件运算符的嵌套来完成此题：学习成绩> =90分的同学用A表示，60-89分之间的用B表示，60分以下的用C表示。
function actionGetScore($score=''){
    $score = (int) $score;
    if($score<0 || $score>100)
        return false;
    $result = $score>=90 ?'A':($score>=60 ? 'B' : 'C');
    return  $result;
}