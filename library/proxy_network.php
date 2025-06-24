<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年05月18日
// 作　者：卢晓峰
// E-mail :xiaofeng.200@163.com
// 文件名 :proxy_network.php
// 创建时间:2024-05-18 20:55:04
// 编 码：UTF-8
// 摘 要:请求网络的代理IP配置

class ProxyNetwork{

    private static $key ='05BAA873';//秘钥
    public static $redis_key ='qf_proxy';//缓存的key

    //用法：ProxyNetwork::getEnabledProxy() //获取当前的可用代理信息

    /**
    * @note 获取最新的代理IP
    *
    * @return array
    */
    public static function getEnabledProxy(){
        //有效时长60分钟
        global $redis_data;
        $proxy_data = $redis_data->get_redis(self::$redis_key);
        $data = [];
        if(!$proxy_data){
            $url = 'https://exclusive.proxy.qg.net/replace?key='.self::$key.'&num=1&area=&isp=0&format=json&distinct=true&keep_alive=480';
            $info =webRequest($url ,"GET",[],[]);
            $proxy_info =json_decode($info,true);
            //缓存五分钟方防止过期
            $redis_data->set_redis(self::$redis_key,json_encode($proxy_info),300);
            $data = $proxy_info['data']['ips'][0] ?? [];
            // return $proxy_info;
        }else{
            $proxy_info = json_decode($proxy_data,true);
            if($proxy_info['code'] == 'SUCCESS'){
                $data = $proxy_info['data']['ips'][0] ?? [];
            }else{
                    return [];
            }
        }
        //判断是否为空
        if(!empty($data)){
            list($ip,$port) = explode(':',$data['server']);
            $data['ip'] = $ip ?? '';
            $data['port'] = $port ?? '';
        }
        return $data;
    }

    /**
    * @note 查询搭理IP的使用情况
    *
    * @return array
    */
    public static function getProxyQues(){
        $url = 'https://exclusive.proxy.qg.net/query?key='.self::$key;
        $info = webRequest($url,"GET",[],[]);//获取当前代理IP的状态
        $data = json_decode($info,true);
        $return_arr =[];
        if($data['code'] == 'SUCCESS'){
            $task_arr = $data['data']['tasks'][0] ?? [];
            if(isset($task_arr['ips'])){
                $return_arr = $task_arr['ips'][0] ?? [];
                return $return_arr;
            }
        }
        return $return_arr;
    }

    /**
    * @note 移除当前的IP信息,根据里面的时间来判断是否过期
    *
    * @return
    */
    public static function removeCurrnetProxy(){
        global $redis_data;
        $proxy = self::getProxyQues();
        if(!empty($proxy)){
            $now_time = time();
            $deadline = $proxy['deadline'] ;
            //如已经过期了，就把redis中的数据一并删除
            if($now_time > strtotime($deadline)){
                $redis_data->del_redis(self::$redis_key);
            }
        }
    }
}
?>