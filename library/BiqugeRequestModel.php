<?php



/*
 * 处理小说的主要模型业务（台湾地区的，后期如果想兼容应该也能兼容，不需要额外处理）
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

class BiqugeRequestModel{






    /**
    * @note 获取生成不同的随机字符串，重复生成的不再使用
    *
    * @param $type string 类型
    * @return string
    */
    public static function createRandString($type = 'wandou_proxy_website')
    {
        $code = '';
        global $redis_data;
        do {
            $code = getMixedChars(1,20);
            $redisProxyDataKey = sprintf("%s_detail:%s",$type,$code);//先监测取的对应的类型是否重复
            #只有不在redis的中，说明没有重复，设置一个直接退出返回，如果在里面就一直轮询。直到找到合适的
            $getValue = $redis_data->get_redis($redisProxyDataKey);
            if (!$getValue) {
                $redis_data->set_redis($redisProxyDataKey, 1); //生成订单后保存在缓存里，防止下次重复生成
                break;
            }
        } while (true); // 防止兑换码重复
        return $code;
    }


    /**
    * @note 获取使用中的代理配置
    *
    * @return array|unknow
    */
     public static function getUsingProxy(){
        $rand_str = self::createRandString();//随机一个不同的随机数
        // $proxy_data = [
        //     'ip'    =>  'gw.wandouapp.com',//IP地址
        //     'port'  =>  '1000', //端口
        //     'username'  =>  "mlc0k5f8_session-{$rand_str}_life-5_pid-0" ,//用户名-让代理存活5分钟
        //     'password'  =>  'pw4xcjh9', //密码
        // ];
        // return $proxy_data;
       

       /////按照API去同步
        $redis_key="wandouy_proxy_cache";
        global $redis_data;
        $cacheData = $redis_data->get_redis($redis_key);
        if(!$cacheData){
            echo "代理过期，重新后去新的代理方式 \r\n";
            $res = webRequest('https://api.wandouapp.com/?app_key=e890aa7191c00cd2f641060591c4f1d0&num=1&xy=3&type=2&lb=\r\n&nr=0&area_id=&isp=0&','GET');
            $data = json_decode($res,true);
            $proxy_data = $data['data'][0] ?? [];
            // $expire_time = $proxy_data['expire_time']?? 0;
            $expire_time = date('Y-m-d H:i:s',time()+ 3*60); //默认使用三分钟
            $redis_time = strtotime($expire_time) - time(); //缓存过期时间
            $redis_data->set_redis($redis_key,json_encode($proxy_data),$redis_time);
            return $proxy_data;
        }else{
            
            $proxy_data = json_decode($cacheData ,true);
            echo "代理暂未过期old -proxy ".var_export($proxy_data,true)."\r\n";
            $cacheTime = sprintf('%.2f' , ($redis_data->ttl($redis_key)/60));
            echo "还有".$cacheTime." minutes 过期\r\n";
            return $proxy_data;
        }
        // //美国的代理 -storm静态
        // $url = 'https://api.wandouapp.com/?app_key=e890aa7191c00cd2f641060591c4f1d0&num=1&xy=3&type=2&lb=\r\n&nr=99&area_id=&isp=0&';
        // $res = webRequest($url,'GET');
        // $data = json_decode($res,true);
        // $proxy_data = $data['data'][0] ?? '';       
        return $proxy_data;
    }




    /**
    * @note 通过swoole中的request请求去获取数据信息,可以批量去进行请求处理
    *
    * @param $urls array  请求链接地址
    * @param $method 请求方式 get post
    * @return  object|unknow
    */
     public static function swooleRequest($urls=[],$method='get'){
        if(!$urls){
            return false;
        }
        //判断是否为单个传入
        if(!is_array($urls) || !isset($urls[0])){
            $urlArr[] = $urls;
            $urls = $urlArr;
        }
        #设置请求方式
        if(!in_array($method, array('get','post'))){
            $method  ='get';
        }
            
        $urls = array_filter($urls); //防止有空的url存在
        $items = [];
        $proxy_data = [];
        $exec_start_time = microtime(true);
         foreach($urls as $v){
             //这个域名走代理
             //|| strstr($v,'lmmwlkj.com') || strstr($v,'chapter.chuangke')
             if(strstr($v, 'ycukhv.com') || strstr($v, 'jhkhmgj.com') || strstr($v,'lmmwlkj.com') ){
                //获取代理
                $proxy_data = self::getUsingProxy();  
                 echo "当前域名包含有ycukhv.com OR  jhkhmgj.com  OR lmmwlkj.com 需要走代理\r\n";
                 break;
            }
         }
             
        echo "swoole队列 **** 当前请求方式 method = {$method} \r\n";
        run(function () use(&$items,$urls,$proxy_data,$method){
            $barrier = Barrier::make();
            $count = 0;
            $N = count($urls);
            $t_url =$urls;
            echo "swoole urls requests num (".$N.")\r\n";
            $http = new HttpRequest;
            $num = 0;
            foreach (range(0, $N-1) as $i) {
                $num++;
                $link = $urls[$i];
                Coroutine::create(function () use (
                    $http,
                    $barrier,
                    &$count,
                    $i,
                    &$items,
                    $urls ,
                    $proxy_data,
                    $num,
                    $method
                    ) {
                    #设置苹果浏览器的请求ua
                    $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36';
                    $cookie = 'ddd:value4';
                    if($method == 'get'){
                         $response = $http
                                    ->ua($user_agent)
                                    ->rawHeader($cookie);
                        //IP和port的代理配置
                        if(isset($proxy_data['ip']) && isset($proxy_data['port'])){
                            $response = $response->proxy($proxy_data['ip'], $proxy_data['port'], 'socks5');
                        }
                        //账密配置
                        if(isset($proxy_data['username']) && $proxy_data['password']){
                            $response =$response->proxyAuth($proxy_data['username'],$proxy_data['password']); //认证账密
                        }
                        $response = $response->get($urls[$i]);
                    }else{
                        $response = $http
                                ->ua($user_agent)
                                ->rawHeader($cookie)
                                ->post($urls[$i]);
                    }

                    $hostData = parse_url($urls[$i]??'');
                    //只要不是404页面的就直接返回，进行组装数据，其他的返回就不需要管了
                    $charaset_urls = [
                            'xuges',
                            'bqg24',
                            'siluke520'
                    ];
                    $soruce_ref = NovelModel::getSourceUrl($urls[$i]);
                    if(in_array($soruce_ref, $charaset_urls)){
                        //特殊的网站需要特殊处理下
                        $items[$hostData['path']]=$response->body('gb2312','UTF-8');
                    }else{
                        $items[$hostData['path']]=$response->body();
                    }
                    $str ="async child-fork-process \tnum = {$num} \turl = {$urls[$i]} \tstrlen =" . strlen($response->body()) . " \tcode = " . $response->getStatusCode();
                    echo $str ."\r\n";
                    //判断如果不是200的返回长什么样子
                    if($response->getStatusCode() != 200){
                        //判断如果是404给一个默认的
                        if($response->getStatusCode() == 404){
                            //获取未发现的页面的链接
                            $notFoundStr=  StoreModel::getNofFoundStr();
                            echo $notFoundStr."\r\n";
                            //这里需要赋值下，不然伦旭哪里跳不出去
                            $items[$hostData['path']] = $notFoundStr;
                        }else{
                            echo '<pre>';
                            var_dump($response->body());
                            echo '</pre>';
                            echo "\r\n";
                        }
                    }
                    System::sleep(1);
                    $count++;
                });
            }
            Barrier::wait($barrier);
            assert($count == $N);
        });
        echo "====================== swoole worker finish \r\n";
        return $items;
    }
}
?>