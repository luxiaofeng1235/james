<?php
/**
*名称:syc_novel_list
*作用:同步小说列表
*说明:
*版权:
*作者:Red QQ:513072539
*时间:2024/2/28
**/

require_once(dirname(__DIR__).'/library/init.inc.php');

///从本地取出来数据
$table_pro_name = 'mc_book';
$table_local_name ='ims_novel_info';
$local_list = $mysql_obj->fetchAll('select *,replace(cate_name,\'大全列表\',\'\') as cate_new_name from '.$table_local_name.' limit 10','db_slave');
$dict_exchange = [
    'title'     =>  'book_name',//小说书名
    'cover_logo'       =>  'pic',//小说封面
    'author'          =>      'author',//作者
    'tag'       =>  'tags',//标签
    'intro'          =>  'desc',//简介
    'nearby_chapter'             =>   'last_chapter_title',//最新章节
    'story_link'          =>  'source_url',//采集来源
    'cate_new_name'          =>  'class_name',//小说分类名称
    'createtime'    =>  'addtime',//添加时间
];

if($local_list){
    $data=[];
    foreach($local_list as $key =>$val){
        $data[] = exchange_book_handle($val);
    }
    $book_handle = [];
    if(!empty($data)){
        $i = 0;
        foreach($data as &$gval){
            $i++;
            $book_handle = checkBookHandle($gval['book_name']);
            $story_id = $gval['story_id'];
            if(!empty($book_handle) && is_array($book_handle)){
                 $pro_id = $book_handle['id'];
                 $where_data="story_id ='".$story_id."' limit 1";
                 $upddateData= ['pro_book_id' =>$pro_id];
                 $res = $mysql_obj->update_data($upddateData,$where_data ,$table_local_name);
            }else{
                //如果没有同步过就直接同步过去
                unset($gval['story_id']);
                $add_push_data = getKeyClean($gval);
                $pro_id = $mysql_obj_pro->add_data($add_push_data,$table_pro_name);

                ////更新当前表的id
                $where_data="story_id ='".$story_id."' limit 1";
                $upddateData= ['pro_book_id' =>$pro_id];
                $res = $mysql_obj->update_data($upddateData,$where_data ,$table_local_name);
            }
            echo "index——".$i."\tstory_id".$story_id."\tpro_id：".$pro_id."\ttitle：".$gval['book_name']."\turl：".$gval['source_url']."\r\n";
        }
        echo "over\r\n";
    }
}

//处理数组中的key进行添加
function getKeyClean($key_data){
    if(!$key_data) return false;
    $new_data =[];
    foreach($key_data as $key =>$val){
        $tkey ="`$key`";
        $new_data[$tkey] = $val;
    }
    return $new_data;
}

//监测是否存在线上的同步数据
function checkBookHandle($book_name=''){
    global $table_pro_name,$mysql_obj_pro;
    $book_name = trim($book_name);
    if(!$book_name)
        return false;
    $sql = "select id from ".$table_pro_name." where book_name = '".$book_name."'";
    $info = $mysql_obj_pro->fetch($sql,'db_slave');
    return $info;
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
    $story_id =trim($data['story_id']);
    $sotre_count =$mysql_obj->fetch("select count(1) as count_num from ims_chapter where story_id='".$story_id."' limit 1",'db_slave');
    $info['text_num'] = 2000;
    $info['chapter_num'] = $sotre_count['count_num'] ??0;
    //处理连载状态
    if( $data['status'] == '连载中'){
        $serialize = 1;//连载
    }else if( $data['status'] == '已经完本'){
        $serialize = 2;//完结
    }else {
        $serialize =3;//太监
    }
    $info['desc'] = preg_replace("/\r\n/", '', $info['desc']);
    $info['serialize'] = $serialize;
    $info['story_id'] = $story_id;
    $info['chapter_title'] = $info['last_chapter_title'];//冗余下最新章节
    $info['addtime'] = (int) $info['addtime'];
    return $info;
}
?>