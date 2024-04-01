<?php
use Ares333\Curl\Toolkit;
use Ares333\Curl\Curl;
class Ares333{

    protected static $maxThread   =20 ; //最大线程数配置
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
        // $proxy_data = Ares333::getProxyData('story');
        // if(!$proxy_data){
        //     return '当前未获取到有效代理';
        // }
        if(!is_array($url)){
            $urls[] =$url;
        }else{
            $urls = $url;
        }
        $urls = array_filter($urls);
        if(!$urls)
            return false;

        $toolkit = new Toolkit();
        $toolkit->setCurl();
        $curl = $toolkit->getCurl();
        $curl->maxThread = self::$maxThread;//开启几个线程访问
        $curl->maxTry = self::$maxTry;//最大重试次数
        $response = []; //接收返回的参数
        $curl->onTask = function ($curl) use(&$response,$urls){
            static $i = 0;
            $urlCount = count($urls);
            if ($i >= $urlCount) {
                return;
            }

            // echo "\n我是属于其中的一个值 ---". $t[$i]."\r\n";
            // echo "\ncurrent tiems：{$i} \r\n";
            /** @var Curl $curl */
            $curl->add(
                array(
                    'opt' => array(
                        CURLOPT_URL => $urls[$i],
                    ),
                    'args'  =>  'This is user argument',
                ),
                function ($r, $args) use(&$response){
                    $info['status'] = "Request success for " . $r['info']['url'];
                    $info['args'] = $args;
                    $info['http_code'] = $r['info']['http_code'];
                    $info['body_size'] = strlen($r['body']) . ' bytes';
                    $info['content']  = $r['body'];
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