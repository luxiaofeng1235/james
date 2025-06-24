<?php
require_once 'vendor/autoload.php';
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20210111\SmsClient;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
try {
    // 实例化一个认证对象，入参需要传入腾讯云账户 SecretId 和 SecretKey，此处还需注意密钥对的保密
    // 代码泄露可能会导致 SecretId 和 SecretKey 泄露，并威胁账号下所有资源的安全性。以下代码示例仅供参考，建议采用更安全的方式来使用密钥，请参见：https://cloud.tencent.com/document/product/1278/85305
    // 密钥可前往官网控制台 https://console.cloud.tencent.com/cam/capi 进行获取
    $cred = new Credential("AKIDNabMsS6qYL7g9MK4Y4RZS5TcmgJhof4M", "Sv9NSJupvoUu3BD5kCTiko3B7u62lFUT");
    // 实例化一个http选项，可选的，没有特殊需求可以跳过
    $httpProfile = new HttpProfile();
    $httpProfile->setEndpoint("sms.tencentcloudapi.com");

    // 实例化一个client选项，可选的，没有特殊需求可以跳过
    $clientProfile = new ClientProfile();
    $clientProfile->setHttpProfile($httpProfile);
    // 实例化要请求产品的client对象,clientProfile是可选的
    $client = new SmsClient($cred, "ap-nanjing", $clientProfile);

    // 实例化一个请求对象,每个接口都会对应一个request对象
    $req = new SendSmsRequest();

    $params = array(
        "PhoneNumberSet" => array( "+8613786925698" ),
        "SmsSdkAppId" => "1400894092",
        "TemplateId" => "2203222",
        "SignName" => "大连朗思特网络科技有限公",
        "TemplateParamSet" => array( "123456", "2" )
    );
    $req->fromJsonString(json_encode($params));

    // 返回的resp是一个SendSmsResponse的实例，与请求对象对应
    $resp = $client->SendSms($req);
    // 输出json格式的字符串回包
    $res = $resp->toJsonString();
    $req = json_decode($res,true);
    echo '<pre>';
    var_dump($req);
    echo '</pre>';
    exit;
}
catch(TencentCloudSDKException $e) {
    echo $e;
}