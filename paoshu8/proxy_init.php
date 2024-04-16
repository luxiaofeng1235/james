<?php
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
$exec_start_time = microtime(true);
$set_minute = 8;
$limit =30;
$cache_key= 'zhima_multi_data';

$proxy_data = $redis_data->get_redis($cache_key);
if(!$proxy_data){
    do{
        $list = webRequest('http://pg.tiqu.letecs.com/getip_cm?neek=321a408a&num=50&type=2&pro=0&city=0&yys=0&port=2&pack=342905&ts=1&ys=1&cs=1&lb=1&sb=&pb=4&mr=2&regions=&code=qlwo1314is',true);
        $data = json_decode($list,true);
        $proxy = $data['data'] ?? [];
        $proxy_ret=[];
        if($proxy){
             foreach($proxy as $val){
               $time = strtotime($val['expire_time']) - time();
               $time = sprintf('%.2f',$time/60);
               if($time>= $set_minute){
                    $proxy_ret[]=$val;
               }
            }
            //如果匹配到就退出
            if(!empty($proxy_ret)){
                break;
            }
        }else{
            echo "未获取到代理配置信息重新获取\r\n";
        }
    }while(true);
    echo "新代理拉取：当前共匹配到大于{{$set_minute}}分钟的代理有 ".count($proxy_ret)."个\r\n";
    $list =array_slice($proxy_ret,0 ,$limit);
    echo "只取配置中 limit={$limit}中匹配到的总代理数有 ".count($list)."\r\n";
    $expire_time = $set_minute * 60; //设置dialing保存的成功数据
    $ret = $redis_data->set_redis($cache_key,json_encode($list),$expire_time);
    if($ret){
        echo "代理缓存成功,key={$cache_key} \r\n";
    }else{
        echo "代理缓存失败\r\n";
    }
}else{
    $data = json_decode($proxy_data ,true);
    echo "缓存里的代理还未过期，暂时可用 ,length = ".count($data)."\r\n";
    $ttl =$redis_data->ttl($cache_key); //获取缓存的可用时间
    $minutes = sprintf('%.2f',($ttl/60)); //剩余的分钟数
    echo "剩余可用缓存时间：".$minutes." minutes\r\n";
}
echo "finish\r\n";

?>