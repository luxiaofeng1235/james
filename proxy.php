<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
$res = Env::get('REDIS.HOST_NAME');


echo "<pre>";
var_dump($res);
echo "</pre>";
exit();

echo 333;exit;
//http://webapi.http.zhimacangku.com/getip?neek=321a408a&num=1&type=1&time=3&pro=0&city=0&yys=0&port=2&pack=0&ts=0&ys=0&cs=0&lb=1&sb=&pb=4&mr=1&regions=
$proxyInfo = getZhimaProxy();
$url ='http://www.baidu.com/';
$proxy = $proxyInfo['ip'];
$port = $proxyInfo['port'];
$proxyauth = '';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_PROXYPORT, $port);
if($proxyauth){
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
}
curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22");
curl_setopt($ch, CURLOPT_ENCODING ,'gzip'); //加入gzip解析
curl_setopt($ch, CURLOPT_HEADER, 0);
$curl_scraped_page = curl_exec($ch);
$httpcode = curl_getinfo($ch);
$header =  curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);//关闭cURL会话
echo '<pre>';
print_R($httpcode);
echo '</pre>';
exit;
echo '<pre>';
var_dump($curl_scraped_page);
echo '</pre>';
exit;
?>