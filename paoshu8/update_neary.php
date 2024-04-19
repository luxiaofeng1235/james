<?php
/*
 * 同步跑书吧最新入库和最新更新的章节信息

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


//最新入库的列表
$ruku_list = QueryList::html($pageList)
                ->rules($ruku_rules)
                ->range($range_ruku)
                ->query()
                ->getData();
echo '<pre>';
print_R($ruku_list);
echo '</pre>';
exit;




?>