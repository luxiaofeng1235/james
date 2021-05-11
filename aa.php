<?php

// $tt = "test\r\n";
// $tt.="1122222\r\n";
// $tt.='33344';


// $aa =file_put_contents('sites.txt', $tt,FILE_APPEND);
// echo '<pre>';
// var_dump($aa);
// echo '</pre>';
// exit;

 
ini_set("limit_memory",'8000M');
set_time_limit(600);
//获取当前的路径位置
$dirname = dirname(__FILE__); //返回根目录
$dirname = str_replace('\\', '/', $dirname);
require_once ($dirname."/library/init.inc.php");

 

// $str ='151515|2
// 140400001|10000000016327|0|100|测试收款人名称|测试行名称|123456789123|6236681300001758483|附言1
// 140400002|10000000016327|0|100|测试收款人名称|测试行名称|123456789123|6236681300001758483|附言2';
// echo '<pre>';
// var_dump($str);
// echo '</pre>';
// echo "md5后的内容：";
// echo "<br/>";

// $wai_str= md5($str);
// echo $wai_str;
// echo "<br/>";
// $aaa =encrypt_sha256($wai_str);

// echo encrypt_sha256('b205dd291f1c7117a6f895a97bc8a943');
// echo "<hr/>";
// echo "md5变为sha256后：".$aaa;
// exit;


 
$time = date('Y-m-d H:i:s');
$merOrderId = '319420170832021042842'; //处理生成的订单号
$str = 'divisionFlag=true&goods=[{"body":"goods body","goodsCategory":"Auto","goodsId":"0001","goodsName":"电费","price":100,"quantity":1,"subMerchantId":"988460101800202","subOrderAmount":1},{"body":"goods body","goodsCategory":"Auto","goodsId":"0002","goodsName":"电费","price":200,"quantity":2,"subMerchantId":"988460101800201","subOrderAmount":2}]&instMid=MINIDEFAULT&merOrderId='.$merOrderId.'&mid=898201612345678&msgSrc=WWW.TEST.COM&msgType=wx.unifiedOrder&orderDesc=账单描述&platformAmount=0&requestTimestamp='.$time.'&signType=SHA256&tid=00000001&totalAmount=3fcAmtnx7MwismjWNhNKdHC44mNXtnEQeJkRrhKJwyrW2ysRR';

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
            'instMid'=>'MINIDEFAULT',
            'merOrderId'=>'319420170832021042746',
            'mid'=>'898201612345678',
            'msgSrc'=>'WWW.TEST.COM',
            'msgType'=>'wx.unifiedOrder',
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




$postdata= '{"mid":"898201612345678","tid":"00000001","msgType":"wx.unifiedOrder","msgSrc":"WWW.TEST.COM","instMid":"MINIDEFAULT","signType":"SHA256","orderDesc":"账单描述","merOrderId":"'.$merOrderId.'","totalAmount":3,"requestTimestamp":"'.$time.'","divisionFlag":true,"platformAmount":0,"goods":[{"body":"goods body","goodsCategory":"Auto","goodsId":"0001","goodsName":"电费","price":100,"quantity":1,"subMerchantId":"988460101800202","subOrderAmount":1},{"body":"goods body","goodsCategory":"Auto","goodsId":"0002","goodsName":"电费","price":200,"quantity":2,"subMerchantId":"988460101800201","subOrderAmount":2}],"sign":"'.$sign.'"}';

echo '发送的报文：'.$postdata;
echo "<hr/>";
 

$url = 'https://qr-test2.chinaums.com/netpay-route-server/api/';
 

 $initdata = webRequest($url,'POST',$postdata);
 echo '<pre>';
 print_R($initdata);
 echo '</pre>';
 exit;



?>