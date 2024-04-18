<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once(__DIR__.'/library/init.inc.php');

use Swoole\Timer;
use Yurun\Util\HttpRequest;
use Yurun\Util\YurunHttp\ConnectionPool;
use Yurun\Util\YurunHttp\Handler\Swoole\SwooleHttpConnectionManager;


$exec_start_time = microtime(true);
function dumpPoolInfo()
{
    foreach (SwooleHttpConnectionManager::getInstance()->getConnectionPools() as $pool)
    {
        // var_dump($pool->getConfig()->getUrl() . ': Count=' . $pool->getCount() . ', Free=' . $pool->getFree() . ', Used=' . $pool->getUsed());
    }
}

// 启用连接池
ConnectionPool::enable();

// 为这个地址设置限制连接池连接数量3个
// 一定不要有 / 及后续参数等
ConnectionPool::setConfig('https://www.xsw.tw', 16);
Co\run(function () {
    dumpPoolInfo();

    $timer = Timer::tick(300, function () {
        dumpPoolInfo();
    });

    $wg = new \Swoole\Coroutine\WaitGroup();
    for ($i = 0; $i < 200; ++$i)
    {
        $wg->add();
        go(function () use ($wg,$i) {
            co::sleep(1);
            $proxy_data = getQyZhimaRand();
            //转换对应的字段
            $proxy_data = combineProxyParam($proxy_data);
            $http = new HttpRequest();
            $response = $http
                        // ->proxy($proxy_data['ip'], $proxy_data['port'], 'socks5')
                        ->get('https://www.xsw.tw/book/3.html');
            var_dump(strlen($response->body()),$response->getStatusCode());

            echo  "num ={$i} +++++++++++++++++++++++++++++++++++++++ \r\n";
            $wg->done();
        });
    }
    $wg->wait();
    Timer::clear($timer);

    dumpPoolInfo();
});
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>