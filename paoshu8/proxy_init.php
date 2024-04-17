<?php
set_time_limit(0);
ini_set('memory_limit','9000M');
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
$exec_start_time = microtime(true);
$set_minute = 8;
$limit =6;
//缓存的key
$redis_cache_key= Env::get('ZHIMA_QY_REDIS_KEY');
echo "cache_key：".$redis_cache_key."\r\n";
// $redis_data->del_redis($redis_cache_key);
// echo 3;die;
$proxy_data = $redis_data->get_redis($redis_cache_key);
if(!$proxy_data){
    do{
        sleep(1); //防止接口频繁请求
        $list = webRequest('http://webapi.http.zhimacangku.com/getip_3h?neek=321a408a&num=6&type=2&time=3&pro=0&city=0&yys=0&port=2&pack=0&ts=1&ys=1&cs=1&lb=1&sb=&pb=4&mr=1&regions=120000,430000,420000,310000,110000,530000,440000,610000,620000,340000,140000,150000,350000,460000,630000,500000,360000,210000,220000,370000,510000,520000,410000,230000','GET');
        $data = json_decode($list,true);
        $proxy = $data['data'] ?? [];
        $proxy_ret=[];
        if($proxy){
            $t= [];
             foreach($proxy as $val){
                $expire_time = isset($val['expireTime']) ? $val['expireTime'] : $val['expire_time'];
                $t[]=strtotime($expire_time);
                // echo $expire_time."\r\n";
                $proxy_ret[]=$val;
               // $time = strtotime($val['expire_time']) - time();
               // $time = sprintf('%.2f',$time/60);
               // if($time>= $set_minute){
               //      $proxy_ret[]=$val;
               // }
            }
            $diff_time = min($t);
            echo "proxy-max-expire_time = ".date('Y-m-d H:i:s',$diff_time).PHP_EOL;
            //如果匹配到就退出
            if(!empty($proxy_ret)){
                break;
            }
        }else{
            echo "未获取到代理配置信息重新获取\r\n";
        }
    }while(true);
    echo "新代理拉取：当前共匹配到的代理有 ".count($proxy_ret)."个\r\n";
    $list =array_slice($proxy_ret,0 ,$limit);
    echo "只取配置中 limit={$limit}中匹配到的总代理数有 ".count($list)."\r\n";
    $expire_time = $diff_time - time(); //设置dialing保存的成功数据
    $ret = $redis_data->set_redis($redis_cache_key,json_encode($list),$expire_time);
    if($ret){
        echo "代理缓存成功,key={$redis_cache_key} \r\n";
    }else{
        echo "代理缓存失败\r\n";
    }
}else{
    //代理不为空的情况
    $data = json_decode($proxy_data ,true);
    echo "缓存里的代理还未过期，暂时可用 ,length = ".count($data)."\r\n";
    $ttl =$redis_data->ttl($redis_cache_key); //获取缓存的可用时间
    $minutes = sprintf('%.2f',($ttl/60)); //剩余的分钟数
    echo "剩余可用缓存时间：".$minutes." minutes\r\n";
}
echo "finish\r\n";
echo "now-time：".date('Y-m-d H:i:s')."\r\n";

?>