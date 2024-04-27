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

class StoreModel{

     private static $imageType ='jpg';//默认的头像

     public static $file_type = 'txt'; //存储为txt的格式文件

     private static $table_name = 'mc_book'; //对应的表信息

     public static $page_name = 'store_page_'; //保存的具体文件的前缀

     public static $detail_page = 'detail_page_';//保存对应的详情的分页信息


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
    * @note 统一处理小说信息的返回
    *
    * @param $info array 小说基本信息
    * @return string
    */
    public static function combineNovelHandle($info =[]){
        if(!$info){
            return [];
        }
        //处理小说作者标题，并替换前后空格
        $author = str_replace('作者：','',$info['author']);
        $author  = trimBlankSpace($author); //去重收尾空格
        //处理小说标题的收尾空格
        $title = trimBlankSpace($info['title']); //去除标题中首尾空格
        //处理时间戳问题并转换
        $third_update_time = trim($info['third_update_time']);
        $third_update_time = str_replace('更新时间：', '',$third_update_time);
        $third_update_time = strtotime($third_update_time);
        //小说状态
        $status = str_replace('小说状态：', '' , $info['status']);
        //小说分类
        $cate_name = str_replace('小说分类：','',$info['cate_name']);
        $info['title'] = $title;
        $info['author']   = $author;
        $info['status']  = $status;
        $info['cate_name'] = $cate_name;
        $info['third_update_time'] = $third_update_time;
        return $info;
    }


    /**
    * @note 获取生成不同的随机字符串，重复生成的不再使用
    *
    * @param $type string 类型
    * @return string
    */
    public static function createRandStr($type = 'proxy_website')
    {
        $code = '';
        global $redis_data;
        do {

            $code = getMixedChars(1,10);
            $redisOrderKey = $type . ':detail:' . $code;//先监测订单是否重复生成了
            #获取是否存在redis中的缓存
            $order_sn = $redis_data->get_redis($redisOrderKey);
            if (!$order_sn) {
                if (!$code) {
                    return false;
                }
                $redis_data->set_redis($redisOrderKey, $code); //生成订单后保存在缓存里，防止下次重复生成
                break;
            }
        } while (true); // 防止兑换码重复
        return $code;
    }


    /**
    * @note 获取海外代理IP
    *
    * @return   array
    */
     public static function getForeignProxy(){

        $rand_str = self::createRandStr();//随机生成API的数据-不会重复在库里
        // $rand_str  = 'wandou_proxy_'.date('YmdH');
        $proxy_data = [
            'ip'    =>  'gw.wandouapp.com',//IP地址
            'port'  =>  '1000', //端口
            'username'  =>  'g5jpdc6m_session-'.$rand_str.'_life-2_pid-0' ,//用户名
            'password'  =>  'fmkqrbh3', //密码
        ];

        return $proxy_data;
        // $proxy_info = webRequest('https://api.stormproxies.cn/web_v1/ip/get-ip-v3?app_key=6dd6f7b2ff738c58b27cd17c9c58fe01&pt=9&num=1&ep=&cc=US&state=&city=&life=2&protocol=1&format=json&lb=%5Cr%5Cn','GET');
        // $proxy_info = webRequest('https://api.wandouapp.com/?app_key=e890aa7191c00cd2f641060591c4f1d0&num=1&xy=3&type=2&lb=\r\n&nr=99&area_id=&isp=0&','GET');
        // $tdata = json_decode($proxy_info,true);
        // $proxy_data = [];
        // $proxy_ret = $tdata['data'][0] ??[];
        // if($proxy_ret){
        //     $results = explode(':',$proxy_ret);
        //     $proxy_data['ip'] = $results[0];
        //     $proxy_data['port'] = $results[1];
        // }
        return $proxy_ret;
     }

