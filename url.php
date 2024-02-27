<?
set_time_limit(0);
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;##引入querylist的采集器



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

$list = file('./url.txt');
foreach($list as $key =>$val){
    $urls = str_replace("\r\n",'',$val);
    $items[]=$urls;
}

$logpath ='C:\Users\Administrator\Desktop\下载图片';

foreach($items as $key =>$url){
    $link_data = explode('/',$url);
    $cur_url =end($link_data);

    $local_file = $logpath.DS . $cur_url;
    if(!file_exists($local_file)){
        $t_data[]=[
          'url'   =>$url,
          'str'   =>$cur_url,
      ];
    }
}
echo '<pre>';
print_R($t_data);
echo '</pre>';
exit;
foreach($t_data as $d_url){
    $filename = $logpath.'\\'.$d_url['str'];
    $aa = curl_file_get_contents($d_url['url']);
    $res  = file_put_contents($filename, $aa);
}
echo "over";
?>