<?php
//显示配置文件信息
$redis = new Redis();
//配置文件信息
$redis->connect("localhost","6379");
$redis->select(0);

$cacheKey = "angle_class";
// $redis->set($cacheKey , "222334343");
$dt = $redis->get($cacheKey);
echo '<pre>';
var_dump($dt);
echo '</pre>';
exit;
// $redis_key ="test:123";
// $ret = $redis->set($redis_key,"11222");
// $aa = $redis->get($redis_key);
// echo '<pre>';
// print_R($aa);
// echo '</pre>';
// exit;
?>