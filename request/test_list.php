<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_detail.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:采集小说的列表信息
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
use QL\QueryList;##引入querylist的采集器
$url = 'https://www.baode.cc/class/1_1/';

$item_rules = [
    'category'       => ['.s1','text'],
    'title'     =>  ['.s2 a','text'],
    'story_link'     =>  ['.s2 a','href'],
];
$range = '#newscontent li';
$itemList = QueryList::get($url)
                ->rules($item_rules)
                ->range($range)
                ->query()
                ->getData();
if($itemList->all()){
    $novel_list = $itemList->all();
        foreach($novel_list as $gkey =>$gval){
            $link_url = str_replace('//','https://',$gval['story_link']);
            $link_data =  explode('/',$link_url);
            $novel_list[$gkey]['story_link'] = $link_url;
            $novel_list[$gkey]['story_id'] = $link_data[4]?? 0;
            $novel_list[$gkey]['createtime'] = time();
            $novel_list[$gkey]['source'] = Env::get('APICONFIG.PAOSHU_STR');//标记
            $where_data = "title = '".$gval['title']."'";
            // //查是否存在当前小说信息
            $info = $mysql_obj->get_data_by_condition($where_data,$novel_table_name,'store_id');
            if(!empty($info)){
                unset($nover_list[$gkey]);
            }
        }
        $novel_list = array_merge(array(),$novel_list);
        echo "最新文章同步完成=======共同步".count($novel_list)."篇小说";

}
?>