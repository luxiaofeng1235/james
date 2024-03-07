<?
/*
 * 白名单同步到芝麻官网接口里
 *
 * Copyright (c) 2017 - zhenyi
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
$dirname = dirname(__FILE__);
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
$white_list_url ='https://wapi.proxy.linkudp.com/api/white_list?neek=2605210&appkey=fb0cbe4573722eb914f7ed8bf573c2e9';//白名单列表
$white_add_url ='https://wapi.proxy.linkudp.com/api/save_white?neek=2605210&appkey=fb0cbe4573722eb914f7ed8bf573c2e9&white=';//白名单添加接口
$white_del_url ='https://wapi.proxy.linkudp.com/api/del_white?neek=2605210&appkey=fb0cbe4573722eb914f7ed8bf573c2e9&white=';//白名单删除接口
$allow_ip = [
    '61.52.83.3',//本地调试的ip
    '103.36.91.35',//线上服务器IP
];
$white_list = webRequest($white_list_url,'GET');
if($white_list){
    $white_proxy_list = json_decode($white_list ,true);
    //获取列表成功
    if($white_proxy_list['code']  == 0){
        $proxy_list = $white_proxy_list['data']['lists'] ?? [];
        $ips = array_column($proxy_list,'mark_ip');
        $add_num = $white_proxy_list['data']['white'] ?? 0;//可添加白名单的数量
        //如果待添加的总数小于指定的次数后可以添加
        if(count($allow_ip) < $add_num){
            foreach($allow_ip as $val){
                //判断是否在白名单里
                if(!in_array($val , $ips)){
                    //拼接当前的url地址
                    $add_default_url =$white_add_url . $val;
                    //添加白名单接口
                    webRequest($white_add_url,'GET');
                    sleep(1);
                }
            }

            //这里面直接获取外网的IP地址判断是否在获取的白名单里，如果没有直接添加进去
            $remote_ip = getRemoteIp();
            if(!empty($remote_ip)){
                if( !in_array($remote_ip , $ips)){
                    $add_remote_url = $white_add_url . $remote_ip;
                    webRequest($add_remote_url,'GET');
                    echo "add-remote-ip success：".$remote_ip."\r\n";
                }
            }
        }else{
            echo "当天IP添加数量已超过".$add_num.'个'.PHP_EOL;
        }
    }
}
echo 'now-time：'.date('Y-m-d H:i:s') .PHP_EOL;
?>