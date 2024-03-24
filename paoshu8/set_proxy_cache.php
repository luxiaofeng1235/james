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
* @note 获取并排除指定地区的代理IP
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
    $diff_time = 15*60; //设置代理的存活时间
    do{
        $info = webRequest($url,'GET');
        $proxy_info  =    json_decode($info , true);
        $proxy_data = $proxy_info['data'][0] ?? [];
        if(!empty($proxy_data)){
            $now_time = time();
            $expire_time = $proxy_data['expire_time'] ?? '';
            //利用过期时间-当前时间计算代理的可用时间
            $t = strtotime($expire_time) - $now_time;
            if($t>=$diff_time){
                $new_proxy = $proxy_data;
                break;
            }
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
$proxy_conf = [];
echo "cache_key：".$redis_cache_key."\r\n";
$host  =  isset($argv[2]) ? trim($argv[2]) : Env::get('APICONFIG.PAOSHU_HOST') ;
echo "检测的url：".$host."\r\n";
// $redis_data->del_redis($redis_cache_key);
$proxy = $redis_data->get_redis($redis_cache_key);
$diff_time = 15*60; //设置代理的存活时间在15分钟以上，保证小说突然被挂掉
if(!$proxy){
    do{
        $info = webRequest($url,'GET');
        $proxy_info  =    json_decode($info , true);
        $proxy_data = $proxy_info['data'][0] ?? [];
        $expire_time = $proxy_data['expire_time'] ?? '';
        //利用过期时间-当前时间计算代理的可用时间,判断是否满足15分钟以上
        $unix_time = strtotime($expire_time) - time();
        //先判断是否满足15分钟以上的代理如果满足了再检测代理的可用性
        if($unix_time>=$diff_time){
            //检测目标网站是否可用，如果不可用就取一个可用的
            $check_data = curlProxyState($host,$proxy_data);
            if($check_data['http_code'] == 200){
                //如果检测到可用的IP，就直接退出循环
                $proxy_conf = $proxy_data;
                break;
            }
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
    echo "剩余可用缓存时间：".sprintf('%.2f',($ttl/60))." minutes\r\n";
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