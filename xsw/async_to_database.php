<?php
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :deal_page.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:自动把JSON文件里的数据追加到数据库中去
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
use Yurun\Util\HttpRequest;

$page = 1;
$cateId = isset($argv[1]) ? $argv[1] : '';
if(!$cateId){
  exit("请输入要处理的分类ID\r\n");
}
$json_file = Env::get('SAVE_CACHE_INFO_PATH') .DS .StoreModel::$detail_page . $cateId . '_'. $page.'.json';
echo "json_file = $json_file\r\n";
if(!file_exists($json_file)){
    return '当前文件不存在，请稍后重试'.PHP_EOL;
}
$json_data = readFileData($json_file);
if(!$json_data) {
    echo "获取小说分页信息失败\r\n";
    return false;
}
$storyList= json_decode($json_data,true);
if(!$storyList){
  echo "暂无小说数据，请稍后重试\r\n";
  return false;
}

//检测当前文件是否存在此本书
foreach($storyList as $value){
    if(!$value) continue;
    //清洗对应的数据信息，去空，转义等等，方便进行计算
   $arr = StoreModel::combineNovelHandle($value);
   dd($arr);


   echo '<pre>';
   print_R($third_update_time);
   echo '</pre>';


}

?>