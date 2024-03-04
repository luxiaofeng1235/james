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

$target_url = Env::get('APICONFIG.WEB_SOTRE_HOST');//需要抓取的url

function getCurlData($url,$data){
    if(!$url || !$data)
        return false;
    $proxy = $data['ip']; //代理IP
    $port = $data['port']; //端口
    $proxyauth = $data['username'].':'.$data['password']; //用户名密码
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYPORT, $port);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $curl_scraped_page = curl_exec($ch);
    $httpcode = curl_getinfo($ch);
    $return = ['curl' => $httpcode,'proxy'=>$data];
    echo '<pre>';
    print_R($return);
    echo '</pre>';
    exit;
    return $return;
}

$i = 0;
do{
    //轮训程序一直判断当前的url进行抓取判断
    $i++;
    $url =Env::get('PROXY_GET_URL');
    $item = webRequest($url,'GET');
    $tscode  = json_decode($item,true);
    $proxy_data = $tscode['data']['list'][0] ?? [];
    $res  =getCurlData($target_url , $proxy_data);
    //如果能扫描到http_code=200的说明当前的curl是有效的
    if(!empty($res)  && $res['curl']['http_code'] == 200){
        echo "匹配到当前的可用代理配置:"."\r\n";
        echo '<pre>';
        print_R($res['proxy']);
        echo '</pre>';
        exit;
    }

}while(true);
$exec_end_time =microtime(true); //执行结束时间
$executionTime = $exec_end_time - $exec_start_time;
echo "search proxy IP time: ".sprintf('%.2f',($executionTime/60))." minutes \r\n";
?>