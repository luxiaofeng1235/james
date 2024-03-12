<?php

//统计已经跑的脚本数据
ini_set("memory_limit", "5000M");
// set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

$between_where = [
    [0,20000],[20000,40000],[40000,60000],[60000,80000],
    [80000,100000],[100000,120000],[120000,140000],
    [140000,160000],[160000,180000],[180000,200000],[200000,300000]
];
$is_async = 0;//未同步的数据
$run_time_count = 0;
foreach($between_where as $key =>$val){
    if(!$val || !isset($val[0]) || !isset($val[1]))
        continue;
    $sql = "SELECT count(store_id) as num FROM ".Env::get('APICONFIG.TABLE_NOVEL')." where is_async =".$is_async. " and store_id > ".$val[0]." and store_id < ".$val[1];
    $info  = $mysql_obj->fetch($sql , 'db_slave');
    $ts_count = $info[0] ?? 0;
    $run_time_count+=$ts_count;//总数
    echo $val[0].'-'.$val[1]." nend to run nums：".$ts_count.PHP_EOL;
}
echo "all-no-run nums：".$run_time_count."\r\n";
echo "finish\r\n";
?>
