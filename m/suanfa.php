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