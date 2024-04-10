<?php
class curl_pic_multi{

  private static  $url_list=array();

  private static $curl_setopt=array(

      'CURLOPT_RETURNTRANSFER' => 1,//结果返回给变量
      'CURLOPT_HEADER' => false,//是否需要返回HTTP头
      'CURLOPT_NOBODY' => 0,//是否需要返回的内容
      'CURLOPT_FOLLOWLOCATION' => 0,//自动跟踪
      'CURLOPT_TIMEOUT' => 120//超时时间(s)
  );

  //设置连接的超时时间
  private static $connection_timeout = 20;

  function __construct($seconds=30){
      set_time_limit($seconds);
  }

  /*

   * 设置网址

   * @list 数组

   */

  public function setUrlList($list=array()){
      $this->url_list=$list;

  }

  /*

   * 设置参数

   * @cutPot array

   */

  public function setOpt($cutPot){

      $this->curl_setopt=$cutPot+$this->curl_setopt;

  }


  /**
  * @note 随机IP
  *
  * @param
  * @return
  */
public static function Rand_IP(){

    $ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
    $ip3id= round(rand(600000, 2550000) / 10000);
    $ip4id= round(rand(600000, 2550000) / 10000);
    //下面是第二种方法，在以下数据中随机抽取
    $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
    $randarr= mt_rand(0,count($arr_1)-1);
    $ip1id = $arr_1[$randarr];
    return $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
}

/**
* @note 随机refer地址
*
* @return string
*/
public static function Rand_refer(){
  $refer_path = ROOT . 'config/refer.txt';
  $url = [];
  //从配置中读取自定义的refer地址
  if (file_exists($refer_path)) {
      $urls = file($refer_path);
      foreach($urls as &$val){
          $val = str_replace("\r\n",'',$val);
      }
  }
    //每次curl请求随机一个
    $randarr= mt_rand(0,count($urls)-1);
    $get_url = $urls[$randarr];
    if(empty($get_url)){
        $get_url = $urls[0]; //如果为空默认取一个
    }
    return $get_url;
  }

