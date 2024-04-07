<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
require_once($dirname.'/library/file_factory.php');
// require_once($dirname.'/library/Ares333.php');//代理IP使用
use QL\QueryList;
use Overtrue\Pinyin\Pinyin;
$a = file_get_contents('https://tps.kdlapi.com/api/gettps/?secret_id=omreo9ymecyrhy2iv14w&signature=oge14f34l4by3512x278obxrb2&num=1&pt=2&format=json&sep=1');
echo '<pre>';
print_R($a);
echo '</pre>';
exit;
$exec_start_time = microtime(true);
$file = readFileData('/mnt/book/chapter/3b628cc6ceae3ec44c695f47d9a511c7.json');
$t = json_decode($file,true);
$arr = array_chunk($t,300);

$i =0;
foreach($arr as $v){
  $i++;
   $urls= array_column($v,'chapter_link');
    $items = Ares333::curlThreadList($urls);
    foreach($items as $key =>$val){
         if($val['http_code'] != 200){
            echo  "{$val['http_code']}\t111111111111\r\n";
         }
    }
    die;
    echo '<pre>';
    print_R($items);
    echo '</pre>';
    exit;

    echo "current-page：{$i} count：".count($items)."\r\n";
    die;
    sleep(1);
}
echo "over\r\n";
echo "over\r\n";
echo '<pre>';
print_R(count($items));
echo '</pre>';
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "\nScript execution time: ".round(($executionTime/60),2)." minutes \r\n";
exit;
echo "over\r\n";
die;
// echo '<pre>';
// print_R($items);
// echo '</pre>';
// exit;


$str= '    1956.第1956章 他  一定会记仇    ';
$t = replaceLRSpace($str);
echo '<pre>';
dd($t);
echo '</pre>';
exit;
echo '<pre>';
var_dump($t);
echo '</pre>';
exit;

$t = trim($str);
echo '<pre>';
var_dump($t);
echo '</pre>';
exit;

$pattern = '/\s+/'; // 匹配一个或多个空格
$replacement = ''; // 空替换字符串
$result = preg_replace($pattern, $replacement, $string);
echo $result; // 输出"HelloWorld"
exit();


///一次申请三个一起判断，火力全开来进行判断，需要用三个IP来一起抓取提高效率
$proxy_detail = NovelModel::checkProxyExpire();//获取列表的PROXY
$proxy_count =  NovelModel::checkMobileKey();//获取统计的PROXY
$proxy_empty =  NovelModel::checkMobileEmptyKey();//获取修复空数据的PROXY
$proxy_img = NovelModel::checkImgKey(); //获取修复图片的PROXY


/*
//校验代理IP是否过期
if(!$proxy_detail || !$proxy_count || !$proxy_empty || !$proxy_img){
   exit("入口--代理IP已过期，key =".Env::get('ZHIMA_REDIS_KEY').",".Env::get('ZHIMA_REDIS_MOBILE_KEY').",".Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA').",".Env::get('ZHIMA_REDIS_IMG')." 请重新拉取最新的ip\r\n");
}
$file_name =Env::get('SAVE_JSON_PATH') .DS .'a1cc13dfb7f54f7df320821cbacbaae4.' .NovelModel::$json_file_type;
echo '<pre>';
print_r ($file_name);
echo '</pre>';
$json_data = readFileData($file_name);
if(!$json_data){
  exit('未找到JSON内容');
}
echo 33;die;

$items = json_decode($json_data,true);
foreach($items as &$v){
  $v['link_url'] = $v['chapter_link'];
}
echo '<pre>';
print_R($items);
echo '</pre>';
exit;
$items =NovelModel::exchange_urls($items , 0 , 'count');

$items = array_slice($items , 0,200);



///mnt/book/txt/a1cc13dfb7f54f7df320821cbacbaae4
$urls = array_column($items , 'mobile_url');

*/

// $list = curl_pic_multi::Curl_http($urls,5);
// echo '<pre>';
// print_R($list);
// echo '</pre>';
// exit;
$urls = [
    'http://www.baidu.com',
];
$contents_arr  =guzzleHttp::multi_req($urls,'image');
echo '<pre>';
print_r ($contents_arr);
echo '</pre>';
// echo '<pre>';
// print_R($contents_arr);
// echo '</pre>';
// exit;
$rand_str = ClientModel::getRandProxy();//随机获取代理
$list  = ClientModel::callRequests1($contents_arr , $items,'ghttp',$rand_str);
echo '<pre>';
print_R($list);
echo '</pre>';
exit;

echo '<pre>';
print_R($items);
echo '</pre>';
exit;
$list = curl_pic_multi::Curl_http($urls,5);
echo '<pre>';
print_R($list);
echo '</pre>';
exit;

$list = curl_pic_multi::Curl_http($urls,3);
// echo '<pre>';
// print_R($list);
// echo '</pre>';
// exit;
$res = NovelModel::getErrSucData($list, $urls,'curl');
echo '<pre>';
print_R($res);
echo '</pre>';
exit;

