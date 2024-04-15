<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once(__DIR__.'/library/init.inc.php');

use Swoole\Timer;
use Yurun\Util\HttpRequest;
use Yurun\Util\YurunHttp\ConnectionPool;
use Yurun\Util\YurunHttp\Handler\Swoole\SwooleHttpConnectionManager;

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
ConnectionPool::setConfig('https://www.xsw.tw', 10);

Co\run(function () {
    dumpPoolInfo();

    $timer = Timer::tick(500, function () {
        dumpPoolInfo();
    });

    $wg = new \Swoole\Coroutine\WaitGroup();
    for ($i = 0; $i < 20; ++$i)
    {
        $wg->add();
        go(function () use ($wg,$i) {
            $http = new HttpRequest();
            $response = $http->get('https://www.xsw.tw/book/1654104.html');
            // var_dump($response->body());

            echo  "num ={$i} +++++++++++++++++++++++++++++++++++++++ \r\n";
            $wg->done();
        });
    }
    $wg->wait();
    Timer::clear($timer);

    dumpPoolInfo();
});

?>