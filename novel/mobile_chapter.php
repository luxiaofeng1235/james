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

echo "\r\n";
echo "*********************************************************************\r\n";
//校验代理IP是否过期
if(!NovelModel::checkMobileKey()){
    exit("代理IP已过期，key =".Env::get('ZHIMA_REDIS_MOBILE_KEY')." 请重新拉取最新的ip\r\n");
}
$table_novel_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息


$exec_start_time = microtime(true);

$limit= 1; //轮训的补偿设置
$order_by =' order by store_id asc';
$redis_key ='chapter_list_count'; //保存列表的最大值

$store_id = isset($argv[1])  ? intval($argv[1]) : 0;
if(!$store_id){
    exit("请输入要执行的store_id");
}

$where_data = 'is_async =1 and store_id = '.$store_id; //固定条件
// $id = $redis_data->get_redis($redis_key);
// if($id){
//     echo "当前的增量最大ID：".$id.PHP_EOL;
//     $where_data .=" store_id > ".$id;
// }else{
//     $where_data .=" store_id>3100";
// }

$sql = "select store_id ,title,author,count_status,pro_book_id,story_link from {$table_novel_name} where ".$where_data;
// $sql .=" order by store_id asc";
$sql .= " limit ".$limit;
echo "sql = $sql \r\n";
$items = $mysql_obj->fetch($sql , 'db_slave');
if(!empty($items)){
    $t_num = 0;
    extract($items);
    //同步生成统计的总数
    $ret = ayncCountItem($items,$redis_data);
    echo "【生成移动端章节统计】 store_id : {$store_id}  pro_book_id：{$pro_book_id} title：{$title}  author：{$author}  url： {$story_link} results：{$ret}  \r\n";
}else{
    echo "no data \r\n";
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
// echo "原始数据长度:".count($items)."\r\n";
echo "finish_time：".date('Y-m-d H:i:s') .PHP_EOL;
echo "script execution time: ".sprintf('%.2f',($executionTime/60)) ." minutes \r\n";
echo "over\r\n";

echo "*********************************************************************\r\n";
echo "\r\n";
echo "\r\n";
//设置缓存方便下次存取
// $ids = array_column($items,'store_id');
// $max_store_id = max($ids);
// $redis_data->set_redis($redis_key,$max_store_id);//设置增量ID下一次轮训的次数

// echo "下次轮训的最大id起止位置 store_id：".$max_store_id.PHP_EOL;




/**
* @note 同步统计的章节总数
*
* @param  $info array 当前的列表信息
* @param $redis_data object Redis连接句柄
* @return array|string
*/

function ayncCountItem($info,$redis_data){
    if(!$info){
        return 'no record'.PHP_EOL;
    }
    extract($info);
    // $redis_store_key ='count_store_'.$info['store_id'];
    // $check_status = $redis_data->get_redis($redis_store_key);
    if($count_status){
        $count_status!=1 &&  updateCountStatus($store_id);
        return '已经处理过章节了，无需重复执行' .PHP_EOL;
    }
    //判断是否有关联的书本ID
    if(!$pro_book_id){
        $count_status!=1 &&  updateCountStatus($store_id);
        return '并无关联的小说ID'.PHP_EOL;
    }

    $md5_str= NovelModel::getAuthorFoleder($title,$author);
    $txt_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;
    if(!file_exists($json_path)){
        $count_status!=1 &&   updateCountStatus($store_id);
        return '暂无json文件，不需要去处理'.PHP_EOL;
    }
    @$list = readFileData($json_path);
    if(!$list){
        $count_status!=1 &&   updateCountStatus($store_id);
        return "当前读取章节目录为空";
    }
    //获取当前的章节名称
    $chapter_list = json_decode($list,true);
    if(!$chapter_list){
        $count_status!=1 &&   updateCountStatus($store_id);
        return '无此章节记录';
    }
    if(!$chapter_list){
        $count_status!=1 &&  updateCountStatus($store_id);
        return "去除广告后就没有章节了 \r\n";
    }

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
        $count_status!=1 &&   updateCountStatus($store_id);
        return "当前没有需要处理的章节信息\r\n";
    }

    echo "------------------------------------------待处理的统计章节总数：".count($dataList).PHP_EOL;

    //转换移动端的请求数据
    $dataList = NovelModel::exchange_urls($dataList, $store_id,'count');
    //处理返回的数据
    $goods_list = dealMobileData($dataList);
    $len_num = Env::get('LIMIT_EMPTY_SIZE'); //默认配置200个URL
    $tlist = array_chunk($goods_list , $len_num); //每次200个请求去处理
    //处理抓取的对象信息
    $buidItem = [];
    foreach($tlist as $k =>$v){
        //获取数据
        $curl_contents = getCurlContents($v);
        if(!$curl_contents) $curl_contents = [];
        //合并结果并缓存到数据中去
        $buidItem = array_merge($buidItem,$curl_contents);
        // sleep(1);
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
    // $aa = $redis_data->set_redis($redis_store_key, 1);//标记已经处理过了
    echo "store_id complete：".$store_id . PHP_EOL;
    // if($aa){
    //     echo 'redis success ----11111111111111111' . PHP_EOL;
    // }else{
    //     echo 'redis success ----2222222222222222'.PHP_EOL;
    // }
    echo "save_json_file.....：{$save_path} \r\n";
    $count_status!=1 &&  updateCountStatus($store_id); //更新统计章节的状态
    return  "complete over\r\n";

}

/**
* @note 更新统计章节的状态
*
* @param  $info array 关联章节信息
* @return array
*/
function updateCountStatus($store_id){
    global $mysql_obj , $table_novel_name;
    $store_id = intval($store_id);
    if(!$store_id)
        return false;
    $where_condition = "store_id = '". $store_id."'";
    $chapter_data['count_status'] = 1;
    //对比新旧数据返回最新的更新
    $mysql_obj->update_data($chapter_data,$where_condition,$table_novel_name);
}




/**
* @note 获取小说内容
*
* @param  $goods_list array 章节信息
* @return array
*/

function getCurlContents($goods_list =[]){
    if(!$goods_list)
        return false;
     //抓取获取相关的内容信息
    $curl_contents = [];
    $o_num = count($goods_list);
    if(!$o_num){
        return "暂无需要去抓取\r\n";
    }
    $urls = array_column($goods_list, 'mobile_url');
    //请求curl操作
    $curl_contents = guzzleHttp::multi_req($urls ,'count');
    // $curl_contents = curl_pic_multi::Curl_http($urls,2);
    $contents_arr = $curl_contents;

    if(!$contents_arr) return [];

    //获取出来成功和失败的数据
    $returnList=  NovelModel::getErrSucData($contents_arr,$goods_list);
    $sucData = $returnList['sucData'] ?? []; //成功的数据
    $errData = $returnList['errData'] ?? []; //失败的数据
    $repeat_data = $curl_contents1 =[];
    if(!empty($errData)){
        $i = 0;
        while(true){
            $old_num = count($errData);
            $urls = array_column($errData, 'mobile_url');
            //重新请求新的计算信息
            $curl_contents1 = curl_pic_multi::Curl_http($urls);
            $curl_contents1 = array_filter($curl_contents1);
            foreach($curl_contents1 as $tkey=> $tval){
                //防止有空数据跳不出去,如果非请求失败确实是空，给一个默认值
                if(!strstr($tval, '请求失败')  && empty($tval)){
                    $tval ='此章节作者很懒，什么也没写';
                }
                //strstr($val,'您当前访问的页面存在安全风险') || strstr($val,'请求失败')
                //只有不是这个的才跳出去
                if(
                !empty($tval)
                && (!strstr($tval,'您当前访问的页面存在安全风险') || !strstr($tval,'请求失败'))
             ){
                    $repeat_data[] = $tval;
                    unset($curl_contents1[$tkey]); //已经存在就踢出去，下次就不用重复计算了
                    $i++;
                }
            }
            $curl_contents1 =array_values($curl_contents1);//获取最新的数组
            //如果已经满足所有都取出来就跳出来
            if($old_num == $i){
                break;
            }
        }
    }
    //合并最终的需要处理的数据
    $finalData = array_merge($sucData , $repeat_data);
    global $urlRules;
    $count_arr = [];
    //获取移动端的关联数据信息
    $rules =$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
    foreach($finalData as $gkey => $gval){
        $data = QueryList::html($gval)->rules($rules)->query()->getData();
        $html = $data->all();
        $store_content = $html['content'] ?? '';
        $meta_data = $html['meta_data']??''; //meta表亲爱
        $first_line = $html['first_line'] ?? '';//第一行的内容
        //获取当前章节的页码数
        $html_path =NovelModel::getChapterPages($meta_data,$first_line);
        // echo $gkey.'--'.$html_path.PHP_EOL;
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
    return $lists;
}
?>