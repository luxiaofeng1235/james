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
if(!NovelModel::checkProxyExpire()){
    exit("代理IP已过期，请重新拉取最新的ip\r\n");
}

//同步的书籍ID信息
$store_id = isset($argv[1])  ? intval($argv[1]) : 0;
if(!$store_id){
    exit("please input your store_id.... \r\n");
}
$where_data = 'is_async = 1'; //只对已经同步得来进行计算
//查询小说的基本信息
$sql = "select title,cover_logo,author,pro_book_id,store_id from ims_novel_info where store_id = ".$store_id;
$info =$mysql_obj->fetch($sql , 'db_slave');
if(!empty($info)){
    extract($info);
    //同步一下图片，直接取
    $md5_str= NovelModel::getAuthorFoleder($title,$author);
    echo "book_md5：".$md5_str."\r\n";
    $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;
    $list = readFileData($json_path);
    if(!$list){
        echo "当前读取章节目录为空\r\n";
        exit;
    }
    //解析章节列表
    $chapter_list = json_decode($list,true);
    //当前的存储的小说的目录信息
    $txt_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $dataList= [];
    if(!$chapter_list){
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
            $chapterInfo['link_url'] = $val['chapter_link'];
            $chapterInfo['link_str'] = $link;
            $chapterInfo['link_name'] = $val['chapter_name'];
            $dataList[] =   $chapterInfo;
        }else{
            $i++;
        }
    }
    if(!$dataList){
        echo "book_name：{$info['title']}  pro_book_id：{$info['pro_book_id']}  不需要轮询抓取了，章节已经全部抓取下来了\r\n";
        exit();
    }
    //统计下当前的跑出来的数据情况
    echo "json-list-num：".count($chapter_list)."\tis_have_num：".$i."\tall-empty-num：".count($dataList).PHP_EOL;
    // $tmp_size = 10; //每次定义20个步长去处理
    // $items = array_slice($dataList , 0, $tmp_size); //测试后期删掉
    // echo "共需要处理的空章节总数--步长按照{$tmp_size}来算的:".count($items).PHP_EOL;
    echo "-----------------------------\r\n";
    $curlMulti = new curl_pic_multi();
    $limit_num = Env::get('LIMIT_EMPTY_SIZE');
    $items = array_chunk($dataList, $limit_num);
    $all_count = count($dataList);
    $save_list = [];
    //轮训处理获取关联的数据信息
    do{
        $success_num= 0;
        $insert_data = [];
        foreach($items as $k =>$v){
            //抓取远端地址并进行处理
            $curl_contents= NovelModel::getHtmlData($v);
            if(!$curl_contents)
                continue;
             if($curl_contents && is_array($curl_contents)){
                foreach($curl_contents as $gval){
                    if(!empty($gval['content'])){
                        $success_num++;
                        $insert_data[]=$gval;
                    }
                }
            }
        }
        //判断如果是当前请求和连接一直就退出
        if($all_count == $success_num){
            $save_list = $insert_data;
            break;
        }
    }while(true);
    //保存对应的文章信息
    NovelModel::saveLocalContent($save_list);
    echo "store_id = ".$info['store_id']." | pro_book_id = ".$info['pro_book_id'].PHP_EOL;
    echo "over\r\n";
}else{
    echo "no this chapter\r\n";
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";

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

