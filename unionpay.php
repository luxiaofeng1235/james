<?php

ini_set("limit_memory",'8000M');
set_time_limit(600);
//获取当前的路径位置
$dirname = dirname(__FILE__); //返回根目录
$dirname = str_replace('\\', '/', $dirname);
require_once ($dirname."/library/init.inc.php");

$mdkey ='fcAmtnx7MwismjWNhNKdHC44mNXtnEQeJkRrhKJwyrW2ysRR';
//报文推送时间
$time = date('Y-m-d H:i:s');
$merOrderId = '3194201708320210428490'; //处理生成的订单号

$res=[

    'divisionFlag' =>'true',
    'goods'=>[
            [
                'body'=>'goods bod2y',
                'goodsCategory'=>'Auto',
                'goodsId'=>'0002',
                'goodsName'=>'测试',
                'price'=>'100',
                'quantity'=>'1',
                'subMerchantId'=>'988460101800202',
                'subOrderAmount'=>'1',
            ],
            [
                'body'=>'goods body1',
                'goodsCategory'=>'Auto',
                'goodsId'=>'0003',
                'goodsName'=>'电费',
                'price'=>'200',
                'quantity'=>'2',
                'subMerchantId'=>'988460101800201',
                'subOrderAmount'=>'2',
            ],
    ],
    'instMid'=>'APPDEFAULT',
    'merOrderId'=>$merOrderId,
    'mid'=>'898201612345678',
    'msgSrc'=>'WWW.TEST.COM',
    'msgType'=>'trade.precreate',
    'orderDesc'=>'账单描述11',
    'platformAmount'=>'0',
    'requestTimestamp'=>date('Y-m-d H:i:s'),
    'signType'=>'SHA256',
    'tid'   =>  '00000001',
    'totalAmount'=>'3',

];

 

$postdata = Authorization($res, $mdkey);
echo '<pre>';
print_R($postdata);
echo '</pre>';

 


$url = 'https://qr-test2.chinaums.com/netpay-route-server/api/';
//发送结果
$initdata = webRequest($url,'POST',$postdata);

echo '<pre>';
print_R($initdata);
echo '</pre>';
exit;
 


?>