<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :deal_page.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:处理列表分页并保存到对应的目录去
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$limit = Env::get('TWCONFIG.RUN_LIST_PAGE');
if(!$limit){
    exit("请输入完本的起止页码数");
}

//创建目录
$download_path =Env::get('SAVE_PAGE_PATH');//下载路径;
if(!is_dir($download_path)){
    createFolders($download_path);
}


$size  = explode(',',$limit);
$pages = range($size[0] , $size[1]);


$pages = array_slice($pages,0 , 1);

$dataList =$urls= [];
//生成页面链接方便进行爬取
foreach($pages as $page){
   //替换相关的关联参数信息
    $url = StoreModel::replaceParam(Env::get('TWCONFIG.API_HOST_COMPLATE'),'pages',$page);
    $urls[]= $url;
}
//设置配置细腻
$item = StoreModel::swooleRquest($urls);
if(!empty($item)){
    $num = 0;
    foreach($item as $key => $val){
        $num++;
        $page = $pages[$key+1] ?? 1;
        //保存的文件名生成规则
        $save_file = $download_path.DS. StoreModel::$page_name.$page .'.'.StoreModel::$file_type;
        echo "num = $num \t url = {$urls[$key]}  \t path = {$save_file} \t complate \r\n";
    }
}
echo "finish\r\n";

?>