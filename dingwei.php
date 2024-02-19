<?php
phpinfo();
exit;
$lat = '34.674727';
$lng = '112.50062';

//40.11201,116.4004 石家庄

//34.674727,112.50062  洛阳君河湾
$aaa=get_geocoder_location($lng,$lat);
echo '<pre>';
print_R($aaa);
echo '</pre>';
exit;

/**
 * [get_geocoder_location 腾讯地图逆地址解析]
 * @param  [float] $lng [description]
 * @param  [float] $lat [description]
 * @return [array]      [description]
 * @document https://lbs.qq.com/service/webService/webServiceGuide/webServiceGcoder
 */
function get_geocoder_location($lng, $lat) {

    $key = 'N3LBZ-J3TK3-V743G-3IMY2-YHO4V-45F4K'; //签名校验的KEY
    $sk = '5BZOKOuolG67ZPeDulMuD5lAV92Trp5D'; //sk参数
    $map_ak = 'AK';

    $param = 'key='.$key.'&location='.$lat.','.$lng;
    /*
    签名计算(sig)：

      请求路径+”?”+请求参数+SK进行拼接，并计算拼接后字符串md5值（字符必须为小写），即为签名(sig)：

      要求：请求参数必须是未进行任何编码（如urlencode）的原始数据

     */
     //md5("/ws/geocoder/v1?key=5Q5BZ-5EVWJ-SN5F3-*****&location=28.7033487,115.8660847SWvT26ypwq5Nwb5RvS8cLi6NSoH8HlJX")
      // echo '/ws/geocoder/v1?'.$param.$sk;die;
    $sign = md5('/ws/geocoder/v1?'.$param.$sk);
    $url = 'https://apis.map.qq.com/ws/geocoder/v1?key='.$key.'&location='.$lat.','.$lng.'&sig='.$sign;



    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36');
    $content = curl_exec($ch);
    curl_close($ch);

    $result = [];
    if($content) {
        $result = json_decode($content, true);
    }
    return $result;
}
?>