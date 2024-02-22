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

use QL\QueryList;##引入querylist的采集器

$link_url =Env::get('APICONFIG.WEB_SOTRE_HOST');//域名地址
$api_host = Env::get('APICONFIG.API_HOST'); //请求的接口域名
$ql = QueryList::getInstance();
$table_chapter_name = Env::get('APICONFIG.TABLE_CHAPTER');//章节表
$is_async = 0;
$where_data = 'is_async ='.$is_async;
$sql = "select m.chapter_id,m.link_url,m.novelid from ".$table_chapter_name . " as m where  ".$where_data." and m.novelid  = 339142 limit 10";
$items = $mysql_obj->fetchAll($sql ,'db_slave');

if($items && is_array($items)){
    $ql = QueryList::getInstance();
    foreach($items as $key =>$value){
        $novelid =intval($value['novelid']);
        $c_url = trim($value['link_url']);
        if(!$novelid || !$c_url) continue;
        //获取需要抓取小说内容的配置信息
        $infoData  = getAPIUrl($novelid , $c_url);
        $api_url = $infoData['api_url'] ?? '';
        $headers = $infoData['headers'] ?? '';
        if(!$api_url || !$headers){
            continue;
        }

        $folder_data = getStoreFile($c_url);//获取处理的小说内容
        $download_path = ROOT . 'log' . DS . 'xiaoshuo' .DS . $folder_data['folder'];//下载路径
        echo "chapter_id=".$value['chapter_id']."\tnovelid=".$novelid."\t" ."url:". $c_url . "\tfolder：".$folder_data['folder']."\r\n";
        if(!is_dir($download_path)){
            createFolders($download_path);
        }
        $save_path = $folder_data['save_path'];
        $filename = $download_path . DS . $save_path;
        //获取小说里的内容信息
        $content = getApiContent($api_url , $headers);
        $update_data = ['is_async'=>1];
        $where_data = "chapter_id = '".$value['chapter_id']."'";
        $mysql_obj->update_data($update_data,$where_data,$table_chapter_name);
        if(!empty($content)){
            file_put_contents($filename, "\r\n".$content);
        }
        echo 1;die;
    }
    echo "over\r\n";
}else{
    echo "no data\r\n";
}

//获取远程抓取的内容
function getApiContent($api_url ,$headers){
    global $ql;
    $response = $ql::post($api_url,[],$headers);
    $result  =$response->getHtml();
    $result = str_replace("}</p>",'}',$result);
    $info = str_replace(array("\r\n","\r","\n"),"",$result);
    // echo $info;die;
    $data = json_decode($info);
    $store_content = $data->data->chapter->content ?? '';
    echo '<pre>';
    print_R($store_content);
    echo '</pre>';
    exit;
    if($store_content){
        //把P标签去掉，直接换成换行符
        $store_content = str_replace("<p>",'',$store_content);
        $store_content = str_replace("</p>","\n\n",$store_content);
    }
    return $store_content;
}


///获取拼装小说请求接口的url
function getAPIUrl($novelid,$url){
    if( !$novelid || !$url ){
        return false;
    }
    global $api_host,$link_url;
    $urls = explode('/' , $url);
    $store_name = $urls[1] ?? '';
    $pager_data = end($urls);
    $pager_data = str_replace('.html','',$pager_data);
    list($chapterid,$page) = explode('_',$pager_data);

    //拼装Refer和urls的参数
    $Referer = $link_url . $url;
    //替换URL的相关信息
    $api_url = $link_url .$api_host;
    $api_url = str_replace('{$novelid}',$novelid, $api_url);
    $api_url = str_replace('{$chapterid}',$chapterid , $api_url);
    $api_url = str_replace('{$page}',$page , $api_url);
    //组装header信息
    $headers  = [
        'headers' => [
            'Referer'             =>    $Referer,
            'Cache-Control'       =>    'Cache-Control',
            'X-Requested-With'    =>    'XMLHttpRequest',

        ]
    ];

    $items['api_url'] = $api_url;
    $items['headers'] = $headers;

    return $items;
}
?>