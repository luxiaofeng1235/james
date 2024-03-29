<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_run.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:同步小说内容
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
// set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
// ini_set('max_execution_time ','0');//永远不过期

use QL\QueryList;##引入querylist的采集器

$link_url =Env::get('APICONFIG.WEB_SOTRE_HOST');//域名地址
$api_host = Env::get('APICONFIG.API_HOST'); //请求的接口域名
$ql = QueryList::getInstance();
$table_chapter_name = Env::get('APICONFIG.TABLE_CHAPTER');//章节表
$is_async = 0;
$where_data = 'is_async ='.$is_async;
$str = isset($argv[1]) ? array($argv[1]) : '0_859';
$ids =[];
foreach($str as $key =>$val){
    $ids[] = "'".$val."'";
}

$where_data .=" and story_id in (".join(',' , $ids).")";
$sql = "select chapter_id,link_url,story_id,link_name from ".$table_chapter_name . "  where  ".$where_data;
$items = $mysql_obj->fetchAll($sql ,'db_slave');
if($items && is_array($items)){
    foreach($items as $key =>$val){
        $data[$val['link_url']] = $val;
    }
    //处理并发获取到的数据信息
    $handle_data= getContenetNew($data);

    foreach($handle_data as $key =>$value){
        $story_id = trim($value['story_id']);
        $c_url = trim($value['link_url']);
        if(!$story_id || !$c_url) continue;
        //获取需要抓取小说内容的配置信息
        $link_url = Env::get('APICONFIG.PAOSHU_HOST'). $value['link_url'];
        $folder_data = getStoreFile($c_url,$value['link_name']);//获取处理的小说内容
        $download_path = ROOT . 'log' . DS . 'paoshu8' .DS . $folder_data['folder'];//下载路径
        echo $download_path;die;
        if(!is_dir($download_path)){
            createFolders($download_path);
        }
        $save_path = $folder_data['save_path'];
        $filename = $download_path . DS . $save_path;
        echo "chapter_id=".$value['chapter_id']."\story_id=".$story_id."\t" ."url:". $c_url . "\tfolder：".$folder_data['folder']."\t当前章节：".$value['link_name']."\t目录结构：".$filename."\r\n";

        //获取小说里的内容信息
        $content = $value['content'] ?? '';
        $update_data = ['is_async'=>1];
        $where_data = "chapter_id = '".$value['chapter_id']."' limit 1";
        $mysql_obj->update_data($update_data,$where_data,$table_chapter_name);
        file_put_contents($filename, "\r\n".$content);
    }
    echo "over\r\n";
}else{
    echo "no data\r\n";
}

//获取远程抓取的内容
function getApiContent($api_url ){
    global $ql;
    $res = MultiHttp::curlGet([$api_url],null,false);
    $rules = [
        'title'    =>['.bookname h1','text'],
        'content'    =>['#content','html']
    ];
    foreach($res as $value){
        $data = QueryList::html($value)->rules($rules)->query()->getData();
        $html = $data->all();
        $store_content = $html['content'] ?? '';
        if($store_content){
            $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
            //把P标签去掉，直接换成换行符
            $store_content = str_replace("<p>",'',$store_content);
            $store_content = str_replace("</p>","\n\n",$store_content);
        }
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