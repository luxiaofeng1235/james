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
    '103.36.91.35',//线上服务器IP
    '39.148.224.50',//国贸360的IP
];

$white_list = webRequest($white_list_url,'GET');
sleep(5); //设置间隔时间，由于接口有限制不能并行处理
if($white_list){
    $white_proxy_list = json_decode($white_list ,true);
    //获取列表成功
    if($white_proxy_list['code']  == 0){
        $proxy_list = $white_proxy_list['data']['lists'] ?? [];
        $ips = array_column($proxy_list,'mark_ip');
        $add_num = $white_proxy_list['data']['white'] ?? 0;//可添加白名单的数量

        //如果待添加的总数小于指定的次数后可以添加
        if(count($proxy_list) < $add_num){
            foreach($allow_ip as $val){
                //判断是否在白名单里
                if(!in_array($val , $ips)){
                    //拼接当前的url地址
                    $add_default_url =$white_add_url . $val;
                    //添加白名单接口
                    webRequest($white_add_url,'GET');
                    echo "add-list-ip success：".$val."\r\n";
                    sleep(5);
                }
            }
            //这里面直接获取外网的IP地址判断是否在获取的白名单里，如果没有直接添加进去
            $remote_ip = getRemoteIp();
            if(!empty($remote_ip)){
                if( !in_array($remote_ip , $ips)){
                    $add_remote_url = $white_add_url . $remote_ip;
                    $res = webRequest($add_remote_url,'GET');
                    echo '<pre>';
                    var_dump($res);
                    echo '</pre>';
                    sleep(5);
                    echo "add-remote-ip success：".$remote_ip."\r\n";
                }
            }
        }else{
            //如果当前IP有五个以上，只保留这两个IP其他的都干掉
            //需要重新获取一次客户端IP，防止把服务器里的IP也给删除掉
            $remote_ip = getRemoteIp();
            $all_ip = array_merge_recursive($ips,[$remote_ip]); //实际的保留的IP地址为：允许设置的IP+本机的IP地址
            $allow_ip_info = array_unique($all_ip);
            $i = 0;
            foreach($allow_ip_info as $current_ip){
                if(!in_array($current_ip,$allow_ip)){
                    $i++;
                    //需要删除的接口
                    $del_ip_url =$del_ip_url = $white_del_url.$current_ip;
                    //执行删除逻辑的IP，释放出来对应的IP
                    $ret= webRequest($del_ip_url,'GET');
                    echo '<pre>';
                    var_dump($ret);
                    echo '</pre>';
                    echo "delete-remote-ip success：".$current_ip."\r\n";
                    sleep(5);
                }
            }
            echo "当天IP添加数量已超过".$add_num.'个，已删除释放出来的IP总数:'.$i."个\r\n";
        }
    }
}
echo 'now-time：'.date('Y-m-d H:i:s') .PHP_EOL;
?>