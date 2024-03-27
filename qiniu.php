<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,奥芬
// 日 期：2020年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@offengroup.com
// 文件名 :qiuniu.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:七牛鉴黄模块开发
// ///////////////////////////////////////////////////
ini_set('memory_limit','1000M');
set_time_limit(500);
//定义项目目录

$dirname =str_replace("\\",'/',dirname(__FILE__)).'/library/' ;
define('APPPATH',$dirname);

require_once(APPPATH."common.php");
require_once(APPPATH . "autoload.php");
// 引入鉴权类
use Qiniu\Auth;
use Qiniu\Http\Client;


##测试数据
$list = array(
    'app_content' =>array(
        'username' =>111,
        'password'  => 3334
    ),
    'send_message' =>33222,
    'status' =>array(
        'plist'=>1,
        'array'=>array(
            'http',
            'arr'
        )
    )
);

//解析数据格式
$mobile_data = array2string($list);

if($mobile_data){
     //进行转换
    $result = string2array(stripslashes($mobile_data));
    $aids = is_array($result) && !empty($result) ? $result['status'] : array();
    echo '<pre>';
    print_R($aids);
    echo '</pre>';

}
##测试
$img_data[] = array(
    'data'=>
        array(
            'uri'=>"http://big5.taiwan.cn/xwzx/bwkx/201401/W020140103537538775945.jpg",
        ),
);
/*
 array(
 https://www.baidu.com/img/bd_logo1.png
            'uri'=>"http://big5.taiwan.cn/xwzx/bwkx/201401/W020140103537538775945.jpg",
        ),
*/
$images_str = json_encode($img_data);
//返回七牛鉴黄结果
$response = post_jh($img_data);
echo '<pre>';
print_R($response);
echo '</pre>';
exit;


    //自定义方法，鉴黄请求
    function post_jh($img_all=array()){
        //   $this->load->helper('string_handle');
        $return = array('status'=>'error');
        if(is_array($img_all) && count($img_all)){
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = 'yEacIJa59EK8ar6UMlOkRft39W91qoJmmHzhw9fC';//'PW6t6QF4df4TLcrJYMEPAlrvnlbrYb5E-a6pE7qF';
            $secretKey = 'dtPS1DPJPFzh523Kp9ItFSqmSFP7CqQ7-CDyttNo';//'VyYy18APKl4qMZ37phH09vWRE4z4gaL-Cyj95FgW';

            $auth = new Auth($accessKey, $secretKey); // 构建鉴权对象

            $post_url = "http://argus.atlab.ai/v1/pulp";//七牛请求地址

            $field_list = array(); //一个分享的总请求列表，因为七牛需要单图片请求
            $img_str = array();
            foreach ($img_all as $key => $value) {
                $images_str = json_encode($value);
                $sign = $auth->authorization($post_url,$img_str,'application/json');//签名
                $headers = authorizationV2($post_url, 'POST', $images_str, 'application/json', $auth);//提交头部
                $headers['Content-Type'] = 'application/json';
                $response = Client::post($post_url,$images_str,$headers);//响应数据
                $response = json_encode($response);
                $post_decode = json_decode($response,true);
                $body = json_decode($post_decode['body'],true);
                $body['result']['name'] = $value['data']['uri'];
                $field_list [] = $body;
            }


            //解析请求列表
            $label = 2;//类别：0色情1性感2正常
            $review = 0;//是否需复审：0否，1是
            $score = 1;//概率

            if(is_array($field_list) && count($field_list)){
                $image_question = array();

                foreach ($field_list as $key=>$val){
                    if(in_array($val['result']['label'],array(0,1)) || (in_array($val['result']['label'],array(2)) && $val['result']['score'] < 0.8)){
                        $review = 1;
                    }
                    if($label > $val['result']['label']){
                        $label = $val['result']['label'];
                    }
                    if($score > $val['result']['score']){
                        $score = $val['result']['score'];
                    }
                    $image_question[] = $val['result']['name'];

                }
                $image_str = array2string($image_question);
                $review_time = date('Y-m-d H:i:s');
                //组合数据返回
                $return=array(
                    'status'=>'success',
                    'rate'=>$score,
                    'label'=>$label,
                    'review_time'=>$review_time,
                    'image_str'=>$image_str,
                    'review'=>$review,
                    'body'=>json_encode($field_list)
                );
            }
        }
        return $return;
    }

    /**
     *新的加密token规则
     */
     function authorizationV2($url, $method, $body = null, $contentType = null, $auth)
    {
        $urlItems = parse_url($url);
        $host = $urlItems['host'];

        if (isset($urlItems['port'])) {
            $port = $urlItems['port'];
        } else {
            $port = '';
        }

        $path = $urlItems['path'];
        if (isset($urlItems['query'])) {
            $query = $urlItems['query'];
        } else {
            $query = '';
        }

        //write request uri
        $toSignStr = $method . ' ' . $path;
        if (!empty($query)) {
            $toSignStr .= '?' . $query;
        }

        //write host and port
        $toSignStr .= "\nHost: " . $host;
        if (!empty($port)) {
            $toSignStr .= ":" . $port;
        }

        if (!empty($contentType)) {
            $toSignStr .= "\nContent-Type: " . $contentType;
        }

        $toSignStr .= "\n\n";

        if (!empty($body)) {
            $toSignStr .= $body;
        }

        $sign = $auth->sign($toSignStr);
        $auth = 'Qiniu ' . $sign;
        return array('Authorization' => $auth);
    }


?>