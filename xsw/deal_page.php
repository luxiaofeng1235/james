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
$exec_start_time = microtime(true);

$limit = Env::get('TWCONFIG.RUN_LIST_PAGE');
if(!$limit){
    exit("请输入完本的起止页码数");
}

//创建目录
$download_path =Env::get('SAVE_PAGE_PATH');//下载路径;
if(!is_dir($download_path)){
    createFolders($download_path);
}

$cateId = isset($argv[1]) ? $argv[1] : 1;
$size  = explode(',',$limit);
$pages = range($size[0] , $size[1]);


$urls=[];
//cate_id = 1 已同步
//cate_id =2 已同步
//cate_id = 3 已同步
//cate_id = 4 已同步
//cate_id = 5 已同步
//cate_id = 6 已同步
//cate_id = 7 已同步
// $pages = array_slice($pages ,0, 100); //按照数据进行分割
/*
0 -100 --处理中-已完成
100 -100 -处理中 -已完成
200 -100 -处理中 - -已完成
300 -100 -处理中 --已完成
400 -100 -处理中  --已完成
 */
// $items =array_chunk($pages, 100);
// echo '<pre>';
// print_R($items);
// echo '</pre>';
// exit;
foreach($pages as $page){
    $save_file = $download_path.DS. StoreModel::$page_name.$page.'.'.StoreModel::$file_type;
    $url = StoreModel::replaceParam(Env::get('TWCONFIG.API_HOST_COMPLATE'),'pages',$page);
    $url = StoreModel::replaceParam($url,'cateId',$cateId);
    $httpData = parse_url($url);
    $urlPath = $httpData['path']  ?? '';
    $dataList[$urlPath]=[
        'page'  => $page,
        'url_path'  => $urlPath ,
        'story_link' =>$url
    ];
}

//按照一定的长度去分割数组，防止一次性请求数据量太大
$apiList = array_chunk($dataList, 100);
$get_url = reset($dataList);
$urlArr = parse_url($get_url['story_link']);
$referer_url = $urlArr['scheme']  . '://' .  $urlArr['host'];

if(!$apiList){
    exit("no data\r\n");
}
foreach($apiList as  $gkey =>$value){
    echo "current page = ".($gkey+1)."  \r\n";
    $urls = array_column($value,'story_link');
    //转换数组
    $returnList = double_array_exchange_by_field($value,'url_path');
    //设置请求的url信息
    $item = StoreModel::swooleRquest($urls);
    $item = StoreModel::swooleCallRequest($item,$returnList, $referer_url, 'story_link',2);
    $successNum = 0;
    if(!empty($item)){
        $i = 0;
        foreach($item as $key =>$val){
            $i++;
            $page = str_replace('/list/','',$key);
            $page = str_replace('.html','',$page);
            $save_path  = $download_path.DS. StoreModel::$page_name.$page.'.'.StoreModel::$file_type;
            // echo $save_path."\r\n";
            if(!empty($val)){
                $successNum++;
            }
            //每次覆盖不追加文件
            writeFileCombine($save_path, $val);
            echo "num = {$i} \t page = {$page}\t strlen = ".strlen($val)."\t url = {$referer_url}{$key} \t save_path = {$save_path} compelte\r\n";
        }
    }
    echo "***************************************************\r\n";
}


$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "共爬取下来的页面总数量为：{$successNum} \r\n";
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "finish\r\n";

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
    // $content =QueryList::html($html)
    //             ->rules($rules)
    //             ->query()
    //             ->getData();
    // $content = $content->all();
    // $pageStr  = $content['currentPage'] ?? '';
    // //转换成简体
    // $pageStr = traditionalCovert($pageStr);
    // $pageRet = explode('/',$pageStr);
    // $page = 0;
    // if(!empty($pageRet) && isset($pageRet[0])){
    //     $numData  = $pageRet[0] ?? '';
    //     preg_match('/\d+/',$numData , $matches);
    //    if(isset($matches[0])){
    //       $page = $matches[0] ?? 0;
    //    }
    // }
    // // echo "-----{$pageStr} \t{$page}\r\n";
    // //获取生成的连接信息
    // $link_url  = $url = StoreModel::replaceParam(Env::get('TWCONFIG.API_HOST_COMPLATE'),'pages',$page);
    // $info['page'] = $page;
    // $info['url'] = $link_url;
    $info['save_path'] = $download_path.DS. StoreModel::$page_name.$page.'.'.StoreModel::$file_type;
    return $info;
}
?>