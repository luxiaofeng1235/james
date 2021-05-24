<?php



// $tt = "test\r\n";
// $tt.="1122222\r\n";
// $tt.='33344';

 // =file_put_contents('sites.txt', $tt,FILE_APPEND);
// echo '<pre>';
// var_dump($aa);
// echo '</pre>';
// exit;


//https://blog.csdn.net/zhemejinnameyuanxc/article/details/83383434
ini_set("limit_memory",'8000M');
set_time_limit(600);
//获取当前的路径位置
$dirname = dirname(__FILE__); //返回根目录
$dirname = str_replace('\\', '/', $dirname);
// require_once ($dirname."/library/ThreeDesUtil.php");
      
$data = '1234567887654321';//加密明文
$method = 'aes-128-ecb';//加密方法 :对应JAVA的AES/ECB/PKCS5Padding算法
$passwd = '12344321';//加密密钥
$options = 0;//数据格式选项（可选）
$iv = '';//加密初始化向量（可选）

echo "原始串为data：".$data;
echo "<hr/>";

$result = openssl_encrypt($data, $method, $passwd, OPENSSL_RAW_DATA);
$code=base64_encode($result);
echo "加密后的串为：".$code;
echo "<br/>";

$jiemi =openssl_decrypt($result, $method, $passwd,OPENSSL_RAW_DATA);
echo "解密后为：".$jiemi;
echo "<br/>";exit;

//解密：

// $iput='123456';
// $key='123';
// $data = openssl_encrypt($input,'des-ede3',$key,0);
// $data= base64_decode($data);

// $miwen =$data;

// #解密
// $decrypted = openssl_decrypt(base64_decode($miwen),'des-ede3',$key,OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
// echo '<pre>';
// var_dump($decrypted);
// echo '</pre>';
// exit;

$key  = 'ABCEDFGHIJKLMNOPQ';
$iv  = '0123456789';
$des = new Encrypt($key, $iv);
$str = "abcdefghijklmnopq";
echo "source: {$str},len: ",strlen($str),"\r\n";
$e_str = $des->encrypt3DES($str);
echo "entrypt: ".$e_str, "\r\n";
exit;
$res = ThreeDesUtil::Encrypt();
echo '<pre>';
var_dump($res);
echo '</pre>';
exit;
function getDatas($content = ''){
      global $dirname;
      require_once ($dirname."/library/phpanalysis/phpanalysis.class.php");
      $pa=new PhpAnalysis();
      $pa->SetSource($content);
      $pa->resultType=2;
      $pa->differMax=true;
      $pa->StartAnalysis();
      $arr=$pa->GetFinallyIndex();
      $splitVal =  array_keys($arr);
      if($splitVal[0]  && in_array($splitVal,['广州','东莞']) ) {
            return $splitVal[0].$splitVal[1];
      }if(strpos($splitVal[0], '银行')!=false){
            return $splitVal[0];
      }else if(isset($splitVal[1]) && $splitVal[1] =='银行'){
            return $splitVal[0].$splitVal[1];
      }else{
            return $splitVal[0];
      }
}

 
  $arr = getDatas('大连银行');

//   //判断
echo '<pre>';
print_R($arr);
echo '</pre>';
exit;




 

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