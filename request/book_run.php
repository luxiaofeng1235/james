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
$table_novel_name = 'ims_novel_info'; //小说明细表
$table_chapter_name = 'ims_chapter';//章节表
$sql = "select m.chapter_id,m.link_url,n.novelid from ".$table_novel_name.' as n left join '.$table_chapter_name.' as m on n.store_id = m.store_id where m.store_id =3 and m.chapter_id =621 limit 1';
$items = $mysql_obj->fetchAll($sql ,'db_slave');


function getAPIUrl($novelid,$url){
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

    $items['headers'] = $headers;
    $items['api_url'] = $api_url;
    return $items;
} 

if($items && is_array($items)){
    
    foreach ($items as $val){
        $url = getAPIUrl($val['novelid'],$val['link_url']);
        echo '<pre>';
        print_R($url);
        echo '</pre>';
        exit;
    }
}
echo $sql;die;
//////////////////////取小说的htm
$url = 'https://www.souduw.com/api/novel/chapter/transcode.html?novelid=327782&chapterid=19&page=1';
$headers  = [
    'headers' => [
        'Referer'             =>    'https://www.souduw.com/GuoYunTeXu_XianZhanHouZou/19_1.html',
        'Cache-Control'       =>    'Cache-Control',
        'X-Requested-With'    =>    'XMLHttpRequest',

    ]
];
$response = $ql::post($url,[],$headers);
$result  =$response->getHtml();
// $result = htmlspecialchars($result,ENT_NOQUOTES);
// echo $result;die;
$result = str_replace("}</p>",'}',$result);
$info = str_replace(array("\r\n","\r","\n"),"",$result);
// echo $info;die;
$data = json_decode($info);
$store_content = $data->data->chapter->content ?? '';
echo '<pre>';
print_R($store_content);
echo '</pre>';
exit;


// $urls = ['http://www.baidu.com'];
// $result = MultiHttp::curlGet($urls);
// echo '<pre>';
// print_R($result);
// echo '</pre>';
// exit;
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