    //获取当前的图片信息
    //type = 1 原生系统代理  2 章节统计 3.数据列表为空统计
    public  static function Curl_http($array,$type=1,$timeout='15'){
      if(!$array)
        return false;
      $res = array();
      $proxy_data = [];
      // if($type == 1){//使用原生系统的代理请求
      //     $proxy_data = getProxyInfo();
      // }else if ($type == 2){//移动端的代理请求
      //     $proxy_data = getMobileProxy();
      // }else if($type ==3){//处理列表为空申请的带
      //     $proxy_data = getMobileEmptyProxy();
      // }else if($type ==4){//处理基础章节的基础配置类
      //     $proxy_data = getZhimaProxy();
      // }else if($type ==5){
      //     $proxy_data = getImgProxy();//获取下载图片使用的代理
      // }
      $proxy_info = webRequest('http://api.yilian.top/v2/proxy/proxies?token=TnFOk8GFECExUxpkdbFrEklkAx7Ubhq4&pull_num=1&format=json&protocol=3&separator=1','GET');


      $tdata = json_decode($proxy_info,true);
      $proxy_data = $tdata['data'][0] ??[];
      //转换字段
      $proxy_data = combineProxyParam($proxy_data);
      // //判断代理IP是否失效，防止数据异常
      if(!$proxy_data){
          echo '【调用位置：curl_pic_multi类】 当前代理IP已经过期了，重新获取吧 --------！'.PHP_EOL;
          NovelModel::killMasterProcess(); //结束当前进程
          exit(1);
      }
    //伪造referer地址
    $referer = self::Rand_refer();
    //伪造客户端IP进行访问，
    $headerIp = array(
      'CLIENT-IP:'.self::Rand_IP(),
      'X-FORWARDED-FOR:'.self::Rand_IP(),
    );
      // if(!$proxy_data)
      //   return [];
      $mh = curl_multi_init();//创建多个curl语柄
      foreach($array as $k=>$url){
          $conn[$k]=curl_init($url);//初始化
          curl_setopt($conn[$k], CURLOPT_TIMEOUT, $timeout);//设置超时时间
        if($proxy_data){
        //是否开启代理
            curl_setopt($conn[$k], CURLOPT_PROXY, $proxy_data['ip']);
            curl_setopt($conn[$k],CURLOPT_PROXYPORT,$proxy_data['port']);
            if(isset($proxy_data['username']) && isset($proxy_data['password'])){
                  curl_setopt($conn[$k],CURLOPT_PROXYUSERPWD,$proxy_data['username'].':'.$proxy_data['password']);
              }
              curl_setopt($conn[$k], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
              curl_setopt($conn[$k],CURLOPT_PROXYAUTH,CURLAUTH_BASIC);
        }
         // 设置连接超时时间，单位是秒
          curl_setopt($conn[$k], CURLOPT_CONNECTTIMEOUT, self::$connection_timeout);
          curl_setopt($conn[$k], CURLOPT_HTTPHEADER, $headerIp);//追踪返回302状态码，继续抓取
          curl_setopt($conn[$k], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0');//设置发送的user-agent信息
          curl_setopt($conn[$k],CURLOPT_REFERER,$referer); //启用referer伪造url,模拟来路
          curl_setopt($conn[$k],CURLOPT_ENCODING,'gzip');//启用解压缩
          // 超过1024字节解决方法
          curl_setopt($conn[$k],CURLOPT_HTTPHEADER, array("Expect:")); //增加配置完整接收数据配置buffer的大小
          curl_setopt($conn[$k],CURLOPT_BUFFERSIZE,320000);//设置调整收包大小阈值
          curl_setopt($conn[$k], CURLOPT_MAX_RECV_SPEED_LARGE, 500240); // 设置最大尺寸为10KB
          curl_setopt($conn[$k],CURLOPT_HTTPPROXYTUNNEL,0);//启用时会设置HTTP的method为GET
          curl_setopt($conn[$k], CURLOPT_SSL_VERIFYPEER, false);//屏蔽过滤ssl的连接
          curl_setopt($conn[$k], CURLOPT_SSL_VERIFYHOST, false);//屏蔽ssl的主机
          curl_setopt($conn[$k], CURLOPT_MAXREDIRS, 7);//HTTp定向级别 ，7最高
          curl_setopt($conn[$k], CURLOPT_HEADER, false);//这里不要header，加块效率
          curl_setopt($conn[$k], CURLOPT_RETURNTRANSFER,1);//要求结果为字符串且输出到屏幕上
          curl_setopt($conn[$k], CURLOPT_HTTPGET, true);


          //tcp设置相关--主要设置Keep-alive心跳
          curl_setopt($conn[$k],CURLOPT_TCP_KEEPALIVE,1);   // 开启
          curl_setopt($conn[$k],CURLOPT_TCP_KEEPIDLE,3);   // 空闲10秒问一次
          curl_setopt($conn[$k],CURLOPT_TCP_KEEPINTVL,3);  // 每10秒问一次
          curl_setopt($conn[$k], CURLOPT_TCP_NODELAY, 1);//TRUE 时禁用 TCP 的 Nagle 算法，就是减少网络上的小包数量。
          curl_setopt($conn[$k], CURLOPT_NOSIGNAL, 1); //TRUE 时忽略所有的 cURL 传递给 PHP 进行的信号。在 SAPI 多线程传输时此项被默认启用，所以超时选项仍能使用。


          //设置版本号和启用ipv4
          curl_setopt($conn[$k], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);    // 强制使用 HTTP/1.0
          curl_setopt($conn[$k], CURLOPT_FOLLOWLOCATION, 1);// 302 redirect
          curl_setopt($conn[$k],CURLOPT_MAXREDIRS, 5) ; //定向的最大数量，这个选项是和CURLOPT_FOLLOWLOCATION一起用的
          curl_setopt($conn[$k], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPv4

          curl_multi_add_handle ($mh,$conn[$k]);
      }
       //防止死循环耗死cpu 这段是根据网上的写法
          do {
              $mrc = curl_multi_exec($mh,$active);//当无数据，active=true
          } while ($mrc == CURLM_CALL_MULTI_PERFORM);//当正在接受数据时
          while ($active and $mrc == CURLM_OK) {//当无数据时或请求暂停时，active=true
              if (curl_multi_select($mh) != -1) {
                  do {
                      $mrc = curl_multi_exec($mh, $active);
                  } while ($mrc == CURLM_CALL_MULTI_PERFORM);
              }
          }

      foreach ($array as $k => $url) {
            if(!curl_errno($conn[$k])){
             $data[$k]=curl_multi_getcontent($conn[$k]);//数据转换为array
             $header[$k]=curl_getinfo($conn[$k]);//返回http头信息
             curl_close($conn[$k]);//关闭语柄
             @curl_multi_remove_handle($mh  , $conn[$k]);   //释放资源
            }else{
             unset($k,$url);
            }
          }
          curl_multi_close($mh);

          return $data;

   }
}
?>