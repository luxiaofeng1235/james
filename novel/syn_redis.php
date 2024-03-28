<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
require_once dirname(__DIR__).'/library/file_factory.php';
use QL\QueryList;
$exec_start_time = microtime(true);
$store_id = isset($argv[1]) ? $argv[1] : 0;
if(!$store_id){
    exit("no id\r\n");
}
$t = NovelModel::getRedisProId($store_id);
echo '<pre>';
print_R("store_id = ".$t."\r\n");
echo '</pre>';
echo "===============================\r\n";
$info = NovelModel::getRedisBookDetail($store_id);
dd($info);


?>
