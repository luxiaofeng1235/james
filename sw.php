<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');

$s_time = time();

echo '开始时间:'.date('H:i:s',$s_time).PHP_EOL;

//进程数




$worker=[];

//模拟地址

$refer_path = ROOT . 'config/refer.txt';
$url = [];
//从配置中读取自定义的refer地址
if (file_exists($refer_path)) {
  $urls = file($refer_path);
  foreach($urls as &$val){
      $val = str_replace("\r\n",'',$val);
  }
}


$curl=$urls;



// $curl = array_slice($curl, 0,20);

$work_number=34;

//创建进程

for ($i=0; $i < $work_number; $i++) {

 //创建多线程

 $pro=new swoole_process(function(swoole_process $work) use($i,$curl){

    //获取html文件

    $content=curldeta($curl[$i]);
    //写入管道

  $work->write( 'index:'.$i.'--'. $content.PHP_EOL);

 },true);

 $pro_id=$pro->start();

 $worker[$pro_id]=$pro;

}

//读取管道内容

foreach ($worker as $v) {

 echo $v->read().PHP_EOL;

}

//模拟爬虫

function curldeta($curl_arr)

{
 file_get_contents($curl_arr);
 return  $curl_arr.PHP_EOL;

}

//进程回收

swoole_process::wait();
$e_time = time();
echo '结束时间:'.date('H:i:s',$e_time).PHP_EOL;
echo '所用时间:'.($e_time-$s_time).'秒'.PHP_EOL;

?>