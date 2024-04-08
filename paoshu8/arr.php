<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$file = file('./zhangjie.txt');
foreach($file as &$v){
    $v = str_replace("\r\n" , '',$v);
}
$item = array_slice($file, 0,1000);
foreach($item as $key =>$val){
    $shell_cmd="cd /www/wwwroot/work_project/novelProject/paoshu8/ &&  /www/server/php/72/bin/php gather_info_local.php {$val} >> run_range.out 2>&1 &";
    echo $shell_cmd;
     shell_exec($shell_cmd);
    echo "\r\n";
}
?>