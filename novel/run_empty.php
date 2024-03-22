<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :process_url.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:通过代理IP伦旭获取当前未同步的书籍章节到本地
// ///////////////////////////////////////////////////
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
$exec_start_time = microtime(true);
//校验代理IP是否过期
if(!NovelModel::checkMobileEmptyKey()){
   exit("代理IP已过期，key =".Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA')." 请重新拉取最新的ip\r\n");
}

//同步的书籍ID信息
$store_id = isset($argv[1])  ? intval($argv[1]) : 0;
if(!$store_id){
    exit("please input your store_id.... \r\n");
}
$table_novel_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
$where_data = 'is_async = 1'; //只对已经同步得来进行计算
//查询小说的基本信息
$sql = "select title,cover_logo,author,pro_book_id,store_id,empty_status from ".$table_novel_name." where store_id = ".$store_id;
echo "sql={$sql} \r\n";
$info =$mysql_obj->fetch($sql , 'db_slave');
if(!empty($info)){
    extract($info);

    //判断是否有关联的书本ID
    if(!$pro_book_id){
        $empty_status!=1 &&  updateEmptyStatus($store_id); //更新状态
        echo  '并无关联的小说ID'.PHP_EOL;
        exit;
    }

    //同步一下图片，直接取
    $md5_str= NovelModel::getAuthorFoleder($title,$author);
    echo "book_md5：".$md5_str."\r\n";
    $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;
    $list = readFileData($json_path);
    if(!$list){
        $empty_status!=1 &&  updateEmptyStatus($store_id); //更新状态
        echo "当前读取章节目录为空\r\n";
        exit;
    }
    //解析章节列表
    $chapter_list = json_decode($list,true);
    //当前的存储的小说的目录信息
    $txt_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $dataList= [];
    if(!$chapter_list){
        $empty_status!=1 &&  updateEmptyStatus($store_id); //更新状态
        exit("暂无关联章节json \r\n");
    }
    $i = 0;
    //构造函数处理广告
    $removeAdInfo = function($arr){
        foreach($arr as &$val){
            $val['link_name'] = $val['chapter_name'];
        }
        //移除广告章节
        $list = NovelModel::removeAdInfo($arr);
        return $list;
    };

    //处理广告并移除关联章节
    $chapter_list = $removeAdInfo($chapter_list);

    foreach($chapter_list as $val){
        //当前的章节路径的名称
        $filename =$txt_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
        $content = readFileData($filename);
        if(!$content ||$content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  || !file_exists($filename)){
            //替换调首字母首字母信息
            $link  =str_replace(Env::get('APICONFIG.PAOSHU_HOST'),'',$val['chapter_link']);
            $chapterInfo['file_path'] = $filename;
            //$chapterInfo['mobile_url'] = $val['mobile_url'];
            $chapterInfo['link_url'] = $val['chapter_link'];
            $chapterInfo['link_str'] = $link;
            $chapterInfo['link_name'] = $val['chapter_name'];
            $dataList[] =   $chapterInfo;
        }else{
            $i++;
        }
    }
    //这里没有说明已经全部抓取下来了
    if(!$dataList){
        $empty_status!=1 &&  updateEmptyStatus($store_id); //更新状态
        echo "book_name：{$info['title']}  pro_book_id：{$info['pro_book_id']}  不需要轮询抓取了，章节已经全部抓取下来了\r\n";
        exit();
    }
    $default_num = count($dataList); //剩余执行的总数


    // $tmp_size = 10; //每次定义20个步长去处理
    // $items = array_slice($dataList , 0, $tmp_size); //测试后期删掉
    // echo "共需要处理的空章节总数--步长按照{$tmp_size}来算的:".count($items).PHP_EOL;
    //测试
    $dataList = array_slice($dataList, 0 ,100);

    //转换成移动端的连接地址:默认的转换地址为：M*N  移动端至少3页，也就是一个URL需要请求N*3 至多三个链接，最多一个链接，需要控制好长度
    $dataList = NovelModel::exchange_urls($dataList,$store_id,'empty');
    // echo '<pre>';
    // print_R($dataList);
    // echo '</pre>';
    // exit;
    //统计下当前的跑出来的数据情况
    echo "-----------------------------\r\n";
    // $curlMulti = new curl_pic_multi();
    $limit_num = Env::get('LIMIT_EMPTY_SIZE'); //默认配置的200个一次请求
    //$limit_num = 200;
    $items = array_chunk($dataList, $limit_num);
    $all_count = count($dataList);
    $save_list = [];
    //轮训处理获取关联的数据信息
    $success_num= 0;
    $insert_data = [];
    $o_success_num = 0;
    foreach($items as $k =>$v){
        //抓取远端地址并进行处理
        $curl_contents= NovelModel::getHtmlData($v);
        if(!$curl_contents)
            continue;
        $success_num = getSuccessTimes($curl_contents);
        $o_success_num+=$success_num;
        // echo '<pre>';
        // print_R($curl_contents);
        // echo '</pre>';
        // exit;
        // dd($curl_contents);
        //保存对应的章节处理信息
        NovelModel::saveLocalContent($curl_contents);
        sleep(1);
    }

    $sy_empty_count = $default_num - $o_success_num; //剩余的总数统计
    $exists_count = $i+$o_success_num;//已存在的统计 ：之前存在的+执行成功的
    if($sy_empty_count<0) $sy_empty_count = 0;

    echo "共需要执行的分页总数：".count($items).PHP_EOL;
    //更新执行补数据的同步的状态：
    $empty_status!=1 &&  updateEmptyStatus($store_id); //更新状态
    echo "all-json-list-num：".count($chapter_list)."\tis_exists_num：".$exists_count."\tall-shengyu-num：".$sy_empty_count.PHP_EOL;

    echo "store_id = ".$store_id." | pro_book_id = ".$info['pro_book_id'].PHP_EOL;
    echo "over\r\n";
}else{
    echo "no this chapter\r\n";
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";

/**
* @note 更新为空的状态同步统计
*
* @param  $store_id int 小说ID
* @return array
*/
function updateEmptyStatus($store_id){
    global $mysql_obj , $table_novel_name;
    $store_id = intval($store_id);
    if(!$store_id)
        return false;
    $where_condition = "store_id = '". $store_id."'";
    $chapter_data['empty_status'] = 1;
    //对比新旧数据返回最新的更新
    $mysql_obj->update_data($chapter_data,$where_condition,$table_novel_name);
}


//获取成功抓取的总次数
function getSuccessTimes($data){
    if(!$data)
        return false;
    $num = 0;
    foreach($data as $value){
        if(!empty($value['content'])){
            $num++;
        }
    }
    return $num;
}

