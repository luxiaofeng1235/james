<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :queue.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:队列的实现，通过指定xx->fucntion来实现队列的转发
// ///////////////////////////////////////////////////


require_once(__DIR__.'/library/init.inc.php');

$param_arr = getopt('a:b:c:');
$queuePath = __DIR__ .DS . 'cmdrun';

if (!isset($param_arr['a']) || !isset($param_arr['b'])) {
    echo "useage:php queueu.php --a=class -b=function -c=param" . "\n";
    return ;
}

$class = $param_arr['a']; //类名
$function = $param_arr['b']; //执行的具体方法
$param  =   $param_arr['c'] ?? ''; //执行接收的参数



// 注册自动加载器
// spl_autoload_register('autoload');

//加载当前的类文件
$classFile = $queuePath.DS.$class.'.php';
if(file_exists($classFile)){
    require_once $classFile; //加载当前的类文件
}

//通过类的缩写去加载函数信息变更
$ret = ucfirst($class)::$function($param); //执行对应的函数信息去加载函数
echo '<pre>';
var_dump($ret);
echo '</pre>';
echo "\r\n";
?>