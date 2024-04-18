<?php

require_once( __DIR__.'/library/init.inc.php');

use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;
use Yurun\Util\HttpRequest;


$items = [];
$exec_start_time = microtime(true);
run(function () use(&$items){
    $barrier = Barrier::make();
    $count = 0;
    $N = 200;
    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count,$i,&$items) {
            $http = new HttpRequest;
             $response = $http->ua('YurunHttp')
                            ->proxy('tw.ipdodo.cloud', '10801', 'socks5') //认证类型设置
                            ->proxyAuth('n1_1712733036-dh-2-region-tw','11e475e0') //认证账密
                             ->get('https://www.xsw.tw/book/1144673/248318027.html');
            // echo $file.PHP_EOL;
            echo "num = {$i} \r\n";
            $items[]=$response->body();
            var_dump(strlen($response->body()),$response->getStatusCode());
            // if($response->getStatusCode() != 200){
            //     echo "获取数据失败=============================\r\n";
            // }
            System::sleep(1);
            $count++;
        });
    }
    Barrier::wait($barrier);
    assert($count == $N);
});
exit;
echo '<pre>';
print_R($items);
echo '</pre>';
exit;

$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>