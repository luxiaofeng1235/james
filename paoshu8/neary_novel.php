<?php
/*
 * 拉取最近的文章列表的章节类目信息
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;

$range = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_home'];
$item_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['list_home'];
$itemList = QueryList::get('http://www.paoshu8.info/')
                ->rules($item_rules)
                ->range($range)
                ->query()
                ->getData();
if($itemList->all()){
    foreach($itemList->all() as $key =>$val){
        if(!$val['story_link'])
            continue;
        $story_id = str_replace('/','',$val['story_link']);
        //如果判断无数据插入数据走入库的流程，如果有数据则需要更具章节
        $sql = "select store_id from ".Env::get('APICONFIG.TABLE_NOVEL')." where story_id='".$story_id."'";
        $info = $mysql_obj->fetch($sql,'db_slave');
        if(!$info){
            $insert_data[]=array_merge(['story_id'=>$story_id],$val);
        }else{
            //更新的可能需要麻烦点，要重新获取最新的json数据文件，截取出来最新的抽离出来从最新的去获取更新
            //还需要和老的做校验对比，跑数据
            //粗暴一点的方法：先打开对应的URL地址信息，取更新对应的章节数据信息，拿最新的和上次的旧的json数据做比对来更新
            $old_data[] =$val;
        }
    }
}
echo '<pre>';
print_R($old_data);
echo '</pre>';
exit;

?>