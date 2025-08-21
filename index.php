<?php

/**
 * note 生成验证码
 * @param int $length 验证码长度
 * @return string
 */
function create_sms_code($length = 4)
{
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= rand(0, 9);
    }
    return $code;
}


phpinfo();
exit;
$a="123ab "+5;
echo "<pre>";
var_dump($a);
echo "</pre>";
exit();

echo 3434;exit;;
echo 11111111111133;exit;
phpinfo();

?>