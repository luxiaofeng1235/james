<?php

ini_set("limit_memory",'8000M');
set_time_limit(600);
//获取当前的路径位置
$dirname = dirname(__FILE__); //返回根目录
$dirname = str_replace('\\', '/', $dirname);
require_once ($dirname."/library/init.inc.php");


 
$time = date('Y-m-d H:i:s');
$merOrderId = '319420170832021042864'; //处理生成的订单号
$str = 'divisionFlag=true&goods=[{"body":"goods body","goodsCategory":"Auto","goodsId":"0001","goodsName":"电费","price":100,"quantity":1,"subMerchantId":"988460101800202","subOrderAmount":1},{"body":"goods body","goodsCategory":"Auto","goodsId":"0002","goodsName":"电费","price":200,"quantity":2,"subMerchantId":"988460101800201","subOrderAmount":2}]&instMid=APPDEFAULT&merOrderId='.$merOrderId.'&mid=898201612345678&msgSrc=WWW.TEST.COM&msgType=trade.precreate&orderDesc=账单描述&platformAmount=0&requestTimestamp='.$time.'&signType=SHA256&tid=00000001&totalAmount=3fcAmtnx7MwismjWNhNKdHC44mNXtnEQeJkRrhKJwyrW2ysRR';

echo "待验签参数：".$str;
echo "<hr/>";


        $res=[
            // 'divisionFlag' =>true,
            // 'goods'=>[
            //         [
            //             'body'=>'goods body',
            //             'goodsCategory'=>'113,122',
            //             'goodsId'=>'0001',
            //             'goodsName'=>'电费1',
            //             'price'=>'100',
            //             'quantity'=>'1',
            //             'subMerchantId'=>'988460101800202',
            //             'subOrderAmount'=>'1',
            //         ],
            //         [
            //             'body'=>'goods body',
            //             'goodsCategory'=>'113,122',
            //             'goodsId'=>'0002',
            //             'goodsName'=>'电费2',
            //             'price'=>'200',
            //             'quantity'=>'2',
            //             'subMerchantId'=>'988460101800234',
            //             'subOrderAmount'=>'2',
            //         ],
            // ],
            'instMid'=>'APPDEFAULT',
            'merOrderId'=>'319420170832021042746',
            'mid'=>'898201612345678',
            'msgSrc'=>'WWW.TEST.COM',
            'msgType'=>'trade.precreate',
            'signType'=>'SHA256',
            'orderDesc'=>'账单描述',
            // 'platformAmount'=>'0',
            'requestTimestamp'=>date('Y-m-d H:i:s'),
            'signType'=>'SHA256',
            'tid'   =>  '00000001',
            'totalAmount'=>'300',

        ];


//55116142f6904c040ff83173913b2dd0ecde8314a68bcdc74cc31d3228189545
$sign = encrypt_sha256($str);
echo '生成的秘钥:'.$sign;
echo "<br/>";
echo "<br/>";




$postdata= '{"mid":"898201612345678","tid":"00000001","msgType":"trade.precreate","msgSrc":"WWW.TEST.COM","instMid":"APPDEFAULT","signType":"SHA256","orderDesc":"账单描述","merOrderId":"'.$merOrderId.'","totalAmount":3,"requestTimestamp":"'.$time.'","divisionFlag":true,"platformAmount":0,"goods":[{"body":"goods body","goodsCategory":"Auto","goodsId":"0001","goodsName":"电费","price":100,"quantity":1,"subMerchantId":"988460101800202","subOrderAmount":1},{"body":"goods body","goodsCategory":"Auto","goodsId":"0002","goodsName":"电费","price":200,"quantity":2,"subMerchantId":"988460101800201","subOrderAmount":2}],"sign":"'.$sign.'"}';

echo '发送的报文：'.$postdata;
echo "<hr/>";
 

$url = 'https://qr-test2.chinaums.com/netpay-route-server/api/';
 

 $initdata = webRequest($url,'POST',$postdata);
 echo '<pre>';
 print_R($initdata);
 echo '</pre>';
 exit;



?>