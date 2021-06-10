<?php
phpinfo();
exit;
/**
 * des-ecb加密
 * @param string  $data 要被加密的数据
 * @param string  $key 加密密钥(64位的字符串)
 */
function des_ecb_encrypt($data, $key){
    return openssl_encrypt ($data, 'des-ecb', $key);
}
// 输出：string 'http://www.mytest.org/a/b/' (length=26)

/*
Array(
0 => aes-128-cbc,   // aes加密
1 => des-ecb,       // des加密
2 => des-ede3,      // 3des加密
...
)
*/
 
/**
 * des-ecb解密
 * @param string  $data 加密数据
 * @param string  $key 加密密钥
 */
function des_ecb_decrypt ($data, $key){
    return openssl_decrypt ($data, 'des-ecb', $key);
}


$ret= '%7B%22PayInfo%22%3A%7B%22CurrencyCode%22%3A%22156%22%2C%22AccNo%22%3A%226225880161919122%22%2C%22AccName%22%3A%22%E9%8D%97%E3%88%A1%E6%AA%BD%E5%AE%84%EF%BF%BD%22%2C%22AccType%22%3A1%2C%22TxnAmt%22%3A100%7D%2C%22TermInfo%22%3A%7B%22TermID%22%3A%22DF000298%22%7D%2C%22TxnInfo%22%3A%7B%22TxnTime%22%3A%22154317%22%2C%22TxnDate%22%3A%2220210525%22%2C%22OrderID%22%3A%2220210525154317773666%22%7D%2C%22MerInfo%22%3A%7B%22ChlID%22%3A%22010113%22%2C%22MerID%22%3A%22333110348160198%22%2C%22MerName
        %22%3A%22%E5%A8%B4%E5%AC%AD%E7%98%AF%22%7D%2C%22VerInfo%22%3A%7B%22VerInter%22%3A%222.0%22%7D%2C%22TxnNo%22%3A%22PAY201%22%2C%22Signature%22%3A%22YTVMMJA1MJNIYJZIODQWZJQ4MDFLYZLKMDAZZGIYYTI=%22%7D';

echo '<pre>';
print_R(urldecode($ret));
echo '</pre>';
exit;
$a=openssl_get_cipher_methods();
echo '<pre>';
var_dump($a);
echo '</pre>';
exit;
$data = '123456';//加密明文
$passwd = 'udik876ehjde32dU61edsxsf';//加密密钥
$options = 0;//数据格式选项（可选）
$iv = '';//加密初始化向量（可选）

$aa= des_ecb_encrypt($data,$passwd);

$code = bin2hex($aa);
echo "data源数据：".$data;
echo "<br/>";
echo "通讯秘钥：".$passwd;
echo "<br/>";
echo "加密后的数据：".$aa;
// $aaa= pack("H*",bin2hex($aa)); 
$jiemi= des_ecb_decrypt($aa,$passwd);
echo "<br/>";
echo "通过加密后反解密的：".$jiemi;

?>