$items  =guzzleHttp::multi_req($urls,'count');
echo '<pre>';
print_R($items);
echo '</pre>';
exit;
$sucData = $errData = $errLink = [];
foreach($items as $key =>$val){
    if(strstr($val, '请求失败') ||  empty($val)) {
        $errLink[]=$urls[$key];
    }else{
        $sucData[] = $val;
    }
}


echo "fisst-num:".count($sucData).PHP_EOL;
$new_list= [];
if(!empty($errLink)){
    $n_num = count($errLink);//统计总数
    $list = array_chunk($errLink,200);
    do{
        $tdk = $list; //这个次方充当一个轮训的变量，每次发现成功了把其中的数据删除掉
        $all_num = 0;
        if(!empty($tdk)){
            foreach($tdk as $k =>$v){
                $data = guzzleHttp::multi_req($v,'empty');
                // $data = curl_pic_multi::Curl_http($v,3);
                foreach($data as $gk =>$gv){
                    if(!$gv) continue;
                    if(!strstr($gv, '请求失败')){
                        $all_num++;
                        if(empty($gv)){
                            $gv ='此作者很懒，什么也么写';
                        }
                        $new_list[] = $gv;
                        unset($tdk[$k][$gk]);//每次成功后，就不需要重复轮训了
                    }else{
                        //echo '11111'.PHP_EOL;
                    }
                }
                //重置里面的键值，保证数据唯一性
                $tdk[$k] = array_values($tdk[$k]);
            }
            $tdk =array_filter($tdk);//过滤掉空串
        }

        echo "每一轮过后，tdk的长度 ".count($tdk) . "\r\n";
       //  echo '<pre>';
       //  print_R($new_list);
       //  echo '</pre>';
       echo "n-num {$n_num} all_num {$all_num}\r\n";
       //  echo '<pre>';
       //  print_R($tdk);
       //  echo '</pre>';
       //  exit;
       //  exit;
        if($n_num == $all_num){
            echo '全部内容来下来了';
            break;
        }
    }while(true);
}
// echo '<pre>';
// print_R($new_list);
// echo '</pre>';
// echo "new-num：".count($new_list).PHP_EOL;
die;


// $mutl_curl = new curl_pic_multi();

// echo '<pre>';
// print_R($data);
// echo '</pre>';
// exit;

// $content= MultiHttp::curlGet($urls,null,true);


$url = [

    'https://www.fumubang.com/',
];

// $data = $mutl_curl->Curl_http($url,3);
// echo '<pre>';
// print_R($data);
// echo '</pre>';
// exit;
// echo '<pre>';
// print_R($data);
// echo '</pre>';
// exit;
$res = guzzleHttp::multi_req($url,'count');
echo '<pre>';
print_R($res);
echo '</pre>';
exit;

$aaa = curl_pic_multi::Curl_http($url);
echo '<pre>';
print_R($aaa);
echo '</pre>';
exit;

foreach($url as $val){
    //转换获取M站的地址
     $mobile_link = NovelModel::mobileLink($val);
     for ($i=0; $i <5 ; $i++) {
          $s_url = $mobile_link.'-'.($i+1);
          $infos[]=$s_url;
     }
}
//去除404的文件信息
$items = curl_pic_multi::Curl_http($infos);
foreach($items as $k =>$v){
    //去除掉404的文件
    if( $v &&  strstr($v , '您请求的文件不存在')){
         unset($items[$k]);
    }
}
echo '<pre>';
print_R($items);
echo '</pre>';
exit;
echo 111;die;
$res = guzzleHttp::multi_req($url);
echo '<pre>';
print_R($res);
echo '</pre>';
exit;
// $res = MultiHttp::curlGet([$url],null,true);
echo '<pre>';
print_R($res);
echo '</pre>';
exit;
// $res = guzzleHttp::multi_req($url);
echo '<pre>';
print_R($res);
echo '</pre>';
exit;
echo 33;die;

//根据当前的URL进行分析转出url

$exec_start_time = microtime(true);
$limit =Env::get('LIMIT_SIZE');
$list = $mysql_obj->fetchAll('select link_url as link_str,chapter_id,CONCAT(\''.Env::get('APICONFIG.PAOSHU_HOST').'\',link_url) as link_url,link_name from ims_chapter where story_id="92_92763"  limit 10','db_slave');
$t =array_chunk($list, $limit);
$all_num = 0;
$curl= new curl_pic_multi();
foreach($t as $key =>$val){
    $urls = array_column($val,'link_url');
    $content= MultiHttp::curlGet($urls,null,true);
    $all_num+=count($content);
    echo 'index-num：'.count($val)."\r\n";
    echo 'curl-num：'.count($content)."====\r\n";
    sleep(1);
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
$proxyInfo = getZhimaProxy();
echo '<pre>';
print_R($proxyInfo);
echo '</pre>';
echo "请求完成，一次配置爬取".count($list)."个url,数据爬取过来的有".$all_num."个页面\r\n";
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
exit;
?>
