<?php
use Ares333\Curl\Toolkit;
use Ares333\Curl\Curl;
class Ares333{

    protected static $maxThread   =60 ; //最大线程数配置
    protected static $maxTry  = 3;//最大试错的次数

    /**
    * @note 获取基本的请求-一般适用于单个URL获取
    *
    * @param $url strsing 获取的url
    * @return array
    */
    public static function getBaseCurl($url){
        if(!$url)
            return false;
        $curl = new Curl();
        $callback = [];
        $curl->add(
            array(
                'opt' => array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true, //通过他来控制是否输出到屏幕上
                ),
                'args' => 'This is user argument'
            ),
            function ($r, $args) use(&$callback){
                $callback['status'] = "Request success for " . $r['info']['url'];
                $callback['args'] = $args;
                $callback['http_code'] = $r['info']['http_code'];
                $callback['body_size'] = strlen($r['body']) . ' bytes';
                $callback['content']  = $r['body'];
                return $callback;
            });
        $curl->start();
        return $callback;
    }

     /**
    * @note 实现文件的下载
    *
    * @param $url strsing 获取的url
    * @return array
    */
    public static function downloadFile($url){
        $curl = new Curl();
        //$url = 'https://www.baidu.com/img/bd_logo1.png';
        $file = __DIR__ . '/download.png';
        // $fp is closed automatically on download finished.
        $fp = fopen($file, 'w');
        $curl->add(
            array(
                'opt' => array(
                    CURLOPT_URL => $url,
                    CURLOPT_FILE => $fp,
                    CURLOPT_HEADER => false
                ),
                'args' => array(
                    'file' => $file
                )
            ),
            function ($r, $args) {
                if($r['info']['http_code']==200) {
                    echo "download finished successfully, file=$args[file]\n";
                }else{
                    echo "download failed\n";
                }
            })->start();
        return $file;
    }

    /**
    * @note 获取当前的代理IP配置信息
    *
    * @param
    * @return
    */

    private static function getProxyData($pro_type){
         $proxy_conf = [];
        // try{
        switch ($pro_type) {
            case 'story':
                $proxy_conf= getZhimaProxy();//获取同步小说的基础章节
                break;
            case 'count':
                $proxy_conf = getMobileProxy();//获取移动端的统计章节的新的代理
                break;
            case 'empty':
                $proxy_conf = getMobileEmptyProxy();//获取移动端的数据为空爬取新的代理
                break;
            case 'image':
                $proxy_conf = getImgProxy();//获取下载图片使用的代理
                break;
        }
        return $proxy_conf;
    }

    /**
    * @note 多任务请求请求数据
    *
    * @param $urls strsing 请求的url列表
    * @return array
    */
    public static function curlThreadList($url=[],$type= 1){
        if(!$url)
            return false;
        //获取配置的代理信息
        $rand_str = ClientModel::getRandProxy();
        $proxy_data= self::getProxyData($rand_str);
        if(!$proxy_data){
            echo '【调用位置：Ares333类】 当前代理IP已经过期了，请稍等片刻 --------！'.PHP_EOL;
            NovelModel::killMasterProcess(); //结束当前进程
            exit(1);
        }

        if(!is_array($url)){
            $urls[] =$url;
        }else{
            $urls = $url;
        }

        $urls = array_filter($urls);
        if(!$urls || count($urls) == 0)
            return false;

        $toolkit = new Toolkit();
        $toolkit->setCurl();
        $curl = $toolkit->getCurl();
        $curl->maxThread = self::$maxThread;//开启几个线程访问
        $curl->maxTry = self::$maxTry;//最大重试次数
        $curl->onInfo = array(
            $toolkit,
            'onInfo'
        );
        $response = []; //接收返回的参数
        $curl->onTask = function ($curl) use(&$response,$urls,$proxy_data){
            static $i = 0;
            $urlCount = count($urls);
            if ($i >= $urlCount) {
                return;
            }
            // echo $proxy_data['ip'].'---'.$proxy_data['port'];
            // dd($proxy_data);

            // echo "\n我是属于其中的一个值 ---". $t[$i]."\r\n";
            // echo "\ncurrent tiems：{$i} \r\n";
            /** @var Curl $curl */
            //速度控制
            $speed = 100000;
            $curl->add(
                array(
                    'opt' => array(
                        CURLOPT_URL => $urls[$i], //指定url
                        CURLOPT_HEADER => false, //是否需要返回HTTP头
                        CURLOPT_RETURNTRANSFER => true, //通过他来控制是否输出到屏幕上
                        CURLOPT_FOLLOWLOCATION  => true,//自动跟踪
                        // 检查是否断联，每10秒发送一次心跳
                         CURLOPT_MAXREDIRS   =>  7,
                        //CURLOPT_TCP_KEEPALIVE =>  1, // 开启
                        //
                        CURLOPT_MAX_RECV_SPEED_LARGE    =>  400000, //设置300K的下载速度
                        // CURLOPT_TCP_KEEPIDLE    => 10, // 空闲10秒问一次
                        // CURLOPT_TCP_KEEPINTVL   => 10,// 每10秒问一次
                        CURLOPT_TIMEOUT => 120,//超时时间(s)
                        CURLOPT_HTTPHEADER  =>  array("Expect:"),//增加配置完整接收数据配置buffer的大小
                        // CURLOPT_HTTPHEADER  => array('Connection: keep-alive','Keep-Alive: 300'),//设置keep-alive
                        // CURLOPT_FORBID_REUSE    => false, //在完成交互以后强迫断开连接，不能重用
                        CURLOPT_HTTPGET => true, //启用时会设置HTTP的method为GET，因为GET是默认是，所以只在被修改的情况下使用。
                        CURLOPT_ENCODING    =>  'gzip',
                        //设置代理服务器相关的
                        CURLOPT_PROXY   =>  $proxy_data['ip'], //代理IP的服务器地址
                        CURLOPT_PROXYPORT   =>  $proxy_data['port'],//代理IP的端口
                        CURLOPT_PROXYTYPE   =>  CURLPROXY_SOCKS5, //指定代理IP的类型
                        CURLOPT_PROXYAUTH   =>  CURLAUTH_BASIC, //代理认证模式
                    ),
                    'args'  =>  'This is user argument',
                ),
                function ($r, $args) use(&$response){
                    $info['status'] = "Request success for " . $r['info']['url'];
                    $info['args'] = $args;
                    $info['http_code'] = $r['info']['http_code'];
                    $info['body_size'] = strlen($r['body']) . ' bytes';
                    $info['content']  = array_iconv($r['body']); //转换编码
                    $response[] = $info;
                    return $response;
            });
            $i ++; //自动累加，防止出错
        };
        $curl->start();
        return $response ?? [];
     }

}
?>