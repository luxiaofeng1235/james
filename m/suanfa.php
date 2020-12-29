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
        return false;
    }
}