    /**
    * @note 通过swoole中的request请求去获取数据信息,可以批量去进行请求处理
    *
    * @param $urls array  链接地址
    * @param $tye intger 暂时还没用到 1：按照数据返回 2：返回状态信息
    * @return  object|unknow
    */
     public static function swooleRquest($urls,$type = 1){
        if(!$urls){
            return false;
        }
        //判断是否为单个传入
        if(!is_array($urls) || !isset($urls[0])){
            $urlArr[] = $urls;
            $urls = $urlArr;

        }
        $urls = array_filter($urls); //防止有空的url存在
        $items = [];
        $exec_start_time = microtime(true);
        $proxy_data = StoreModel::getForeignProxy();
        // $proxy_data = [];
        ///开启协程访问
        run(function () use(&$items,$urls,$proxy_data){
            $barrier = Barrier::make();
            $count = 0;
            $N = count($urls);
            $t_url =$urls;
            echo "swoole urls requests num (".$N.")\r\n";
            $http = new HttpRequest;
            foreach (range(0, $N-1) as $i) {
                $link = $urls[$i];
                //利用屏障的create来开启对应的配置信息
                Coroutine::create(function () use ($http,$barrier, &$count,$i,&$items,$urls ,$proxy_data) {
                    $response = $http->ua('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0')
                                     ->rawHeader('ddd:value4')
                                     ->proxy($proxy_data['ip'], $proxy_data['port'], 'socks5') //认证类型设置
                                     ->proxyAuth($proxy_data['username'],$proxy_data['password']) //认证账密
                                     ->get($urls[$i]);
                    //只要不是404页面的就直接返回，进行组装数据，其他的返回就不需要管了
                    $items[]=$response->body();
                    // var_dump("strlen =" . strlen($response->body()),"code = " . $response->getStatusCode());
                    $str ="async child-fork-process num = {$i} url = {$urls[$i]} \t strlen =" . strlen($response->body()) . "\t code = " . $response->getStatusCode();
                    echo $str ."\r\n";
                    //判断如果不是200的返回长什么样子
                    if($response->getStatusCode() != 200){
                        echo '<pre>';
                        var_dump($response->body());
                        echo '</pre>';
                        echo "\r\n";
                    }
                    System::sleep(2);
                    $count++;
                });
            }
            Barrier::wait($barrier);
            assert($count == $N);
        });
        echo "====================swoole worker finish \r\n";
        return $items;
    }

    /**
    * @note swoole请求重复调用请求，防止有空数据返回做特殊调用--根据情况来进行调整
    *
    * @param $content_arr array  请求的HTML数据
    * @param $goods_list array 原始请求的校验数据
    * @param $field_key string 配置需要从哪个里面获取url
    * @return unnkower
    */
    public static function swooleCallRequest($contents_arr=[],$goods_list=[],$filed_key= 'mobile_url'){
         if(!$contents_arr || !$goods_list){
            return [];
         }
        /***************判断是否有空的数据返回 start*****************************/
         $goods_list = array_values($goods_list);
         $errData  =  $sucData  = [];
         foreach($contents_arr as $key => $val){
            if(empty($val) || $val == ''){//空数据返回
                $errData[] =$goods_list[$key] ?? [];
            }else if(!preg_match('/id="content"/',$val) ){//断章处理，包含有502的未响应都会
                $errData[] =$goods_list[$key] ?? [];
            }else{//正常的数据返回
                $sucData[] = $val;
            }
         }
        /***************判断是否有空的数据返回 end*****************************/



         $repeat_data = $curl_contents1 =[];
         //数据为空的情况判断
         if(!empty($errData)){
            echo "有返回为空或者异常数据的数据请求，会重新去进行请求返回\r\n";
            $successNum = 0;
            $old_num = count($errData);
            $urls = array_column($errData, $filed_key); //进来先取出来
            while(true){
                //通过说swoole来完成并发请求，采用协程
                $curl_contents1 = StoreModel::swooleRquest($urls);
                $temp_url =[];//设置中间变量
                foreach($curl_contents1 as $tkey=> $tval){
                    if(empty($tval) || $tval == ''){//为空的情况
                        echo "获取数据为空，会重新抓取======================{$urls[$tkey]}\r\n";
                        $temp_url[] =$urls[$tkey];
                     }else if(!preg_match('/id="content"/',$tval)) {//是否存在502的情况
                        echo "有断章，会重新抓取======================{$urls[$tkey]}\r\n";
                        $temp_url[] =$urls[$tkey];
                     }else{//正常的返回
                        $repeat_data[] = $tval;
                        unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                        unset($curl_contents1[$tkey]);
                        $successNum++;
                    }
                }
                $urls = $temp_url; //起到指针的作用，每次只存失败的连接
                $urls = array_values($urls);//重置键值，方便查找
                $curl_contents1 =array_values($curl_contents1);//获取最新的数组
                if($old_num == $successNum){
                    echo "数据清洗完毕等待入库\r\n";
                    break;
                }
            }
        }
        echo "success-num =".count($sucData).PHP_EOL;
        echo "repeat_data-num = ".count($repeat_data).PHP_EOL;
        $returnList = array_merge($sucData , $repeat_data);
        echo "all-return-num = ".count($returnList).PHP_EOL;
        return $returnList;
    }


    /**
    * @note 处理转换的编码数据信息
    *
    * @param $data array 待需要处理的数据
    * @return  array|unkinow
    */
    public static function traverseEncoding($data = []){
        if(!$data){
            return false;
        }
        if(count($data) == count($data,1)){
            foreach($data as &$val){
                //转换数组对象
                $val = traditionalCovert($val);
            }
        }else{
            //处理二维数组的转换处理
            foreach($data as $key =>$val){
                 foreach($val as &$v){
                    $v = traditionalCovert($v);
                 }
                 $data[$key] = $val; //需要赋值一下，才能生效
            }
        }
        return $data;
    }
}
?>