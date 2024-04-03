<?php

require_once(__DIR__.'/library/init.inc.php');

// 要访问的目标页面
$targetUrl = "http://www.baidu.com";
$list =range(1, 200);
foreach($list as $val){
	$urls[]=$targetUrl;
}

$item = Ares333::curlThreadList($urls);
echo "over\r\n";
die;
echo '<pre>';
print_R($item);
echo '</pre>';
exit;


$proxy['ip'] = 'socks5h://s449.kdltps.com';
$proxy['port'] = '20818';
$proxy['username'] = 't11211954910358';
$proxy['password'] = 'wp3zfa8z';
// echo '<pre>';
// print_R($proxy);
// echo '</pre>';
// exit;

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//屏蔽过滤ssl的连接
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//屏蔽ssl的主机
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
curl_setopt($ch,CURLOPT_PROXYPORT,$proxy['port']);
curl_setopt($ch,CURLOPT_PROXYUSERPWD,$proxy['username'].':'.$proxy['password']);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
curl_setopt($ch,CURLOPT_PROXYAUTH,CURLAUTH_BASIC);
$result = curl_exec($ch);
curl_close($ch);
var_dump($result);
?>