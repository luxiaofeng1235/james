<?php

require_once( __DIR__.'/library/init.inc.php');

use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;
use Yurun\Util\HttpRequest;



$exec_start_time = microtime(true);
run(function () {
    $barrier = Barrier::make();

    $count = 0;
    $N = 5;

    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count,$i) {
             $http = new HttpRequest;
             $response = $http->ua('YurunHttp')
                             ->get('http://www.baidu.com/');
            echo '<pre>';
            var_dump($response);
            echo '</pre>';
            // echo $file.PHP_EOL;
            echo "num = {$i} \r\n";
            System::sleep(0.5);
            $count++;
        });
    }
    Barrier::wait($barrier);

    assert($count == $N);
});

$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>