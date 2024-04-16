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
ConnectionPool::setConfig('http://m.paoshu8.info', 16);

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
            $http = new HttpRequest();
            $response = $http->get('https://www.baidu.com/s?wd=%E5%AD%A6%E4%B9%A0%E6%80%BB%E4%B9%A6%E8%AE%B0%E5%BC%BA%E8%B0%83%E7%9A%84%E6%80%BB%E4%BD%93%E5%9B%BD%E5%AE%B6%E5%AE%89%E5%85%A8%E8%A7%82&sa=fyb_n_homepage&rsv_dl=fyb_n_homepage&from=super&cl=3&tn=baidutop10&fr=top1000&rsv_idx=2&hisfilter=1');
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