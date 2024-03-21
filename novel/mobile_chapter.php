<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月21日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :mobile_chapter.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:轮训计算移动端的生成的每个章节的对应信息
// ///////////////////////////////////////////////////
ini_set('memory_limit','9000M');
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;

//校验代理IP是否过期
if(!NovelModel::checkMobileKey()){
    exit("代理IP已过期，key =".Env::get('ZHIMA_REDIS_MOBILE_KEY')." 请重新拉取最新的ip\r\n");
}


$exec_start_time = microtime(true);

$where_data = 'is_async =1 and '; //固定条件
$limit= 30;
$order_by =' order by store_id asc';
$redis_key ='chapter_list_count'; //保存列表的最大值
$id = $redis_data->get_redis($redis_key);
if($id){
    echo "当前的增量最大ID：".$id.PHP_EOL;
    $where_data .=" store_id > ".$id;
}else{
    $where_data .=" store_id>3100";
}

$sql = "select store_id ,title,author from ims_novel_info where ".$where_data;
$sql .=" order by store_id asc";
$sql .= " limit ".$limit;
echo "sql = $sql \r\n";
$items = $mysql_obj->fetchAll($sql , 'db_slave');

if(!empty($items)){
    $t_num = 0;
    foreach($items as $key =>$info){
        $t_num++;
        extract($info);
        $t= ayncCountItem($info,$redis_data); //同步生成统计的总数
        echo "num:{$t_num} 【生成移动端章节统计】 store_id : {$store_id} complete title：{$title}  author:{$author} results：{$t}  \r\n";
        // sleep(3);
    }
}
//设置缓存方便下次存取
$ids = array_column($items,'store_id');
$max_store_id = max($ids);
$redis_data->set_redis($redis_key,$max_store_id);//设置增量ID下一次轮训的次数

echo "下次轮训的最大id起止位置 pro_book_id：".$max_store_id.PHP_EOL;
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "原始数据长度:".count($items)."\r\n";
echo "finish_time：".date('Y-m-d H:i:s') .PHP_EOL;
echo "script execution time: ".sprintf('%.2f',($executionTime/60)) ." minutes \r\n";
echo "over\r\n";



/**
* @note 同步统计的章节总数
*
* @param  $info array 当前的列表信息
* @return  array
*/

