<?php

/*
 * 同步代理IP到缓存中去，通过请求url来判断相关的数据信息
 * 主要同步的数据有以下几个流程：
 *
 */
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
$exec_start_time =microtime(true);

$target_url = Env::get('APICONFIG.PAOSHU_HOST');//需要抓取的url
$expire_time = NovelModel::$redis_expire_time;//过期时间设置2个小时的过期
//设置缓存的key

$redis_cache_key = Env::get('ZHIMA_REDIS_KEY');

echo "key：".$redis_cache_key."\r\n";
/**
* @note 获取当前的curl信息
*
* @param $url string 当前的url信息
* @param $data arary 代理参数
* @param $is_proxy bool 是否开启代理
* @return
*/

function getCurlData($url,$data=[],$is_proxy =false){
    if(!$url )
        return false;
    if($is_proxy){
        $proxy = $data['ip']; //代理IP
        $port = $data['port']; //端口
        if(isset($data['username']) && isset($data['password'])){
            $proxyauth = $data['username'].':'.$data['password']; //用户名密码
        }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    if($is_proxy){
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        if(isset($data['username']) && isset($data['password'])){
             curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
        }
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $curl_scraped_page = curl_exec($ch);
    $httpcode = curl_getinfo($ch);
    curl_close($ch);//关闭cURL会话
    if($is_proxy){
        $return = ['curl' => $httpcode,'proxy'=>$data];
        return $return;
    }else{
        //非代理模式请求下
        $proxy_api  = json_decode($curl_scraped_page,true);
        $proxy_data = $proxy_api['data'][0] ?? [];
        return $proxy_data;
    }
}

$i = 0;
echo "link-url：".$target_url."\r\n";
// $url =Env::get('PROXY_GET_URL');
$url = Env::get('ZHIMAURL');//轮训这个芝麻的url信息
do{
    /*
    * 整体思路：
     *
    * 1、先判断缓存中是否有可用的代理配置，如果没有缓存就刷新到cache中
    * 2、还有一种情况是，缓存未过期，但是代理失效了，还需要请求后去更新
     */
    $is_save_data = $redis_data->get_redis($redis_cache_key);
    if(!$is_save_data){
        //轮训程序一直判断当前的url进行抓取判断
        $i++;

        $proxy_data = getCurlData($url,[],false);
        $res  =getCurlData($target_url , $proxy_data,true);
        //如果能扫描到http_code=200的说明当前的curl是有效的
        if(!empty($res)  && $res['curl']['http_code'] == 200){
            echo "匹配到当前的可用代理配置:"."\r\n";
            echo '<pre>';
            print_R($res['proxy']);
            echo '</pre>';
            $redis_data->set_redis(
                $redis_cache_key,
                json_encode($res['proxy']) ,
                $expire_time
            );
            break;
        }
    }else{
        echo "加载redis中缓存中的代理信息\r\n";
        $have_data = json_decode($is_save_data,true);
        $res  =getCurlData($target_url , $have_data,true);
        //如果访问不是200的话，重新请求刷新到redis
        if($res['curl']['http_code'] != 200){
             //先删除redis的缓存信息
            $redis_data->del_redis($redis_cache_key);
            //默认轮询100次去请求最新的未使用的代理
            for ($i=0; $i <300 ; $i++) {
                 $proxy_try_data = getCurlData($url,[],false);
                 //使用代理
                 $t_res =getCurlData($target_url , $proxy_try_data,true);
                 //如果在请求里重新匹配到了新的代理，就重新刷新缓存
                 if( $t_res['curl']['http_code'] == 200 ){
                     $now_proxy_use = $t_res['proxy'] ?? [];
                     echo "重新匹配到了新的可用的代理配置\r\n";
                     $redis_data->set_redis(
                            $redis_cache_key,
                            json_encode($now_proxy_use) ,
                            $expire_time
                        );
                     echo '<pre>';
                     print_R($now_proxy_use);
                     echo '</pre>';
                     break;
                 }
            }
            break;
        }else{
            echo "当前的代理可用,缓存中的数据还能用!!!\r\n";
            echo '<pre>';
            print_R($have_data);
            echo '</pre>';
            //如果是200就直接退出
            break;
        }

    }
}while(true);
$exec_end_time =microtime(true); //执行结束时间
$executionTime = $exec_end_time - $exec_start_time;
echo "now-time：".date('Y-m-d H:i:s')."\r\n";
echo "\r\nsearch proxy IP all-time: ".sprintf('%.2f',($executionTime/60))." minutes \r\n";
?>