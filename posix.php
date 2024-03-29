<?php
posix_setsid ();
// echo "安装信号处理程序...\n";
pcntl_signal(SIGTERM,  function($signo) {
    // exit(1);
    echo "信号处理程序被调用\n";
});


// echo "为自己生成SIGHUP信号...\n";
// // $masterPid = posix_getpid();
$cmd="ps aux | grep 'res.php' |grep -v grep|awk '{print $2}'|sort -n" ; //h
exec($cmd,$out,$ret );
$masterArr = $out ?? [];
$pid  = $masterArr[0]?? 0;
if(!$pid){
    exit("no process\r\n");
}
posix_kill($pid, SIGTERM);


// if($masterArr){
//     foreach($masterArr as $masterPid){

//         echo "killed by pid , masterPid = {$masterPid}\n";
//     }
// }
echo "分发...\n";
pcntl_signal_dispatch();
?>