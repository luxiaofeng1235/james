<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一
// 日 期：2024年3月14日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :set_proxy_cache.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:定时扫描redis里的代理是否存在，如果不存在就取取一个最新的可用的出来，方便业务使用
// 只判断代理IP是否在缓存中，缓存30分钟，过期就重新运行
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
$exec_start_time = microtime(true);

/**
* @note 获取并排除20分钟以下的代理
*
* @return array
*/
function getAllowProxy(){
    global $url;
    //不适用的地区的IP
    $area_prxy =Env::get('NO_USE_PROXY_AREA');
    $no_area_proxy = explode(',',$area_prxy); //按照地区切割
    $no_area_proxy = array_unique($no_area_proxy);//去重
    $new_proxy = [];
    $diff_time = 20*60; //设置代理的存活时间
    do{
        $info = webRequest($url,'GET');
        $proxy_info  =    json_decode($info , true);
        $proxy_data = $proxy_info['data'][0] ?? [];
        //重组相关的参数防止和其他有冲突
        $proxy_data  =combineProxyParam($proxy_data);
        if(!empty($proxy_data)){
            $now_time = time();
            $expire_time = $proxy_data['expire_time'] ?? '';
            //利用过期时间-当前时间计算代理的可用时间
            $t = strtotime($expire_time) - $now_time;
            if($t>=$diff_time){
                $new_proxy = $proxy_data;
                break;
            }
        }else{
            //处理过期的套餐处理
            $msg = $proxy_info['msg'] ?? '';
            echo "message = ".$msg."\r\n";
            NovelModel::killMasterProcess();//退出主程序
            exit();
        }
        sleep(1); //停止1秒
    }while(true);
    $proxy_ret   = $new_proxy; //获取新的代理
    return $proxy_ret;
}
//允许命令行接收的参数
$allowKey = [
    'zhima_proxy_new:',
    'zhima_mobile_key:',
    'zhima_mobile_empty_key:',
    'zhima_proxy_story_info:',
    'zhima_proxy_img:',
];

//从客户端的cli接收参数
$redis_cache_key = isset($argv[1])  ? trim($argv[1]) : '' ;//接受的缓存的key
if(!$redis_cache_key){
    exit("请输入缓存的key");
}else{
    if(!in_array($redis_cache_key,$allowKey)){
        exit("该缓存 ".$redis_cache_key." 无效，请输入有效的key ".var_export($allowKey,true));
    }
}

$pro_type = isset($argv[3]) ? $argv[3] : 1;
if($pro_type == 1){
    //直连IP
    $url = Env::get('ZHIMAURL');
}else if($pro_type ==2){
    //隧道IP
    $url = Env::get('ZHIMA_SUIDAO');
}
// $url = Env::get('YILIANURL'); //使用一连的IP进行访问
echo "proxy-url： {$url} \r\n";
$proxy_conf = [];
echo "cache_key：".$redis_cache_key."\r\n";
$host  =  isset($argv[2]) ? trim($argv[2]) : Env::get('APICONFIG.PAOSHU_HOST') ;
echo "检测的url：".$host."\r\n";
// $redis_data->del_redis($redis_cache_key);
$proxy = $redis_data->get_redis($redis_cache_key);


if(!$proxy){

    do{
        //获取在20分钟以上的代理
        $proxy_data = getAllowProxy();
        //检测目标网站是否可用，如果不可用就取一个可用的
        $check_data = curlProxyState($host,$proxy_data);
        if($check_data['http_code'] == 200){
            //如果检测到可用的IP，就直接退出循环
            $proxy_conf = $proxy_data;
            break;
        }
    }while(true);
    $diff_time = strtotime($proxy_data['expire_time']) - time(); //利用过期时间-当前时间为缓存的redis的时间
    $redis_data->set_redis($redis_cache_key,json_encode($proxy_conf),$diff_time);
    echo "获取到新的代理IP信息（芝麻代理）\r\n";
    echo '<pre>';
    print_R($proxy_conf);
    echo '</pre>';
    echo "\r\n";
}else{
    echo "redis里的代理暂未过期,还能用\r\n";
    $proxy_conf = json_decode($proxy , true);
    $ttl =$redis_data->ttl($redis_cache_key); //获取缓存的可用时间
    $minutes = sprintf('%.2f',($ttl/60)); //剩余的分钟数
    echo "剩余可用缓存时间：".$minutes." minutes\r\n";
    echo '<pre>';
    var_dump($proxy_conf);
    echo '</pre>';
    echo "\r\n";
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "over\r\n";
echo "now-time：".date('Y-m-d H:i:s')."\r\n";
?>