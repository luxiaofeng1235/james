<?php



/*
 * 处理小说的主要模型业务（台湾地区的）
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
use QL\QueryList;
use Swoole\Timer;
use Yurun\Util\YurunHttp\ConnectionPool;
use Yurun\Util\YurunHttp\Handler\Swoole\SwooleHttpConnectionManager;

##swoole相关的
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;
use Yurun\Util\HttpRequest;

class StoreModel{

     private static $imageType ='jpg';//默认的头像

     public static $file_type = 'txt'; //存储为txt的格式文件


     /**
     * @note 替换指定url中的参数
     *
     * @param $url string  链接地址
     * @param $key string 标识
     * @param $paaram string 需要替换的变量和参数信息
     * @return  str
     */
     public static function replaceParam($url , $key = '',$param=''){
        if(!$url || !$key){
            return false;
        }
        $replaceKey = '{$'.$key.'}'; //待替换的字符串
        $host_url = str_replace($replaceKey,$param,$url);
        if(!$host_url){
            return '';
        }
        return $host_url;
     }

    /**
    * @note 通过swoole中的request请求去获取数据信息,可以批量去进行请求处理
    *
    * @param $url string  链接地址
    * @return  str
    */
     public static function swooleRquest($urls){
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
                    var_dump("strlen =" . strlen($response->body()),"code = " . $response->getStatusCode());
                    echo "\r\n";
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
}
?>