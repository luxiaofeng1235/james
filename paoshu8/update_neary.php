<?php
/*
 * 同步跑书吧最新入库和最新更新的章节信息自动同步到数据库

 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
$url = Env::get('APICONFIG.PAOSHU_HOST');

##获取首页的基础信息
$pageList = NovelModel::cacheHomeList($url);
if(!$pageList){
    exit("获取页面内容为空，请稍后重试\r\n");
}

$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
$db_conn = 'db_master';
//定义采集规则：
//最新更新章节循环的class
$range_update  = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_update'];
//最新书籍入库循环的class
$range_ruku  = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_ruku'];
//最近更新的列表
$update_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['update_list'];
//最新入库的规则
$ruku_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['ruku_list'];

//最新更新的列表
$update_list = QueryList::html($pageList)
                ->rules($update_rules)
                ->range($range_update)
                ->query()
                ->getData();
$update_list = $update_list->all();

//最新入库的列表
$ruku_list = QueryList::html($pageList)
                ->rules($ruku_rules)
                ->range($range_ruku)
                ->query()
                ->getData();
$ruku_list = $ruku_list->all();

//合并数据
$novelList = array_merge($update_list , $ruku_list) ;
$novelList = array_filter($novelList);
if(!$novelList){
    exit("暂无可用章节信息");
}
$novelList = array_slice($novelList, 0, 1);

//同步小说目录详情到本地
NovelModel::saveDetailHtml($novelList);

echo "====================插入/更新同步网站数据,共" . count($novelList) ."本小说\r\n";

$insertData = [];
$source = Env::get('APICONFIG.PAOSHU_STR');//同步的来源
foreach($novelList as $key =>$val){
    $val['tag'] = $val['cate_name']??'';
    $storyInfo = NovelModel::getNovelInfoById($val['story_id'],$source);
    if(empty($storyInfo)){
        $val['title'] = trimBlankSpace($val['title']);//小说名称
        $val['author'] = $val['author'] ? trimBlankSpace($val['author']) :'未知';//小说作何
        $val['is_async'] = 0;//是否同步状态
        $val['syn_chapter_status'] = 0; //是否同步章节
        $val['source'] = $source; //来源
        $val['createtime'] = time(); //时间
        $insertData[] = $val;
    }
}
echo "实际待需要插入的小说有 ".count($insertData) . "本，会自动同步\r\n";
if($insertData){
    //同步数据
    $ret= $mysql_obj->add_data($insertData,$novel_table_name,$db_conn);
    if(!$ret){
        echo "数据库数据同步失败\r\n";
    }
    echo "同步小说列表成功 \r\n";
}else{
    echo "暂无小说需要同步\r\n";
}

echo "finish\r\n";

?>