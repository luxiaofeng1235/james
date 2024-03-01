<?php
//批量采集小说信息先到本地
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;
$list = $mysql_obj->fetchAll("select * from ims_link_url limit 0,200",'db_slave');
$urls = array_column($list,'story_link');
$items = MultiHttp::curlGet($urls,null,true);

$html_data = [];
$rules = array(
    'text' => array('meta[og:novel:read_url]','content'),//采集class为two下面的超链接的链接
);
foreach($items as $key =>$val){
    $data = QueryList::html($val)
            ->rules($rules)->query()
            ->getData();
    $json_data = $data->all();
    $html_data[$json_data['text']] =$val;
}

$save_dir=  'E:\html_data';
foreach($html_data as $key =>$val){
    $prefix = str_replace('/','',$key);
    $filename = $save_dir.DS .'info_'.$prefix.'.html';
    file_put_contents($filename,$val);
}
// echo "over\r\n";

?>