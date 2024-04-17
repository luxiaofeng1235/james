<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$exec_start_time = microtime(true);
$startMemory = memory_get_peak_usage();

if(is_cli()){
    $url = isset($argv[1]) ? trim($argv[1]) : 0;
}else{
    $url  = isset($_REQUEST['url']) ? trim($_REQUEST['url']) : 0;
}
if(!$url){
    echo '请选择要抓取的内容的url';
    exit();
}
$link_stroy = trim($url);

$cc = NovelModel::cacheStoryDetail($link_stroy);//转换章节信息
$arr = traditionalCovert($cc);
echo '<pre>';
print_R($arr);
echo '</pre>';
exit;

$cc = NovelModel::getUrlById($link_stroy);
echo '<pre>';
print_R($cc);
echo '</pre>';
exit;

$info = webRequest($link_stroy,'GET');
echo '<pre>';
print_R($info);
echo '</pre>';
exit;

?>