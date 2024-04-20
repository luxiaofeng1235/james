<?php
/*
 * 同步跑书吧最新入库和最新更新的章节信息

 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
$url = Env::get('APICONFIG.PAOSHU_HOST');

##获取首页的基础信息
$pageList = NovelModel::cacheHomeList($url);
if(!$pageList){
    exit("获取页面内容为空，请稍后重试\r\n");
}

//定义采集规则：
//最新更新章节循环的class
$range_update  = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_update'];
//最新书籍入库循环的class
$range_ruku  = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_ruku'];
//最近更新的列表
$update_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['update_list'];
//最新入库的规则
$ruku_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['ruku_list'];


$html  =readFileData('./1.html');

$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['detail_url'];
$data = QueryList::html($html)
        ->rules($rules)
        ->query()
        ->getData();

$data = $data->all();

echo '<pre>';
print_R($data);
echo '</pre>';
die;

echo '<pre>';
print_R($content);
echo '</pre>';
exit;

//最新更新的列表
$update_list = QueryList::html($pageList)
                ->rules($update_rules)
                ->range($range_update)
                ->query()
                ->getData();
// dd($update_list);
$update_list = $update_list->all();

//最新入库的列表
$ruku_list = QueryList::html($pageList)
                ->rules($ruku_rules)
                ->range($range_ruku)
                ->query()
                ->getData();
$ruku_list = $ruku_list->all();

//合并数据


$novelList = array_merge($update_list , $ruku_list) ;

$novelList = array_filter($novelList);
if(!$novelList){
    exit("暂无可用章节信息");
}





//保存的客户端
$files = Env::get('SAVE_HTML_PATH').DS.'detail_1235.'.NovelModel::$file_type;

$urls = array_column($novelList,'story_link');

$urls = array_slice($urls, 0 , 1);
$list = curl_pic_multi::Curl_http($urls);

echo '<pre>';
print_R($list);
echo '</pre>';
exit;
foreach($list as $key =>$val){

}


// foreach($novelList  as $val){
//     if(!$val) continue;
//     //存储保存的路径信息
//     $file_path = Env::get('SAVE_HTML_PATH').DS.'detail_'.$val['story_id'].'.'.NovelModel::$file_type;
//     if(!file_exists($file_path)){
//           //请求接口信息
//           $contents = curl_pic_multi::Curl_http([$val['story_link']]);
//           if(!empty($contents)){
//             //写入文件信息
//                 echo '<pre>';
//                 print_R($contents);
//                 echo '</pre>';
//                 exit;
//                 writeFileCombine($file_path , $contents);
//                 echo "url = {$val['story_link']} path = $file_path \r\n";
//           }
//     }
// }

echo "====================插入/更新同步网站数据,共" . count($novelList) ."本小说\r\n";

echo '<pre>';
print_R($novelList);
echo '</pre>';
exit;

?>