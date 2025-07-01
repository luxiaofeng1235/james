<?php
//测试guttp的配置是否可用
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;


$client = new Client([
    'verify' => false,//配置认证
    'timeout'  => 15,//设置超时时间
    'proxy'=>'socks5://'.$proxy_server,//设置代理
]);

echo "<pre>";
var_dump($client);
echo "</pre>";
exit();
    
$list  =[
    'http://www.paoshu8.info/212_212110/196085968.html',
    'http://www.paoshu8.info/210_210232/196086130.html',
    'http://www.paoshu8.info/204_204016/196084852.html',
];
foreach($list as $k=>$v) {
    $promises[$k] = $client->getAsync($v);
}
// 发送并发请求，并等待所有请求完成（每个请求之间是异步的，因此效率非常高），返回一个响应数组
$responses = Utils::unwrap($promises);
$ret = [];
// 处理响应
foreach ($responses as $k => $response) {
    $result = $response->getBody()->getContents();
    $ret[] = $result;
    // $result即接口返回的body体，{code:0,message:ok,data:{}}，可以使用json_decode转一下
}
echo '<pre>';
print_R($ret);
echo '</pre>';
exit;

?>