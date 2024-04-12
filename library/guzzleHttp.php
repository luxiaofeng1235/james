<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :process_url.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:利用guzzle处理抓取网络请求-效率高
// ///////////////////////////////////////////////////
// use GuzzleHttp\Pool;
// use GuzzleHttp\Client;
// use GuzzleHttp\Psr7\Request;
// use GuzzleHttp\Promise\Utils;
// use GuzzleHttp\Exception\BadResponseException;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Event\BeforeEvent;


use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response as Response1;
// use GuzzleHttp\Message\Response;


class guzzleHttp{

    //网站抓取的cookie
    private static $cookie  = 'width=85%25; Hm_lvt_61f1afd153b0a37229eefe873fe6586a=1710121429,1710213284,1710295133,1710378533; Hm_lpvt_61f1afd153b0a37229eefe873fe6586a=1710403476';//设置网站的一个cookie

    private static $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36'; //客户端参数

    private static $content_type = 'text/html;charset=UTF-8'; //设置页面的content-type

    private static $requestMehtod = 'XMLHttpRequest'; //默认的发送请求

    private static $accept = 'text/html'; //接收的编码格式

    private static $encoding = 'gzip, deflate, br'; //默认的解码格式


      /**
    * @note 获取系统代理
    *
    * @param $reqs array 网络请求地址
    * @return object
    */
    protected static function getSystemProxy(){
        $client = new Client();
        $url = Env::get('PROXY_GET_URL');
        $res = $client->request('GET', $url);
        $info =(string) $res->getBody();
        $data = json_decode($info,true);
        if($data['code'] == 1){
            $proxy_data = $data['data']['list'][0] ?? [];
            extract($proxy_data);
            $proxy_server =$username .':'.$password .'@'. $ip .':'.$port;
            return $proxy_server;
        }
    }

    /**
    * @note 批量获取请求的数据
    *
    * @param $reqs array 网络请求地址
    * @param $pro_type count :章节统计抓取类型 empty :列表为空的代理抓取类型
    * @return object
    */
   public static function multi_req($reqs ,$pro_type ='count')
    {
        if(!$reqs)
            return [];

        //校验传过来的不是数组
        if(!is_array($reqs)){
            $reqs = array($reqs);
        }
        // $proxy_conf = [];
        // // try{
        // switch ($pro_type) {
        //     case 'story':
        //         $proxy_conf= getZhimaProxy();//获取同步小说的基础章节
        //         break;
        //     case 'count':
        //         $proxy_conf = getMobileProxy();//获取移动端的统计章节的新的代理
        //         break;
        //     case 'empty':
        //         $proxy_conf = getMobileEmptyProxy();//获取移动端的数据为空爬取新的代理
        //         break;
        //     case 'image':
        //         $proxy_conf = getImgProxy();//获取下载图片使用的代理
        //         break;
        // }
        // if(!$proxy_conf){
        //     echo '【调用位置：guzzleHttp类】 当前代理IP已经过期了，重新获取吧--------！'.PHP_EOL;
        //     NovelModel::killMasterProcess(); //结束当前进程
        //     exit(1);
        // }
        // extract($proxy_conf);
        // $proxy_server =$ip .':'.$port;
        // if(!$proxy_server)
        //     return false;


      $proxy_info = webRequest('http://api.tq.roxlabs.cn/getProxyIp?num=1&return_type=json&lb=1&sb=&flow=1&regions=tw&protocol=socks5','GET');
      $tdata = json_decode($proxy_info,true);
      $proxy_data = $tdata['data'][0] ??[];
      //转换字段
        $proxy_data = combineProxyParam($proxy_data);
        if(!$proxy_data){
            echo '【调用位置：guzzleHttp类】 当前代理IP已经过期了，重新获取吧--------！'.PHP_EOL;
            NovelModel::killMasterProcess(); //结束当前进程
            exit(1);
        }

        $proxy_server =$proxy_data['ip'] .':'.$proxy_data['port'];
        $client = new Client([
            'verify' => false,//配置认证
            'http_errors'     => true,
            'decode_content'  => true,
            'timeout'  => 300,//设置超时时间
            'connect_timeout' => 10,
            'proxy'=>'socks5://'.$proxy_server,//设置代理
            'headers'    =>[
                'Content-Type'=>self::$content_type, //content-type信息
                'Accept'    => self::$accept,//接收的编码格式
                'Accept-Encoding' => self::$encoding,
                'User-Agent'    => self::$userAgent, //客户端参数
                'Cookie'    =>  self::$cookie, //cookie设置
                'x-requested-with' => self::$requestMehtod //x-rquest-with参数
            ]
        ]);
        //采用多线程的getAsync去并发请求
        foreach($reqs as $val){
            $promises[] = $client->getAsync($val);
        }
        $items = [];

        // 等待请求完成，即使其中一些请求已经失败
        //利用unwap的异步来请求，这个效率比较高
        // {"image":{"state":"fulfilled","value":{}},"jpeg":{"state":"fulfilled","value":{}},"png":{"state":"fulfilled","value":{}},"webp":{"state":"fulfilled","value":{}}}
        $responses = Utils::settle($promises)->wait();
        foreach ($responses as $key => $value) {
            if ($value['state'] === 'fulfilled') {
                    $contents = (string) $value['value']->getBody()->getContents();
            } elseif ($value['state'] === 'rejected') {
                $contents = "key:" . $key . "请求失败或code不是200=====";
            }
            $items[] = $contents;
        }
        return $items;
            // 创建多个请求，请求地址可以相同，也可以不同
        //     foreach($reqs as $val){
        //         $promises[] = $client->getAsync($val);
        //     }

        //     $items =[];
        //     //利用unwap的异步来请求，这个效率比较高
        //     $responses = Utils::unwrap($promises);
        //     foreach ($responses as $k => $response) {
        //         $result =(string) $response->getBody()->getContents();
        //         $items[]=$result;
        //         // $result即接口返回的body体，{code:0,message:ok,data:{}}，可以使用json_decode转一下
        //     }
        //     return $items;
        // }catch (\GuzzleHttp\Exception\RequestException $e) {
        //      echo $e->getMessage()."\n";
        //     if ($e->hasResponse()) {
        //         echo $e->getResponse()->getStatusCode().' '.$e->getResponse()->getReasonPhrase()."\n";
        //         echo $e->getResponse()->getBody();
        //     }
        //     return;
        //     // do something with json string...
        // }
        // return $responses;
    }

    /**
    * @note 保存下载图片到本地
    *
    * @param $url string 网络图片
    * @return stream
    */
    public static function saveRemotePic($url){
        if(!$url)
            return false;
        $proxy_server = NovelModel::getProxyItem();
            if(!$proxy_server)
                return false;
        $client = new Client([
            'verify' => false,//配置认证
            'http_errors'     => true,
            'decode_content'  => true,
            'timeout'  => 300,//设置超时时间
            'proxy'=>'socks5://'.$proxy_server,//设置代理
            'headers'    =>[
                'Content-Type'=>self::$content_type, //content-type信息
                'Accept'    => self::$accept,//接收的编码格式
                'Accept-Encoding' => self::$encoding,
                'User-Agent'    => self::$userAgent, //客户端参数
                'Cookie'    =>  self::$cookie, //cookie设置
                'x-requested-with' => self::$requestMehtod //x-rquest-with参数
            ]
        ]);
        $filename = '/root/file2.jpg';
        $response = $client->get($url, ['sink' => $filename]);
        return $filename;
    }
}
?>