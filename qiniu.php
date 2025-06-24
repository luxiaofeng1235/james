<?php


require dirname(__FILE__).'/vendor/autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Http\Client;

// 配置七牛云账号
// $accessKey = $config['access_key'];
// $secretKey = $config['secret_key'];

// 构建鉴权对象
$auth = new Auth('yEacIJa59EK8ar6UMlOkRft39W91qoJmmHzhw9fC', 'dtPS1DPJPFzh523Kp9ItFSqmSFP7CqQ7-CDyttNo');
echo '<pre>';
var_dump($auth);
echo '</pre>';
exit;
//h('yEacIJa59EK8ar6UMlOkRft39W91qoJmmHzhw9fC','dtPS1DPJPFzh523Kp9ItFSqmSFP7CqQ7-CDyttNo');
// 调用内容审核接口
$securityUrl = 'http://ai.qiniuapi.com/v3/image/censor';
$params = [
    'data' => [
        'uri' => $imageUrl
    ]
];

$headers = [
    'Content-Type' => 'application/json',
    'Authorization' => 'Qiniu ' . $auth->signRequest($securityUrl, json_encode($params), 'application/json')
];

$response = Client::post($securityUrl, json_encode($params), $headers);

if ($response->statusCode === 200) {
    $result = json_decode($response->body, true);
    echo "审核结果： " . json_encode($result);
} else {
    echo "审核失败： " . $response->body;
}