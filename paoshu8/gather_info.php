<?php
/*
 * 同步小说基本信息的主程序
 * 主要同步的数据有以下几个流程：
 * 1、同步ims_novel_info表信息和状态 --已实现
 * 2、下载图片到本地的指定目录 --已实现
 * 3、同步章节数据暂时放到chapter表，后期采用json存储 -待完善
 * 4、同步线上mc_book数据比对--目前未实现
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
use QL\QueryList;##引入querylist的采集器

if(is_cli()){
    $store_id = $argv[1] ?? 0;
}else{
    $store_id  = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
}
if(!$store_id){
    echo '请选择要抓取的内容id';
    exit();
}
$table_novel_name =Env::get('APICONFIG.TABLE_NOVEL'); //小说基本信息表
$info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_novel_name);
$url = Env::get('APICONFIG.PAOSHU_API_URL'); //获取配置的域名信息
//删除旧数据，防止有新的进行抓取
function delete_chapter_data($store_id,$story_id,$table_name){
    if(!$store_id){
        return false;
    }
    global $mysql_obj;
    $sql = "delete from ".$table_name." where story_id = '".$story_id."'";
    $mysql_obj->query($sql,'db_master');
}

//清洗掉不需要的字段
function cleanData($items = [],$filter_key=[]){
    if(!$items ||!$filter_key) return false;
    $list = [];
    foreach($items as $key => $val){
        $info = [];
        foreach($val as $k =>&$v){
            //如果在过滤的字段里，直接切除
            if(!in_array($k , $filter_key)){
                $info[$k] = $v;
            }
        }
        $list[$key] = $info;
    }
    return $list;
}

if($info){
    $story_link = trim($info[0]['story_link']);//小说地址
    if($info[0]['is_async'] == 1){
        echo "url：---".$story_link."---当前数据已同步，请勿重复同步\r\n";
        exit();
    }
    //定义抓取规则
    $rules = array(
        'cover_logo'       =>array('#fmimg img','src'),//小说封面
        'author'    => array('#info p:eq(0)','text'),//小说作者
        'title'     =>array('#info>h1','text'),//小说标题
        'status'    =>array('meta[property=og:novel:status]','content'),//小说的状态
        'third_update_time'    =>array('#info p:eq(2)','text'), //最近的更新时间
        'nearby_chapter'    =>array('meta[property=og:novel:latest_chapter_name]','content'), //最近的文章
        // 'intro' => array('#intro','text'),//小说的简介
        'cate_name' =>array('meta[property=og:novel:category]','content'),//分类
        'intro' =>array('meta[property=og:description]','content'),
        'tag'   => array('meta[property=og:novel:category]','content'),
        'location'  =>  array('.con_top','text'),//小说的面包屑位置
        // 'link_url'    =>array('.place a:eq(2)','href'),//当前书籍的url
        // 'novel_url'   =>array('.info a:eq(2)','href'),//获取小说的跳转地址
    );

    // $redis_book_key = 'store_info:'.$store_id;
    // $redis_data  = $redis_data->get_redis($redis_book_key);
    // if(!$redis_data){
    //     //爬取相关规则下的类
         $info_data=QueryList::get($story_link)
                ->rules($rules)
                ->query()->getData();
        $store_data = $info_data->all();
    //     $redis_data->set_redis($)
    // }
    if(!empty($store_data)){
        //保存图片到本地
        NovelModel::saveImgToLocal($store_data['cover_logo']);
        //同步数据到mc_book表
        $store_data['story_link'] = $story_link;

        $story_id = trim($info[0]['story_id']); //小说的id
        //处理空字符串
        $location = str_replace("\r\n",'',$store_data['location']);
        $location =trim($location);
        $store_data['location'] = $location;

        $update_time  = str_replace('最后更新：','',$store_data['third_update_time']);
        $third_update_time = $update_time.' 00:00:00';
        $third_update_time = strtotime($third_update_time);
        $store_data['third_update_time'] = $third_update_time;

        //处理作者
        $author_data = explode('：',$store_data['author']);
        $store_data['author']  = $author_data[1] ?? '';
        $store_data['updatetime'] = time();
        $store_data['intro'] = addslashes($store_data['intro']);//转义 特殊字符
        $store_data['tag'] = str_replace('小说','',$store_data['tag']);
        //执行更新操作
        if($info[0]['createtime'] == 0){
            $store_data['createtime']  = time();
        }
        $where_data = "story_id = '".$story_id."'";
        //定义章节的目录信息
        $list_rule = array(
            'link_name'     =>array('a','text'),
            'link_url'       =>array('a','href'),
        );

        //同步小说的基础信息到mc_book
        $sync_pro_id = NovelModel::exchange_book_handle($store_data,$mysql_obj);
        $store_data['pro_book_id'] = $sync_pro_id;


        $range = '#list dd';
        $rt = QueryList::get($story_link)
                ->rules($list_rule)
                ->range($range)
                ->query()->getData();

        $item_list = $chapter_ids = $items= [];
        if(!empty($rt->all())){
            $now_time = time();
            $chapter_detal = $rt->all();
            //处理过滤章节名称里的特殊字符---按照名称进行存储，部分章节可能重名
            $chapter_detal = removeDataRepeat($chapter_detal);
            foreach($chapter_detal as $val){
                $link_url = trim($val['link_url']);
                $chapter_ret= explode('/',$link_url);
                $chapter_str=str_replace('.html','',$chapter_ret[2]);
                $chapter_id = (int) $chapter_str;
                $val['chapter_id'] = $chapter_id;//章节id
                $val['store_id'] = $info[0]['store_id']; //关联主表info里的store_id
                $val['story_id'] = $story_id;//小说的id
                $val['createtime'] = time();
                $val['novelid'] = $chapter_id;
                $items[$val['link_url']] = $val;
                $chapter_ids[$val['chapter_id']] = 1;
            }
            $sort_ids= array_keys($chapter_ids);
            //取出来章节
            $item_list = array_values($items);
        }
        array_multisort($sort_ids , SORT_ASC , $item_list);
        //清晰不需要的数据信息
        $item_list = cleanData($item_list,['chapter_id']);

        $update_id = $info[0]['store_id'];
        //删除章节关联的数据信息
        $chapter_table_name= Env::get('APICONFIG.TABLE_CHAPTER');
        //处理相关的信息
        delete_chapter_data($update_id,$story_id,$chapter_table_name);
        $update_ret = $mysql_obj->update_data($store_data,$where_data,$table_novel_name);
        $res = $mysql_obj->add_data($item_list , $chapter_table_name);
        if($res){
             //更新小说表的is_async为1，表示已经更新过了不需要重复更新
            $update_data['is_async'] = 1;
            $mysql_obj->update_data($update_data,$where_data,$table_novel_name);
        }
        echo "insert_id：".$update_id."\t当前小说：".$store_data['title']."|novelid=".$story_id." ---url：".$story_link."\t拉取成功，共更新章节目录：".count($item_list)."个\r\n";
    }
}else{
    echo "no data";
}

//处理抓取中按照章节名称返回
//将章节中的全角符号转换成英文
//过滤调一些特殊分符号
function removeDataRepeat($data){
    if(!$data) return false;
    foreach($data as $key=>$val){
        //处理连接中的特殊字符
        $link_name = replaceCnWords($val['link_name']);
        if(!empty($link_name)){
            $t[$link_name] = [
                'link_name' =>$link_name,
                'link_url'  =>$val['link_url']
            ];
        }
    }
    $t= array_values($t);
    return $t;

}
?>