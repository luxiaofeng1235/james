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
    * @note 从特定的url中获取对应的数据信息
    * @return array
    *
    */
   public static function novelChapterList($html){
      if(!$html){
        return '';
      }
      $contents = '';
      $chapter_list = [];
      preg_match('/<div class=\"info-chapters flex flex-wrap\">.*?<\/div>/ism',$html,$list);
      $contents = $list[0] ?? '';
      if($contents){
            $link_reg = '/<a href=\"([^"]+)\".*?>/'; //匹配A连接
            $text_reg ='/<a href=\"[^\"]*\"[^>]*>(.*?)<\/a>/si';//匹配链接里的文本
            //处理中间的换行字符,不然匹配会出问题
            preg_match_all($link_reg,$contents,$link_href);//匹配链接
            preg_match_all($text_reg,$contents,$link_text);//匹配文本;
            $len = count($link_href[1]);

            //回调函数处理去除换行
            $link_text = array_map('trimBlankLine',$link_text);
            for ($i=0; $i <$len ; $i++) {
               $chapter_list[] =[
                  'link_name' => $link_text[1][$i] ?? '',
                  'link_url'  => $link_href[1][$i] ?? '',
               ];
            }
        }
        //繁简体转换
        $chapter_list = StoreModel::traverseEncoding($chapter_list);
        return $chapter_list;
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
        if(isset($info['third_update_time'])){
            $third_update_time = trim($info['third_update_time']);
            $third_update_time = str_replace('更新时间：', '',$third_update_time);
            $third_update_time = strtotime($third_update_time);
            $info['third_update_time'] = $third_update_time;
        }
        //小说状态
        if(isset($info['status'])){
            //小说状态
            $status = str_replace('小说状态：', '' , $info['status']);
            $info['status']  = $status;
        }
        //小说分类
        if(isset($info['cate_name'])){
             $cate_name = str_replace('小说分类：','',$info['cate_name']);
               $info['cate_name'] = $cate_name;
        }
        $info['title'] = $title;
        $info['author']   = $author;
        $info['source']  = NovelModel::getSourceUrl($info['story_link']); //获取网站来源
        return $info;
    }


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
    * @return array|unknow
    */
     private static function getForeignProxy(){
        $rand_str = self::createRandString();//随机生成API的数据-不会重复在库里
        $proxy_data = [
            'ip'    =>  'gw.wandouapp.com',//IP地址
            'port'  =>  '1000', //端口
            'username'  =>  "hw1oll1y_session-{$rand_str}_life-5_pid-0" ,//用户名-让代理存活5分钟
            'password'  =>  'wd97zgis', //密码
        ];
        // $url = 'https://api.wandouapp.com/?app_key=e890aa7191c00cd2f641060591c4f1d0&num=1&xy=3&type=2&lb=\r\n&nr=99&area_id=&isp=0&';
        // $res = webRequest($url,'GET');
        // $data = json_decode($res,true);
        // $proxy_data = $data['data'][0] ?? '';       
        return $proxy_data;
    }


    /**
    * @note 下载文件/图片等等
    *
    * @param $urls array  链接地址
    * @return  object|unknow
    */
     public static function downloadLocalFile($url){
        if(!$url){
            return false;
        }
        $http = HttpRequest::newSession();
        $http->download(__DIR__ . '/123456.*', $url);
        return 1;
     }


    /**
    * @note 通过swoole中的request请求去获取数据信息,可以批量去进行请求处理
    *
    * @param $urls array  请求链接地址
    * @param $method 请求方式 get post
    * @return  object|unknow
    */
     public static function swooleRquest($urls=[],$method='get'){
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
        $exec_start_time = microtime(true);
        $proxy_data = StoreModel::getForeignProxy();
        // $proxy_data = [];
        ///开启协程访问
        // echo 3;exit;
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
                //利用屏障的create来开启对应的配置信息

                #苹果的UA-设置后访问具体的内容页面
                //Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1

                #360的浏览器
                #Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36
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
                                    ->rawHeader($cookie)
                                    // ->proxy($proxy_data['ip'], $proxy_data['port'], 'socks5') //认证类型设置
                                    // ->proxyAuth($proxy_data['username'],$proxy_data['password']) //认证账密
                                    ->get($urls[$i]);
                        }else{
                            $response = $http
                                    ->ua($user_agent)
                                    ->rawHeader($cookie)
                                    ->post($urls[$i]);
                        }

                    $hostData = parse_url($urls[$i]??'');
                    //只要不是404页面的就直接返回，进行组装数据，其他的返回就不需要管了
                    //判断当前是否存在这些源并转换编码
                    $charaset_urls = [
                            'xuges',
                            'bqg24',
                            'siluke520',
                            'bqwxg8',
                    ];
                    $soruce_ref = NovelModel::getSourceUrl($urls[$i]);
                    if(in_array($soruce_ref, $charaset_urls)){
                        //特殊的网站需要特殊处理下
                        $items[$hostData['path']]=$response->body('gb2312','UTF-8');
                    }else{
                        $items[$hostData['path']]=$response->body();
                    }
                    // var_dump("strlen =" . strlen($response->body()),"code = " . $response->getStatusCode());
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




    /**
    * @note 获取404的页面地址信息，防止跳不出去
    *
    * @return unknow
    */
    public static function getNofFoundStr(){
                $str = '{
            "type": "https://tools.ietf.org/html/rfc9110#section-15.5.5",
            "title": "Not Found",
            "status": 404,
            "traceId": "00-2eeb3f62c0bf6d8487f8d4b236a96d79-ed98f8bf5769dc23-00"
        }';
            return $str;
    }

    /**
    * @note swoole请求重复调用请求，防止有空数据返回做特殊调用--根据情况来进行调整
    *
    * @param $content_arr array  请求的HTML数据
    * @param $goods_list array 原始请求的校验数据
    * @param $referer_url string 回调地址，用于判断用什么页面的元素来判断
    * @param $field_key string 配置需要从哪个字段里面获取url
    * @param $type 1 :判断章节内容页 2：判断列表页(可以根据需要进行调整)
    * @return unknow
    */
    public static function swooleCallRequest($contents_arr=[],$goods_list=[],$referer_url='',$method='get',$field_key= 'mobile_url',$type = 1){
        if(!$contents_arr || !$goods_list){
            return [];
        }
        // echo '<pre>';
        // var_dump(count($contents_arr));
        // echo '<pre>';
        // var_dump($goods_list);
        // echo '</pre>';
        // exit;
        // echo '</pre>';
        // exit;
        //获取网站的来源
        $source_url = NovelModel::getSourceUrl($referer_url);
        //id="list"
         //type = 2的时候，一般都是可以自动配置的，根据需要自行调整
        if($type!=1){
            $content_reg =  '/class="list-out"/';//列表判断
        }else{
            //导入校验的标签
            $content_reg = CommonService::importCollectTagId($referer_url);
        }
        /***************判断是否有空的数据返回 start*****************************/
        $errData  =  $sucData  = [];
        foreach($contents_arr as $key => $val){
            if(strstr($val, 'DMCA和内容违规原因')){//404的话直接跳出去不然会死循环
                 $sucData[$key] ='页面不存在，稍后更新后查看';
            }else{
                if(empty($val) || $val == ''){//空数据返回
                    $errData[] =$goods_list[$key] ?? [];
                }else if(!preg_match($content_reg,$val) ){//断章处理，包含有502的未响应都会
                    $errData[] =$goods_list[$key] ?? [];
                }else{//正常的数据返回
                     $sucData[$key] = $val;
                }
            }
        }
        /***************判断是否有空的数据返回 end*****************************/

        $repeat_data = $curl_contents1 =[];
         //数据为空的情况判断
        if(!empty($errData)){
            echo "有返回为空或者异常数据的数据请求，会重新去进行请求返回111\r\n";
            $successNum = 0;

            $old_num = count($errData);
            $urls = array_column($errData, $field_key); //进来先取出来,根据上面的取出来
            echo "待需要重新抓取的处理的url数据:\r\n";
            echo '<pre>';
            var_dump($urls);
            echo '</pre>';
            while(true){
                //通过说swoole来完成并发请求，采用协程
                $curl_contents1 = StoreModel::swooleRquest($urls,$method);
                $temp_url =[];//设置中间变量
                if(!$curl_contents1){
                    $curl_contents1 = [];
                }
                $len = 50 ;//至少要保持页面有数据，防止数据为空
                foreach($curl_contents1 as $tkey=> $tval){
                    if(empty($tval) || $tval == ''){//为空的情况
                        //当前需要抓取的url连接诶
                        $t_url = $goods_list[$tkey][$field_key] ?? '';
                        echo "获取数据为空，会重新抓取====================== {$t_url}\r\n";
                        $temp_url[] = $t_url; //取出来当前的连接
                    }else if( !preg_match($content_reg,$tval)) {//是否存在502的情况
                        //当前需要去抓取的url
                        $s_url = $goods_list[$tkey][$field_key] ?? '';
                        echo "有断章，会重新抓取====================== {$s_url}\r\n";
                        $temp_url[] =$s_url; //直接取出来当前的连接
                    }else{//正常的返回
                        $repeat_data[$tkey] = $tval;
                        unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                        unset($curl_contents1[$tkey]);
                        $successNum++;
                    }
                }
                $urls = $temp_url; //起到指针的作用，每次只存失败的连接
                $urls = array_values($urls);//重置键值，方便查找
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
        if(count($sucData) +count($repeat_data) != count($returnList) ){
            echo "数组返回数量不对，请检车代码逻辑";
            exit();
        }
        return $returnList;
    }


    /**
    * @note 处理转换的繁简体转换
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