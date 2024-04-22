<?php

//Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0
//Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
require_once($dirname.'/library/file_factory.php');
// require_once($dirname.'/library/Ares333.php');//代理IP使用
use QL\QueryList;
use Overtrue\Pinyin\Pinyin;
use sqhlib\Hanzi\HanziConvert;

$files = readFileData('/mnt/book/xsw_page_list/store_page_698.txt');
$range = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_range'];
$rules = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_list'];

$ret = traditionalCovert($files);
echo '<pre>';
print_R($ret);
echo '</pre>';
exit;

echo '<pre>';
print_R($files);
echo '</pre>';
exit;
// $rt = QueryList::get('https://www.xsw.tw/book/230000/');

$item = QueryList::html($files)->rules($rules)
            ->range($range)
            ->query()
            ->getData();
$item = $item->all();
$data = StoreModel::traverseEncoding($item);
echo '<pre>';
print_R($data);
echo '</pre>';
exit;

dd(new StoreModel());
 $rules = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['content'];
 dd($rules);

// $html = readFileData('./biquge.html');
$html =webRequest('http://www.biquge5200.net/148_148106/178191780.html','GET');


$rules = [
          'content'    =>['#content','html','',function($content){
             $con =  array_iconv($content);
             // $con = filterHtml($con);
             //替换标签
            $preg = '/<div id=\"gc1\".*?>.*?<\/div>/ism';
            $con = preg_replace($preg, '',$con);
            //替换为实体标签的P标签处理
            $con = filterHtml($con);
             return $con;
          }],
          'meta_data'       =>['meta[http-equiv=mobile-agent]','content'],
          'href'      =>['.con_top a:eq(2)','href'],
        ];
$data = QueryList::html($html)->rules($rules)->query()->getData();
$data = $data->all();
$meta_data = $data['meta_data']??'';
$href = $data['href'];
$aa = getHtmlUrl($meta_data,$href);
dd($aa);

// $str = 'j11     122       ';
// echo myTrim($str);

// die;



// $aa = webRequest('https://www.xsw.tw/book/1144673/248318027.html','GET');
// echo '<pre>';
// var_dump($aa);
// echo '</pre>';
// exit;
// $ar = webRequest('https://bapi.51daili.com/getapi2?linePoolIndex=-1&packid=2&time=13&qty=30&port=2&format=json&field=ipport,expiretime,regioncode,isptype&dt=1&usertype=17&uid=43558','GET');
// $data =json_decode($ar,true);
// $arr =[];
// foreach($data['data'] as $key=>$val){
//   echo $val['expireTime']."\r\n";
//   $arr[] =strtotime($val['expireTime']);
// }
// echo "=============\r\n";
// $t = min($arr);
// echo date('Y-m-d H:i:s',$t);
// die;
$url=[
  'http://www.biquge5200.net/182_182787/',
];
$list = curl_pic_multi::Curl_http($url);
echo '<pre>';
print_R($list);
echo '</pre>';
exit;
$i = 0;
foreach($arr as $val){
  if(empty($val) ||  strpos($val,'请求失败')){
      $i++;
  }
}
echo '<pre>';
var_dump($i);
echo '</pre>';
exit;

$aa = webRequest('https://m.xsw.tw/1123700/247529821.html','GET');


$num = 300; //最多只能配置300个
$limit = 100;
$t= ceil($num/$limit);
$items =[];
for ($i=0; $i <$t ; $i++) {
    $proxy  = webRequest('https://api.caiji.com//web_v1/ip/get?key=a07e7c2bc0fffef9ac12b081d0f66887&quantity=100&format=json&protocol=3&region=&nr=3&lb=%5Cn&ip_type=1','GET');
    $data = json_decode($proxy,true);
    $proxy_list = $data['data']['list'] ?? [];
    $items = array_merge($proxy_list,$items);
}

$new_proxy  =[];
if(count($items)>0){
    foreach($items as $key =>$val){
        if($key<$num){
            $new_proxy[$key] = $val;
        }
    }
}


$i = 0;
foreach($new_proxy as $proxy){
    $i++;
    $a = curlProxyState('http://www.paoshu8.info/0_859/653518.html',$proxy);
    echo '<pre>';
    print_R($a);
    echo '</pre>';
    exit;
    echo "num = {$i}\r\n";
}
echo "over\r\n";
die;


 // $a = webRequest('http://www.baidu.com','GET');
 // echo '<pre>';
 // print_R($a);
 // echo '</pre>';
 // exit;

$exec_start_time = microtime(true);
$file = readFileData('/mnt/book/chapter/9a7819bc9a00853972f2d6a985310647.json');
$t = json_decode($file,true);
$arr = array_chunk($t,300);
echo "共需要 ".count($arr)."页\r\n";

// $goods_list= $arr[0] ?? [];
// $urls= array_column($goods_list,'chapter_link');
// $items = Ares333::curlThreadList($urls);
//  foreach($items as $key =>$val){
//      if($val['http_code'] != 200){
//         echo  "{$val['http_code']}\t111111111111\r\n";
//      }
// }
// echo "over\r\n";
// die;


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

    echo "current-page：{$i} count：".count($items)."\r\n";
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

    'http://m.paoshu8.info/wapbook-26189-140160348-2',
];


for ($i=0; $i < 50; $i++) {
   $urls[] = $url;
}
echo '<pre>';
print_R($urls);
echo '</pre>';
exit;
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
