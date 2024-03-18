<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';


$s_time = time();
echo '开始时间:'.date('H:i:s',$s_time).PHP_EOL;

//进程数
$work_number=6;
$worker=[];
//模拟地址
$curl=[
    'http://www.baidu.com',
    'http://www.fumubang.com',
    'https://www.php.cn/faq/709717.html',
    'https://www.dbs724.com/315797.html',
    'https://www.cnblogs.com/robin201711/p/8516844.html',
    'https://www.shiwaiyun.com/article/post/194660.html',
];


//创建进程
for ($i=0; $i < $work_number; $i++) {
        //创建多线程
        $pro=new swoole_process(
            function(swoole_process $work)
            use($i,$curl){
            //获取html文件
            $content=curldeta($curl[$i]);
            //写入管道
            $work->write($content.PHP_EOL);
     },true);
     $pro_id=$pro->start();
     $worker[$pro_id]=$pro;
}

//读取管道内容
foreach ($worker as $k =>$v) {
    //保存文件
    echo $v->read().PHP_EOL;
}


//模拟爬虫

function curldeta($curl_arr)

{ //file_get_contents

     $a = file_get_contents($curl_arr);
     echo $curl_arr.'---success'.PHP_EOL;
     // echo $a.PHP_EOL;
}


//进程回收

swoole_process::wait();
$e_time = time();
echo '结束时间:'.date('H:i:s',$e_time).PHP_EOL;
echo '所用时间:'.($e_time-$s_time).'秒'.PHP_EOL;
?>