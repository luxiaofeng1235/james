<?php
$url = "http://www.paoshu8.info/files/article/image/109/109470/109470s.jpg"; // 远程图片的URL
$aa = curl_file_get_contents($url);
$res  = file_put_contents('./1111.jpg', $aa);
echo '<pre>';
var_dump($res);
echo '</pre>';
exit;
//获取远程图片
function curl_file_get_contents($durl){
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $durl);
   curl_setopt($ch, CURLOPT_TIMEOUT, 2);
   curl_setopt($ch, CURLOPT_ENCODING,'gzip');
   // curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
   curl_setopt($ch, CURLOPT_REFERER,0);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $r = curl_exec($ch);
   curl_close($ch);
   return $r;
 }

?>