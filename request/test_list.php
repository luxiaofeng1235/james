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
$item_rules = $urlsRules['baode']['list'];

$dict_exchange = [
    'title'     =>  'book_name',//小说书名
    'story_link'          =>  'source_url',//采集来源
    'cate_name'          =>  'class_name',//小说分类名称
    'createtime'    =>  'addtime',//添加时间
];
$range = $urlsRules['baode']['range'];
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
            $novel_list[$gkey]['title'] = addslashes($gval['title']);
            $novel_list[$gkey]['story_link'] = $link_url;
            $novel_list[$gkey]['story_id'] = $link_data[4]?? 0;
            $novel_list[$gkey]['createtime'] = time();
            $novel_list[$gkey]['source'] = Env::get('APICONFIG.PAOSHU_STR');//标记
            $where_data = "title = '".$gval['title']."'";
            // //查是否存在当前小说信息
            $info = $mysql_obj->get_data_by_condition($where_data,$novel_table_name,'store_id');
            if(!empty($info)){
                $t[]=$gval;
                unset($novel_list[$gkey]);
            }
        }
        $novel_list = array_merge(array(),$novel_list);
        $novel_list = array_slice($novel_list, 0 , 1);
        foreach($novel_list as $key => $info){
            //判断线上是否存在记录
            $sql = "select id from ".Env::get('TABLE_MC_BOOK')." where book_name='".$info['title']."' limit 1";
            $pro_ret = $mysql_obj->fetch($sql,'db_novel_pro');
            if($pro_ret){
                $pro_book_id = $pro_ret['id'];
            }else{
                //线上小说数据转换
                $pro_data= exchange_book_handle($info);
                $pro_book_id = $mysql_obj->add_data($pro_data,Env::get('TABLE_MC_BOOK'),'db_novel_pro');
            }
            $info['pro_book_id'] = $pro_book_id;
            $novel_list[$key]['pro_book_id'] = $pro_book_id;
        }
        $result = $mysql_obj->add_data($novel_list ,$novel_table_name);
        if(!$result){
            echo "complate error";
        }
        echo "最新文章同步完成=======共同步".count($novel_list)."篇小说";

}

//转换对应的字段信息
function exchange_book_handle($data){
    global $dict_exchange,$mysql_obj;
    if(!$data)
        return false;
    //先按照源数据进行判断
    $ex_key =[];
    foreach($dict_exchange as $key  => $val){
        if(!$key)
            continue;
        $ex_key[$key] = 1;
    }
    foreach($data as $key =>$val){
        if(isset($ex_key[$key])){
            $info[$dict_exchange[$key]]=trim($val);
        }
    }
    //计算当前的分类ID信息
    $info['cid'] = NovelModel::getNovelCateId($info['class_name']);
    return $info;
}
?>