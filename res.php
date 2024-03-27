<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :res.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:测试文件，没什么用
// ///////////////////////////////////////////////////


//结束当前进程
function killProcess(){
    posix_setsid ();
    echo "安装信号处理程序...\n";
    pcntl_signal(SIGTERM,  function($signo) {
        // exit(1);
        echo "信号处理程序被调用\n";
    });
    $masterPid = posix_getpid();
    if(!$masterPid){
        echo "no this process\r\n";

    }
    posix_kill($masterPid, SIGTERM);
    echo "killed by pid, pid = {$masterPid}\n";
    echo "分发...\n";
    pcntl_signal_dispatch();
}

$i = 0;
do{
$i++;
sleep(3);
    if($i>5){
        killProcess();//杀掉当前进程
        exit(1);
    }
echo "time:".date('Y-m-d H:i:s')." -- 1111111111\r\n";
}while(true);

