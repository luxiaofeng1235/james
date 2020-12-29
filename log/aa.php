<?php
$aaa ='1,2,3,4,5';
$arr = explode(',' , $aaa);
echo '<pre>';
print_R($arr);
echo '</pre>';
exit;

$cc = '123456';
$arr =str_split($cc);
echo '<pre>';
var_dump($arr);
echo '</pre>';
exit;
echo 333;die;
?>