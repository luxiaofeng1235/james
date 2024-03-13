<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :process_url.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:删除还在redis缓存中已经过期的key
// ///////////////////////////////////////////////////
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

//加载已经缓存的key
$redis_key = Env::get('ZHIMA_REDIS_KEY');
echo "key={$redis_key}\r\n";
$proxy_data = $redis_data->get_redis($redis_key);
if($proxy_data){
    $info = json_decode($proxy_data,true);

    if(!empty($info)){
        $dead_time = $info['expire_time'];//过期时间
        if(time() > strtotime($dead_time)){
            echo "缓存中的代理已过期：".$info['ip'].':'.$info['port']." 执行删除操作\r\n";
            $redis_data->del_redis($redis_key);//删除已过期的缓存
        }else{
            echo "no expire\r\n";
        }
    }
}
?>