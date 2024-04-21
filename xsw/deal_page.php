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
use QL\QueryList;


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


$pages = array_slice($pages,0 , 10);
$dataList =$urls= [];
//生成页面链接方便进行爬取
foreach($pages as $page){
   //替换相关的关联参数信息
    $url = StoreModel::replaceParam(Env::get('TWCONFIG.API_HOST_COMPLATE'),'pages',$page);
    $dataList[]= [
        'story_link'    =>  $url,
    ];
}
$urls = array_column($dataList,'story_link');
//设置配置细腻
$item = StoreModel::swooleRquest($urls);
$rules = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_ret'];


/**
* @note 获取当前的分页信息
*
* @param $val string 文本信息
* @return  string
*/

function getCurrentPage($html){
    global $rules,$download_path;
    if(!$html){
        return false;
    }
    $content =QueryList::html($html)
                ->rules($rules)
                ->query()
                ->getData();
    $content = $content->all();
    $pageStr  = $content['currentPage'] ?? '';
    //转换成简体
    $pageStr = traditionalCovert($pageStr);
    $pageRet = explode('/',$pageStr);
    $page = 0;
    if(!empty($pageRet) && isset($pageRet[0])){
        $numData  = $pageRet[0] ?? '';
        preg_match('/\d+/',$numData , $matches);
       if(isset($matches[0])){
          $page = $matches[0] ?? 0;
       }
    }
    // echo "-----{$pageStr} \t{$page}\r\n";
    //获取生成的连接信息
    $link_url  = $url = StoreModel::replaceParam(Env::get('TWCONFIG.API_HOST_COMPLATE'),'pages',$page);
    $info['page'] = $page;
    $info['url'] = $link_url;
    $info['save_path'] = $download_path.DS. StoreModel::$page_name.$page.'.'.StoreModel::$file_type;
    return $info;
}
$storeArr;
if(!empty($item)){
    foreach($item as $key => $val){
        //获取当前的连接分页信息
        $pageArr = getCurrentPage($val);
        if(!empty($pageArr)){
            $pageArr['content'] = $val;
            //保存对应的数组信息
            $storeArr[] = $pageArr;
        }
    }
    //保存对应的路径信息
    if(!empty($storeArr)){
        $i = 0;
        foreach($storeArr as $gkey => $gval){
            $i++;
            echo "num = {$i} \t page = {$gval['page']}\t url = {$gval['url']} \t save_path = {$gval['save_path']} compelte\r\n";
        }
    }
}
echo "finish\r\n";

?>