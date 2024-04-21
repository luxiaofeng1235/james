<?php

require_once( __DIR__.'/library/init.inc.php');

use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;
use Yurun\Util\HttpRequest;


//获取URL的配置信息
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
            /*
            代理网络: hk.stormip.cn
            端口: 1000
            账户名: storm-jekines_area-TW_session-123456_life-5
            密码: 123456
             */
            Coroutine::create(function () use ($http,$barrier, &$count,$i,&$items,$urls) {
                $response = $http->ua('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0')
                                 ->rawHeader('ddd:value4')
                                 ->proxy('proxy.stormip.cn', '1000', 'socks5') //认证类型设置
                                 ->proxyAuth('storm-jekines_area-TW_session-123456','123456') //认证账密
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

$url  = 'https://www.xsw.tw/wanben_3';
for ($i=0; $i <30 ; $i++) {
    $urls [] = $url;
}

$list = StoreModel::swooleRquest($urls);
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