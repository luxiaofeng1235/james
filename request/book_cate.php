<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_cate.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:获取最新的文章的标签
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;##引入querylist的采集器


$url = Env::get('APICONFIG.WEB_SOTRE_HOST'); //需要爬取的url章节信息
$table_name= 'ims_category';//需要插入的表名称
$data = webRequest($url ,'GET',[],[]);//获取当前的匹配的内容信息
if($data){
    $range = '.lastupdate li';
    $rules = array(
        'cate_name' =>  array('.lx a','text'), //分类名称
        'cate_link_url' =>   array('.lx a','href'), //分类链接地址
        'article_url'   =>array('.sm a','href') //文章链接地址
    );
    $rt = QueryList::get($url)
        ->rules($rules)
        ->range($range)
        ->query()->getData();
    if($rt->all()){
        $data = $rt->all();
        foreach($data as  $key =>$val){
            if(!$val['cate_name']) $data[$key]['cate_name'] = '====';
             //匹配当前是否存在已存在的连接
            $where_data = "article_url = '".$val['article_url']."'";
            $info = $mysql_obj->get_data_by_condition($where_data,$table_name,'id');
            $data[$key]['createtime'] = time();
            if($info){
                unset($data[$key]);
            }

        }
        $cate_data = array_merge(array(),$data);
        $result = $mysql_obj->add_data($cate_data ,$table_name);
        if(!$result){
            echo "complate error";
        }
        echo "最新文章同步完成=======共同步".count($cate_data)."篇小说";
    }

}else{
    echo 'no data now!@!';
}
?>