function ayncCountItem($info,$redis_data){
    if(!$info){
        return 'no record'.PHP_EOL;
    }
    $redis_store_key ='count_store_'.$info['store_id'];
    $check_status = $redis_data->get_redis($redis_store_key);
    if($check_status){
        return '已经处理过了，无需重复执行' .PHP_EOL;
    }
    extract($info);
    $md5_str= NovelModel::getAuthorFoleder($title,$author);
    $txt_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;
    if(!file_exists($json_path)){
        return '暂无json文件，不需要去处理'.PHP_EOL;
    }
    @$list = file_get_contents($json_path);
    if(!$list){
        return "当前读取章节目录为空";
    }
    //获取当前的章节名称
    $chapter_list = json_decode($list,true);
    if(!$chapter_list){
        return '无此章节记录';
    }

    //构造函数处理广告
    $removeAdInfo = function($arr){
        foreach($arr as &$val){
            $val['link_name'] = $val['chapter_name'];
        }
        //移除广告章节
        $list = NovelModel::removeAdInfo($arr);
        return $list;
    };
    //处理广告并移除关联章节
    $chapter_list = $removeAdInfo($chapter_list);



    $dataList = [];
    //只计算没有目录的章节
    foreach($chapter_list as &$val){
        $filename =$txt_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
        $content = readFileData($filename);
        if(!$content ||$content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  || !file_exists($filename)){
            $val['link_url'] = $val['chapter_link'];
            $dataList[] =   $val;
        }
    }


    if(!$dataList){
         return "当前无可写的章节信息\r\n";
    }


    //转换移动端的请求数据

    $dataList = NovelModel::exchange_urls($dataList, 1);
    //处理返回的数据
    $goods_list = dealMobileData($dataList);
    // $len_num = Env::get('LIMIT_SIZE');
    $len_num = 300;
    $tlist = array_chunk($goods_list , $len_num); //每次200个请求去处理
    //处理抓取的对象信息
    $buidItem = [];
    foreach($tlist as $k =>$v){
        $curl_contents = getContents($v);
        if(!$curl_contents) $curl_contents = [];
        //合并结果并缓存到数据中去
        $buidItem = array_merge($buidItem,$curl_contents);
        sleep(1);
    }

    //保存的路径信息
    $save_path = Env::get('SAVE_MOBILE_NUM_PATH').DS.$store_id.'.'.NovelModel::$json_file_type;
    if(!is_dir(dirname($save_path))){
        createFolders(dirname($save_path));
    }
    //格式化json
    $json_data = json_encode($buidItem ,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    //写入文件信息
    $ret = writeFileCombine($save_path , $json_data);
    $aa = $redis_data->set_redis($redis_store_key, 1);//标记已经处理过了
    echo "store_id：".$store_id . PHP_EOL;
    if($aa){
        echo '11111111111111111';
    }else{
        echo '2222222222222222';
    }
    die;
    return  "finish over\r\n";

}

/**
* @note 获取小说内容
*
* @param  $goods_list array 章节信息
* @return array
*/

function getContents($goods_list =[]){
    if(!$goods_list)
        return false;
     //抓取获取相关的内容信息
    $curl_contents = [];
    $o_num = count($goods_list);
    if(!$o_num){
        return "暂无需要去抓取\r\n";
    }
    $i = 0;
    $urls = array_column($goods_list, 'mobile_url');
    @$curl_contents = guzzleHttp::multi_req($urls);
    // echo '<pre>';
    // print_R($curl_contents);
    // echo '</pre>';
    // exit;
    // $data = curl_pic_multi::Curl_http($urls,2);
    // echo '<pre>';
    // print_R($data);
    // echo '</pre>';
    // exit;
    //$res = guzzleHttp::multi_req($url);
    $contents_arr = $curl_contents;
    if(!$contents_arr) return [];

    // $contents_arr= [];
    // while(true){
    //     $urls = array_column($goods_list, 'mobile_url');
    //     $curl_contents = curl_pic_multi::Curl_http($urls);
    //     $curl_contents = array_filter($curl_contents);
    //     foreach($curl_contents as $tval){
    //         //防止有空数据跳不出去
    //         if(!strstr($tval, '503 Service')  && empty($tval)){
    //             $tval ='此章节作者很懒，什么也没写';
    //         }
    //         if(!empty($tval)){
    //             $contents_arr[] = $tval;
    //             $i++;
    //         }
    //     }
    //     //如果已经满足所有都取出来就跳出来
    //     if($o_num == $i){
    //         break;
    //     }
    // }
    global $urlRules;
    $count_arr = [];
    //获取移动端的关联数据信息
    $rules =$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
    foreach($contents_arr as $gval){
        $data = QueryList::html($gval)->rules($rules)->query()->getData();
        $html = $data->all();
        $store_content = $html['content'] ?? '';
        $meta_data = $html['meta_data']??'';
        $html_path = getCurrentNum($meta_data,$store_content);
        if($html_path){
            $count_arr =  array_merge($count_arr ,$html_path);
        }
    }
    //转换数组对象按照当前的结构切割
    $goods_list_new = double_array_exchange_by_field($goods_list ,'mobile_path');
    $count_list =[];
    //计算相关的数量计算
    foreach($count_arr as $gk =>$gv){
         if(isset($goods_list_new[$gk])){
            $count_list[]=[
                'path'  =>  $goods_list_new[$gk]['path'] ??'',
                'pages'   =>    $gv,
            ];
         }
    }
    return $count_list;
}



/**
* @note 获取当前页面的总数量z
*
* @param $meta_data array meta信息
* @param $html string 当前抓取到的url
* @return
*/
function getCurrentNum($meta_data , $html){
    if(!$meta_data ||!$html)
        return false;
    $path =substr($meta_data, 0 , -1);

    $page_num = 0;
    $con_str = preg_split('/<\/p>/',$html); //按照P标签切割函数
    $pages =str_replace("\r\n",'', $con_str[0]); //替换里面的空行
    $content = filterHtml($pages);//过滤特殊字符
    $t = explode('/',$content);// (第1/3页) 替换
    if(!$t)
        return [];
    preg_match('/\d/',$t[1],$allPage);
    $all_num = intval($allPage[0]); //总的页码数量，需要判断是否有1页以上
    $path_info =  $path . '-1';
    $info[$path_info] = $all_num; // 按个格式进行拼装
    return $info;
}


/**
* @note 处理移动端的数据请求
*
* @param  $data array 待处理的数据信息
* @return  array
*/
function dealMobileData($data=[]){
    if(!$data){
        return false;
    }
    //需要显示返回的数据信息·
    $allow_filed=[
        'chapter_name',
        'chapter_link',
        'path',
        'mobile_url',
        'mobile_path'
    ];
    $lists = [];
    //配置可用的代理配置IP
    foreach($data as $key =>$val){
        $mobiePath = parse_url($val['mobile_url']);
        $val['mobile_path'] = $mobiePath['path'] ?? '';
        $info = [];
        foreach($val as $k =>$v){
             if(in_array($k , $allow_filed)){
                $info[$k] = $v;
             }
        }
        $lists[]= $info;
    }
    //转换对象处理
    // $list_arr = double_array_exchange_by_field($lists,'mobile_path');
    return $lists;
}
?>