<?php

// $str= '你好,这里是utf8转gbk!';
// echo $str;
// echo "<br/>";
// $gbk_str= strToGBK($str); //将字符串的编码从UTF-8转到GB2312
// echo 'gbk编码：'.$gbk_str;
// echo "<br/>";
// $utf_str= strToUtf8($gbk_str);
// echo "gbk数据源:".$gbk_str."---utf-8编码后的：".$utf_str;
// exit;


//Java加签
$url = 'https://uv.newoffen.com/sysback/EsSearchGoods/decrypt';
$post_string = '24b06a9b44fcbe377a0e987f6e1a9b9db5337c43b6ddee1a1e25d40986650d34'; 

//加密后的：24b06a9b44fcbe377a0e987f6e1a9b9db5337c43b6ddee1a1e25d40986650d34
$aarr = webRequest($url,'POST',$post_string);
echo '<pre>';
print_R($aarr);
echo '</pre>';
exit;
// $param = json_encode($senData);
// echo '<pre>';
// print_R($param);
// echo '</pre>';
// exit;
echo '<pre>';
print_R($senData);
echo '</pre>';
exit;
exit;

 


function webRequest($url,$method,$params,$header = []){
        //初始化CURL句柄
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        // if(!empty($header)){
        //     curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
        // }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if(!empty($header)){//强制给发送一个header头部信息
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        //请求时间
        $timeout = 30;
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        switch ($method){
            case "GET" :
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                if(is_array($params)){
                    $params = json_encode($params,320);
                }
                //待发送的数据
                $senData = ['data'=>$params];
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS,$senData);
                break;
        }
        $data = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);//关闭cURL会话
        return $data;
    }
?>