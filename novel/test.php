<?php
$s_time = time();

echo '开始时间:'.date('H:i:s',$s_time).PHP_EOL;

//进程数
$work_number=3;
//
$worker=[];

//模拟地址

$curl=[
    'http://www.baidu.com',
    'http://www.fumubang.com',
    'https://www.php.cn/faq/709717.html',
];

//单线程模式

// foreach ($curl as $v) {

// echo curldeta($v);

// }


//创建进程
for ($i=0; $i < $work_number; $i++) {
        //创建多线程
        $pro=new swoole_process(function(swoole_process $work) use($i,$curl){
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
    $filename = './info_' .($k+1).'.html';
    //保存文件
    file_put_contents($filename,$v->read());
    echo $v->read().PHP_EOL;
}


//模拟爬虫

function curldeta($curl_arr)

{ //file_get_contents

     //echo $curl_arr.'---'.PHP_EOL;
     $a = file_get_contents($curl_arr);
     echo $a.PHP_EOL;
}


//进程回收

swoole_process::wait();
$e_time = time();
echo '结束时间:'.date('H:i:s',$e_time).PHP_EOL;
echo '所用时间:'.($e_time-$s_time).'秒'.PHP_EOL;
?>