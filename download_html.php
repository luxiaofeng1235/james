<?php
//批量采集小说信息先到本地
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;
set_time_limit(0);

$exec_start_time =microtime(true);

$all_num = 10000;//先执行一万本，看下内存
$limit = 5000;
$t = $all_num/$limit;


$curl_num = 200;
for ($i=0; $i <$t ; $i++) {
    $str = $i*$limit.','.$limit;
    $list = $mysql_obj->fetchAll("select * from ims_link_url limit ".$str,'db_slave');
    $urls = array_column($list,'story_link');
    $item = array_chunk($urls,$curl_num);
    foreach($item as $key =>$url){
        curlGetHtml($url);
        die;

    }
}


$exec_end_time =microtime(true); //执行结束时间
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".sprintf('%.2f',($executionTime/60))." minutes \r\n";
echo "over\r\n";

//采集内容到本地
function curlGetHtml($urls){
    if(!$urls) return false;
    $rules = array(
        'text' => array('meta[property=og:novel:read_url]','content'),//采集class为two下面的超链接的链接
    );
    $items = MultiHttp::curlGet($urls,null,true);
    $html_data = [];
    foreach($items as $key =>$val){
            $data = QueryList::html($val)
                ->rules($rules)->query()
                ->getData();
        // $prefix = str_replace('/','',$key);
        $json_data = $data->all();
        $html_data[$json_data['text']] =$val;
    }
    $save_dir=  'E:\html_data';
    if(!empty($html_data)){
        foreach($html_data as $k =>$v){
             $index = str_replace('/','',$k);
             $file_name = $save_dir . DS . 'detail_'.$index.'.html';
             file_put_contents($file_name,$v);
        }
    }
}




?>