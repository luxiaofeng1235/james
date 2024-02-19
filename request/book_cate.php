<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_cate.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');


$url = 'https://www.souduw.com/'; //需要爬取的url章节信息
$data = webRequest($url ,'GET',[],[]);//获取当前的匹配的内容信息
if($data){
    $table_name= 'ims_category';//需要插入的表名称
    //<div class="lastupdate"> 最新更新
    preg_match("/<div class=\"lastupdate\".*?>.*?<\/div>/ism",$data,$matchesRes);
    if(isset($matchesRes[0]) && !empty($matchesRes[0])){
        $nearby_item = $matchesRes[0] ?? [];
        //获取分类的基础信息
        preg_match_all("/<span class=\"lx\".*?>.*?<\/span>/ism",$nearby_item,$matchesRes_cate);
        if(isset($matchesRes_cate[0]) && $matchesRes_cate[0]){
                //匹配a连接
                //--- 导出站外链接
                $content_info = $matchesRes_cate[0] ?? [];
                $pat = '/href=[\"|\'](.*?)[\"|\']/i';
                //<span class="sm">
                preg_match_all("/<span class=\"sm\".*?>.*?<\/span>/ism" , $nearby_item,$art_list);
                $book_all = [];
                if(isset($art_list[0]) && $art_list[0]){
                    foreach($art_list[0] as $value){
                        preg_match($pat,$value,$art_info);
                        $book_all[]['article_url'] = $art_info[1] ?? '';
                    }
                }
                // echo '<pre>';
                // print_R($book_all);
                // echo '</pre>';
                // exit;
                //摘取连接里的数据信息
                $items = [];
                # $en_pa ='/[^\u4e00-\u9fa5]/';
                if(is_array($content_info)){
                    $en_preg = "/[\x7f-\xff]+/";//匹配中文
                    $now_time = time();
                    foreach($content_info as $response){
                        preg_match($pat, $response, $link_data);//获取当前的链接
                        preg_match($en_preg , $response,$title_data);//正则匹配汉字

                        //
                        // //防止重复插入
                        $items[] = [
                            'cate_name'     =>  $title_data[0] ?? '',
                            'link_url'  =>  $link_data[1] ?? '',
                            'createtime'    => $now_time,
                        ];
                    }
                }
                if($book_all){
                    $cate_data  = [];
                    foreach($book_all as $key =>$val){
                        $baseinfo = $items[$key]?? [];
                        $detail = array_merge($val , $baseinfo);
                        //匹配当前是否存在已存在的连接
                        $where_data = "article_url = '".$detail['article_url']."'";
                        $info = $mysql_obj->get_data_by_condition($where_data,$table_name,'id');
                        if(!$info){
                            $cate_data[] =$detail;
                        }
                    }
                    unset($items);
                    unset($book_all);
                    if($cate_data){
                        $result = $mysql_obj->add_data($cate_data ,$table_name);
                        if(!$result){
                            echo "complate error";
                        }
                         echo "最新文章同步完成";
                    }else{
                        echo "have complate";
                    }
                }
        }else{
            echo 'no category data !!!';
        }

    }
}else{
    echo 'no data now!@!';
}
?>