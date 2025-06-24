<?php
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;


$t =range(1,100);
foreach($t as $key =>$val){
    $urls[]='http://www.baidu.com';
}

run(function () use($urls){
        $barrier = Barrier::make();
        $count = 0;
        $N = count($urls);
        echo "swoole urls requests num (".$N.")\r\n";
        $num = 0;
        foreach (range(0, $N-1) as $i) {
            $num++;
            $link = $urls[$i];
            //利用屏障的create来开启对应的配置信息
            Coroutine::create(function () use (
                $barrier,
                 $urls,
                &$count,
                $i,
                $num
                ) {
                echo "async child-fork-process \tnum = {$num} \turl = {$urls[$i]} \r\n";
                System::sleep(1);
                $count++;
            });
        }
        Barrier::wait($barrier);
        assert($count == $N);
    });
?>