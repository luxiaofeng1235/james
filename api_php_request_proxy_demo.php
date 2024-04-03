<?php

// require_once(__DIR__.'/library/init.inc.php');

// 要访问的目标页面
$targetUrl = "https://blog.csdn.net/u011382962/article/details/93041973";
$proxy['ip'] = 'socks5h://s449.kdltps.com';
$proxy['port'] = '20818';
$proxy['username'] = 't11211954910358';
$proxy['password'] = 'wp3zfa8z';
// echo '<pre>';
// print_R($proxy);
// echo '</pre>';
// exit;

$ch = curl_init();
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