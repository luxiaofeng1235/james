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


// $html  =readFileData('./1.html');

// $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['detail_url'];
// $data = QueryList::html($html)
//         ->rules($rules)
//         ->query()
//         ->getData();

// $data = $data->all();

// echo '<pre>';
// print_R($data);
// echo '</pre>';
// die;

// echo '<pre>';
// print_R($content);
// echo '</pre>';
// exit;

//最新更新的列表
$update_list = QueryList::html($pageList)
                ->rules($update_rules)
                ->range($range_update)
                ->query()
                ->getData();
$update_list = $update_list->all();

//最新入库的列表
$ruku_list = QueryList::html($pageList)
                ->rules($ruku_rules)
                ->range($range_ruku)
                ->query()
                ->getData();
$ruku_list = $ruku_list->all();

//合并数据
$novelList = array_merge($update_list , $ruku_list) ;
$novelList = array_filter($novelList);
if(!$novelList){
    exit("暂无可用章节信息");
}



/**
* @note 重复调用请求，防止有空数据返回做特殊调用
*
* @param $content_arr array  请求的HTML数据
* @param $goods_list array 原始请求的校验数据
* @param $proxy_type 获取移动端的代理配置 4：列表的代理 2：统计移动端页面的代理 3：修补空数据的代理
* @return unnkower
*/
function callMultiRquests($contents_arr=[],$goods_list=[],$proxy_type=1){
     if(!$contents_arr || !$goods_list){
        return [];
     }
     $goods_list = array_values($goods_list);
     $errData  =  $sucData  = [];
     $patterns = '/id="list"/'; //按照正文标签来匹配，如果没有确实是有问题
     foreach($contents_arr as $key => $val){
        if(!preg_match($patterns, $val)){
            $errData[] =$goods_list[$key] ?? [];
        }else{
            $sucData[] = $val;
        }
     }
     $repeat_data = $curl_contents1 =[];
     //数据为空的情况判断
     if(!empty($errData)){
        $successNum = 0;
        $old_num = count($errData);
        $urls = array_column($errData, 'story_link'); //进来先取出来
        while(true){
            $curl_contents1 = curl_pic_multi::Curl_http($urls,$proxy_type);
            $temp_url =[];//设置中间变量
            foreach($curl_contents1 as $tkey=> $tval){
                if(empty($tval)){//为空的情况
                    echo "获取数据为空，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                 }else if(!preg_match($patterns,$tval) ){//断章处理，包含有502的未响应都会
                    echo "不全的HTML，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                  }else{
                      $repeat_data[] = $tval;
                      unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                      unset($curl_contents1[$tkey]);
                      $successNum++;
                  }
            }
            $urls = $temp_url; //起到指针的作用，每次只存失败的连接
            $urls = array_values($urls);//重置键值，方便查找
            $curl_contents1 =array_values($curl_contents1);//获取最新的数组
            if($old_num == $successNum){
                echo "数据清洗完毕等待入库\r\n";
                break;
            }
        }
     }
    $retuernList = array_merge($sucData , $repeat_data);
    return $retuernList;
}

$novelList = array_slice($novelList, 0, 1);


//保存的客户端
// $files = Env::get('SAVE_HTML_PATH').DS.'detail_1235.'.NovelModel::$file_type;
$urls = array_column($novelList,'story_link');

//获取关联的数据信息
$list = curl_pic_multi::Curl_http($urls);
$list = callMultiRquests($list,  $novelList);

if(empty($list)){
    exit("当前小说链接有问题，请重新检查\r\n");
}


// $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['detail_url'];
// $data = QueryList::html($html)
//         ->rules($rules)
//         ->query()
//         ->getData();
foreach($list as $key =>$val){
    echo '<pre>';
    print_R($val);
    echo '</pre>';
    exit;
}


// foreach($novelList  as $val){
//     if(!$val) continue;
//     //存储保存的路径信息
//     $file_path = Env::get('SAVE_HTML_PATH').DS.'detail_'.$val['story_id'].'.'.NovelModel::$file_type;
//     if(!file_exists($file_path)){
//           //请求接口信息
//           $contents = curl_pic_multi::Curl_http([$val['story_link']]);
//           if(!empty($contents)){
//             //写入文件信息
//                 echo '<pre>';
//                 print_R($contents);
//                 echo '</pre>';
//                 exit;
//                 writeFileCombine($file_path , $contents);
//                 echo "url = {$val['story_link']} path = $file_path \r\n";
//           }
//     }
// }

echo "====================插入/更新同步网站数据,共" . count($novelList) ."本小说\r\n";

echo '<pre>';
print_R($novelList);
echo '</pre>';
exit;

?>