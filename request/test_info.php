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
$url =Env::get('BAODE.STORE_HOST_CATE') . '1_1/';

//从配置中取
$key = Env::get('BAODE.NOVEL_STR');
$rules = $urlRules[$key]['info'];
$story_link =Env::get('BAODE.STORE_HOST_INFO') . '7465/';//小说地址

//爬取相关规则下的类
$info_data=QueryList::get($story_link)
        ->rules($rules)
        ->query()->getData();

$info = $info_data->all();

$info['cover_logo'] = Env::get('BAODE.STORE_HOST_LIST').$info['cover_logo'];
echo '<pre>';
print_R($info);
echo '</pre>';
exit;
?>