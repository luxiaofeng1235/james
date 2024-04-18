<?php

require_once( __DIR__.'/library/init.inc.php');

use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;
use Yurun\Util\HttpRequest;


function getUrl($urls = []){
    if(!$urls){
        return false;
    }
    $urls = array_filter($urls); //防止有空的url存在
    $items = [];
    $exec_start_time = microtime(true);
    run(function () use(&$items,$urls){
        $barrier = Barrier::make();
        $count = 0;
        $N = count($urls);
        $t_url =$urls;
        echo "urls num：".$N."\r\n";
        $http = new HttpRequest;
        foreach (range(0, $N-1) as $i) {
            $link = $urls[$i];
            Coroutine::create(function () use ($http,$barrier, &$count,$i,&$items,$urls) {
                $response = $http->ua('YurunHttp')
                                 // ->proxy('7b2f9a6713186a90.asd.as.roxlabs.vip', '4600', 'socks5') //认证类型设置
                                 // ->proxyAuth('user-red1235-region-tw-sessid-twZh0b2ezH-sesstime-5-keep-true','123456abc') //认证账密
                                 ->get($urls[$i]);
                // echo $file.PHP_EOL;
                echo "num = {$i} \t url = {$urls[$i]}\r\n";
                $items[]=$response->body();
                var_dump(strlen($response->body()),$response->getStatusCode());
                // if($response->getStatusCode() != 200){
                //     echo "获取数据失败=============================\r\n";
                // }
                System::sleep(0.5);
                $count++;
            });
        }
        Barrier::wait($barrier);
        assert($count == $N);
    });
    return $items;
}

$url  = 'https://www.xsw.tw/book/1144673/248318027.html';
for ($i=0; $i <10 ; $i++) {
    $urls [] = $url;
}

$list = getUrl($urls);
echo 333;exit;

echo '<pre>';
print_R($list);
echo '</pre>';
exit;
exit;
echo '<pre>';
print_R($items);
echo '</pre>';
exit;

$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>