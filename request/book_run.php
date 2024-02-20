<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_run.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:处理需要执行的章节信息目录
// ///////////////////////////////////////////////////

ini_set("memory_limit", "5000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

if(is_cli()){
    $store_id = $argv[1] ?? 0;
}else{
    $store_id  = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
}
if(!$store_id){
    echo '请输入需要处理的小说ID';
    exit();
}

$table_novel_name ='ims_novel_info';//章节信息
$table_chapter_name ='ims_chapter';//章节表
// $info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_novel_name);
// if(!$info){
//     echo '处理的小说信息不存在';
//     exit();
// }
//根据小说区取章节
$chapter_info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_chapter_name,'link_url');
echo '<pre>';
var_dump($chapter_info);
echo '</pre>';
exit;
?>