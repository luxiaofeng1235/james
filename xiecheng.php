<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');

use Yurun\Util\HttpRequest;

$arr =getQyZhimaRand();
$url = 'http://www.paoshu8.info/61_61927/141942425.html';
foreach(range(1,300) as $val){
    $urls[]=$url;
}

$items =[];
Co\run(function () use($urls,&$items){
    $wg = new \Swoole\Coroutine\WaitGroup();
    $len = count($urls);

    for ($i = 0; $i < $len; ++$i)
    {
        $wg->add();
        go(function () use ($wg,$i,$urls,&$items) {
                $http = new HttpRequest;
                $response = $http // 支持http、socks4、socks4a、socks5
                            ->ua('YurunHttp')
                            ->get($urls[$i]);
                $items[] = $response->body();
              var_dump(strlen($response->body()),$response->getStatusCode());
            echo  "num ={$i} {$urls[$i]} \r\n";
            $wg->done();
        });
    }
    $wg->wait();
});
echo '<pre>';
print_R($items);
echo '</pre>';
exit;

?>