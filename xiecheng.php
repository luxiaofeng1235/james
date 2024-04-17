<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');

use Yurun\Util\HttpRequest;

$arr =getQyZhimaRand();
$url = 'http://m.paoshu8.info/wapbook-158797-176816601-1';
foreach(range(1,35) as $val){
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
                $proxy_data = getQyZhimaRand();
                $proxy_data = combineProxyParam($proxy_data);
                $response = $http // 支持http、socks4、socks4a、socks5
                            ->ua('YurunHttp')
                            ->proxy($proxy_data['ip'], $proxy_data['port'], 'socks5')
                            ->get($urls[$i]);

             //    echo 'html:', PHP_EOL, $response->body('big5','utf8');
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