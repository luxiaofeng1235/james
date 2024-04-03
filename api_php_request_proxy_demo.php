<?php
ini_set('memory_limit','9000M');
require_once(__DIR__.'/library/init.inc.php');
$url = 'http://www.paoshu8.info/0_859/653516.html';
$t =range(1,300);
foreach($t as $val){
    $urls[]=$url;
}


$item = Ares333::curlThreadList($urls);
echo "over\r\n";
exit();
echo '<pre>';
print_R($item);
echo '</pre>';
exit;

// // 要访问的目标页面
$targetUrl = "http://www.baidu.com";
$proxy['ip'] = '221.229.212.170';
$proxy['port'] = '40137';
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//屏蔽过滤ssl的连接
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//屏蔽ssl的主机
curl_setopt($ch, CURLOPT_URL, $targetUrl);

curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
curl_setopt($ch,CURLOPT_PROXYPORT,$proxy['port']);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
curl_setopt($ch,CURLOPT_PROXYAUTH,CURLAUTH_BASIC);
$result = curl_exec($ch);
curl_close($ch);
var_dump($result);
?>