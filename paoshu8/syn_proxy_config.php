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
        $proxyauth = $data['username'].':'.$data['password']; //用户名密码
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
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
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
        $proxy_data = $proxy_api['data']['list'][0] ?? [];
        return $proxy_data;
    }
}

$i = 0;
do{
    //轮训程序一直判断当前的url进行抓取判断
    $i++;
    $url =Env::get('PROXY_GET_URL');
    $proxy_data = getCurlData($url,[],false);
    $res  =getCurlData($target_url , $proxy_data,true);
    //如果能扫描到http_code=200的说明当前的curl是有效的
    if(!empty($res)  && $res['curl']['http_code'] == 200){
        echo "匹配到当前的可用代理配置:"."\r\n";
        echo '<pre>';
        print_R($res['proxy']);
        echo '</pre>';
        break;
    }

}while(true);
$exec_end_time =microtime(true); //执行结束时间
$executionTime = $exec_end_time - $exec_start_time;
echo "search proxy IP all-time: ".sprintf('%.2f',($executionTime/60))." minutes \r\n";
?>