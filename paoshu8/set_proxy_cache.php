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
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
$exec_start_time = microtime(true);
$proxy_conf = [];
$redis_cache_key = Env::get('ZHIMA_REDIS_KEY');
$proxy = $redis_data->get_redis($redis_cache_key);
if(!$proxy){
    do{
    $url = Env::get('ZHIMAURL');//获取配置的代理URL
    $info = webRequest($url,'GET');
    $proxy_info  =    json_decode($info , true);
    $proxy_data = $proxy_info['data'][0] ?? [];
     //检测目标网站是否可用，如果不可用就取一个可用的
     $check_data = curlProxyState(Env::get('APICONFIG.PAOSHU_HOST'),$proxy_data);
     if($check_data['http_code'] == 200){
        //如果检测到可用的IP，就直接退出循环
        $proxy_conf = $proxy_data;
        break;
     }
    }while(true);
    $diff_time = 30 * 60;//默认先控制30分钟的缓存，防止提前过期
    $redis_data->set_redis($redis_cache_key,json_encode($proxy_conf),$diff_time);
    echo "获取到新的代理信息\r\n";
    echo '<pre>';
    print_R($proxy_conf);
    echo '</pre>';
    echo "\r\n";
}else{
    echo "redis里的代理暂未过期,还能用\r\n";
    $proxy_conf = json_decode($proxy , true);
    echo '<pre>';
    var_dump($proxy_conf);
    echo '</pre>';
    echo "\r\n";
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "over\r\n";
echo "now-time：".date('Y-m-d H:i:s');
?>