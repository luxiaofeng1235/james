<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');

use Yurun\Util\HttpRequest;

$arr =getQyZhimaRand();
$url = 'http://m.paoshu8.info/wapbook-21437-139847322-3';
foreach(range(1,20) as $val){
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
                // echo "url = ".$urls[$i]."\r\n";
                $res =  guzzleHttp::multi_req(array($urls[$i]));
                $info = $res[0] ?? '';
                $items[] = $info;

                // $http = new HttpRequest;
                // $proxy_data = getQyZhimaRand();
                // $proxy_data = combineProxyParam($proxy_data);
                // $response = $http // 支持http、socks4、socks4a、socks5
                //             ->ua('YurunHttp')
                //             ->proxy($proxy_data['ip'], $proxy_data['port'], 'socks5')
                //             ->get('http://m.paoshu8.info/wapbook-21437-139847322-3');

                // echo 'html:', PHP_EOL, $response->body();
             // var_dump(strlen($response->body()),$response->getStatusCode());
            // echo  "num ={$i} +++++++++++++++++++++++++++++++++++++++ \r\n";
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