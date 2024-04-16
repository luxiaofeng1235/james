<?php
set_time_limit(0);
ini_set('memory_limit','9000M');
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
$exec_start_time = microtime(true);
$set_minute = 8;
$limit =30;
//缓存的key
$redis_cache_key= Env::get('ZHIMA_QY_REDIS_KEY');
echo "cache_key：".$redis_cache_key."\r\n";
$proxy_data = $redis_data->get_redis($redis_cache_key);
if(!$proxy_data){
    do{
        sleep(1); //防止接口频繁请求
        $list = webRequest('https://bapi.51daili.com/getapi2?linePoolIndex=-1&packid=2&time=13&qty=10&port=2&format=json&field=ipport,expiretime,regioncode,isptype&dt=1&usertype=17&uid=43558','GET');
        $data = json_decode($list,true);
        $proxy = $data['data'] ?? [];
        $proxy_ret=[];
        if($proxy){
            $t= [];
             foreach($proxy as $val){
                $expire_time = isset($val['expireTime']) ? $val['expireTime'] : $val['expire_time'];
                $t[]=strtotime($expire_time);
                $proxy_ret[]=$val;
               // $time = strtotime($val['expire_time']) - time();
               // $time = sprintf('%.2f',$time/60);
               // if($time>= $set_minute){
               //      $proxy_ret[]=$val;
               // }
            }
            $diff_time = min($t);
            echo "expire_time = ".date('Y-m-d H:i:s',$diff_time).PHP_EOL;
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