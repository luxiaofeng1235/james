<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;


echo '<pre>';
print_R($urlRules[Env::get('APICONFIG.PAOSHU_STR')]);
echo '</pre>';
exit;
$html = readFileData('E:\html_data\detail_29_29995.txt');
$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['info'];
// $rules =
    // $redis_book_key = 'store_info:'.$store_id;
    // $redis_data  = $redis_data->get_redis($redis_book_key);
    // if(!$redis_data){
    //     //爬取相关规则下的类
         $info_data=QueryList::html($html)
                ->rules($rules)
                ->query()->getData();
        $list = $info_data->all();
        echo '<pre>';
        print_R($list);
        echo '</pre>';
        exit;

$aa = NovelModel::getCharaList($html);
echo '<pre>';
print_R($aa);
echo '</pre>';
exit;
preg_match('/<div id=\"list\".*?>.*?<\/dl>/ism',$html,$list);
echo '<pre>';
print_R($list);
echo '</pre>';
exit;

$list_rule = array(
            'link_name'     =>array('a','text'),
            'link_url'       =>array('a','href'),
        );

$range = '#list dd';
$rt = QueryList::html($html)
        ->rules($list_rule)
        ->range($range)
        ->query()->getData();
echo '<pre>';
print_R($rt->all());
echo '</pre>';
exit;

$data = QueryList::html($html)->rules($rules)->query()
->getData();
echo '<pre>';
print_R($data);
echo '</pre>';
exit;
$list = $data->all();


$list = $mysql_obj->fetchAll("select story_link from ims_link_url limit 500",'db_slave');
$urls = array_column($list,'story_link');
$ret = MultiHttp::curlGet($urls,null,true);
echo '<pre>';
print_R($ret);
echo '</pre>';
exit;

//http://www.paoshu8.info/211_211506/195745608.html
$url = 'http://www.paoshu8.info/0_2/';
$list = NovelModel::getRemoteHmtlToCache($url,'detail:1');
echo '<pre>';
print_R($list);
echo '</pre>';
exit;

$ret = MultiHttp::curlGet(['http://www.paoshu8.info/0_2/'],null,true);
$content = $ret[0] ?? '';
echo '<pre>';
print_R($content);
echo '</pre>';
exit;


$content = $ret[0] ?? '';
file_put_contents('aaa.jpg', $content);
echo 1;die;
$info = getProxyInfo();
echo '<pre>';
print_R($info);
echo '</pre>';
exit;
$key ='foleder_data_';
$info = $redis_data->get_redis($key);
if(!empty($info)){
    $startData = json_decode($info,true);
    $t= range(1,$step);
    foreach($startData as $key =>$val){
        foreach($t as $k =>$v){
            echo "index---".($val.$v)."<br/>";
        }
    }
}
die;
die;


$t = remote_file_exists('https://www.baode.cc/ob/7465/');
echo '<pre>';
print_R($t);
echo '</pre>';
exit;

$res = NovelModel::curl_file_get_contents('https://www.baode.cc/booklogo/758ba256ec3140fd917179844b491071.jpeg');
echo '<pre>';
print_R(Env::get('SAVE_NOVEL_PATH'));
echo '</pre>';
exit;
// $res= webRequest('https://www.baode.cc/class/1_1/','GET');
// echo '<pre>';
// print_R($res);
// echo '</pre>';
// exit;
// $cate_name = NovelModel::getNovelCateId('女生');
// echo '<pre>';
// print_R($cate_name);
// echo '</pre>';
// exit;



$title = '第242章 替身少帅，姐姐撩吗？（47）';
$t = replaceCnWords($title);
echo "<pre>";
var_dump($title);
echo "<br/>";
echo '<pre>';
var_dump($t);
echo '</pre>';
exit;

// $str= md5($t);
// $t_str='9ae92084ba83fa851755a482b579af6d';
// if($t_str == $str){
//     echo 1;die;
// }
echo '<pre>';
print_R($str);
echo '</pre>';
exit;

echo md5('第2章:校花');
die;

$list = $mysql_obj_pro->fetchAll("select * from mc_book limit 5",'db_slave');
echo '<pre>';
print_R($list);
echo '</pre>';
exit;
// $aa= filter_words("112<br/>2qqq");
$meta_data ='format=html5; url=http://m.paoshu8.info/wapbook-92763-178209219/';
$real_path = explode('/',$meta_data);
$href = '/92_92763/';
$str = $real_path[3] ?? '';
$c_data = explode('-',$str);
$link = $href. $c_data[2].'.html';
echo '<pre>';
print_R(Env::get('DATABASE_PRO.PORT'));
echo '</pre>';
exit;

$base_dir = 'E:\\chapter'.DS;
$old_dir = './test_dd';
$new_dir = $base_dir . 'test_arr';

$shell_cmd = 'mv '.$old_dir.' '.$new_dir;
// echo $shell_cmd;die;
exec($shell_cmd,$output,$status);
echo '<pre>';
print_R($output);
echo '</pre>';
exit;

$sql ="SELECT count(story_id) as num,story_id from ims_chapter
GROUP BY story_id
HAVING num>1000 and num<8000";
$list = $mysql_obj->fetchAll($sql,'db_slave');
$dd =array_column($list, 'story_id');
// echo '<pre>';
// print_R($dd);
// echo '</pre>';
// exit;

//143_143425 - 168_168191
// echo '<pre>';
// print_R($dd);
// echo '</pre>';
// exit;
/*
0-200
200 200
400 200
600 200
800 200
1000 200
1200 200
1400 200
1600 200
1800 200
2000 200
 */
//168_168212 - 180_180578
// echo '<pre>';
// print_R($dd);
// echo '</pre>';
// exit;
$start = 0;
$size = 200; //定义直接为200进行跑吧，100太慢了要开很多窗口
//第一次数据 0,100
//
//0-200 已执行
//200 -200 //已执行
//400 -200
//600 -200
$t = array_slice($dd,$start,$size);
$str= '';
foreach($t as $key =>$val){
    $str .="'".$val."',";
}
$str= rtrim($str,',');
echo '<pre>';
print_R($str);
echo '</pre>';
exit;



$client = new GuzzleHttp\Client();
$res = $client->request('GET', 'http://www.paoshu8.info/185_185961/', [
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
        'Accept-Encoding' => 'gzip, deflate, br',
    ]
]);
$html = (string)$res->getBody();
echo '<pre>';
print_R($html);
echo '</pre>';
exit;

$sql = "select * from ims_chapter limit 1";
$rs = $mysql_obj->fetchAll($sql,'db_slave');
$redis_data->set_redis('data','1222');

echo '<pre>';
print_R($redis_data->get_redis('data'));
echo '</pre>';
exit;
$str= "‘叮'的响起一声";
$a = addslashes($str);
echo '<pre>';
var_dump($a);
echo '</pre>';
exit;

$html = <<<STR
<div id="one">
    <div class="two">
        <img src="http://querylist.com/1.jpg" alt="这是图片"><img src="http://querylist.com/2.jpg" alt="这是图片2">
        <a href='http://www.baidu.com'>我是链接名称</a>
    </div>
    <span>其它的<b>一些</b>文本</span>
    <em>哈哈测试来了</em>
</div>
STR;

    // // 请求地址
    //  $url = 'https://proxy.ip3366.net/free/?action=china&page=1' ;

    // // // 定义采集规则
    // $rules = [
    //     'ip' => ['td[data-title=IP]', 'text'],
    //     'port' => ['td[data-title=PORT]', 'text'],
    //     'type' => ['td[data-title=类型]', 'text'],
    //     'location' => ['td[data-title=位置]', 'text'],
    //     'speed' =>  ['td[data-title=响应速度]','text'],
    //     //
    //     'time'  =>  ['td[data-title=最后验证时间]','text'],
    // ];
    // // // 循环的dom主体
    // $range = 'tbody tr';
    // $rt = QueryList::get($url)->rules($rules)->range($range)->query()->getData();
    // echo '<pre>';
    // print_R($rt);
    // echo '</pre>';
    // exit;
    // foreach($rt->all() as $val){
    //     $info['ip'] = $val['ip'];
    //     $info['port'] = $val['port'];
    //     $info['type']   =   $val['type'];
    //     $info['location']   =$val['location'];
    //     $info['speed']  =   $val['speed'];
    //     $info['time']   =   $val['time'];
    //     $allProxy[] = $info;
    //     // $allProxy[]['ip'] = $val['ip'];
    //     // $allProxy[]['port'] = $val['port'];
    //     // $allProxy[]['type'] = $val['type'];
    // }
    // echo '<pre>';
    // print_R($allProxy);
    // echo '</pre>';
    // exit;

// $rules = array(

//     'text' => array('#one','text'),//采集class为two下面的超链接的链接
//     'link'=> array('.two>a','href'),//采集class为two下面的下的文字
//     'link_name' =>array('.two>a','text'),//采集class为two下面的连接地址里的文字
//     'img'=> array('.two>img:eq(1)','src'),//采集two标签里的第二个图片的src的地址
//     'other' => array('span','html'),//采集span标签里的html
//     'tdk'   =>array('#one em','text'),//采集div的id为one的em下的内容
// );
// $data = QueryList::html($html)->rules($rules)->query()
// ->getData();
// $list = $data->all();
// echo '<pre>';
// print_R($data->all());
// echo '</pre>';
// exit;

// $aa =Pinyin('zhongguo');
// echo '<pre>';
//  var_dump($aa);
//  echo '</pre>';
//  exit;
// $rules = array(
//     'cover_logo'       =>array('.jieshao img','src'),
//     'author'    => array('.jieshao .rt em:eq(0) a','text'),
//     'title'     =>array('.jieshao .rt>h1','text'),
//     'status'    =>array('.jieshao .rt em:eq(1)','text'),
//     'third_update_time'    =>array('.jieshao .rt em:eq(2)','text'),
//     'nearby_chapter'    =>array('.jieshao .rt em:eq(3) a','text'),
//     'intro' => array('.intro','html'),
//     'location'  =>  array('.place','text'),
//     'link_url'    =>array('.place a:eq(2)','href'),//当前书籍的url
//     'novelid'   =>array('.info a:eq(2)','href'),//获取a连接里的值
// );


// $rules = array(
//     'link'       =>array('.jieshao img','src'),
// );
// $url = 'https://www.souduw.com/xiaoshuo/ChuaiZaiLiHunHou_QianFuSanGuiJiuKouQiuHeHao.html';
// $info_data=QueryList::get($url)->rules($rules)->query()->getData();
// $info = $info_data->all();
// echo '<pre>';
// print_R($info);
// echo '</pre>';
// exit;



$rules =array(
    'href'  =>  array('a','href'),
    'link_name' =>array('a','text'),

);

$url ='https://www.souduw.com/xiaoshuo/NBA_KaiJuXuanZeDaYaoDangDuiYou.html';
$range = '.mulu li';
$list=QueryList::get($url)->rules($rules)->range($range)->query()->getData();
echo '<pre>';
print_R($list);
echo '</pre>';
exit;
if($list){
    foreach($list->all() as $item){
        $auth_list[]  =  $item;
    }
}

echo '<pre>';
var_dump($auth_list);
echo '</pre>';
exit;

$dom = new DomDocument();
echo '<pre>';
print_R($dom);
echo '</pre>';
exit;
echo 3;die;
$array = array('1','1');
foreach ($array as $k=>&$v){
 $v = 2;
}

class A{
    public function __construct(){
		echo "Class A...<br/>";
 	}
}
class B extends A
{
    public function __construct(){
        echo "Class B...<br/>";  }
}
new B();
die;
##s$sum = 0;
$sum = 100 * (1+100) /2;
echo '1-100的相加总和为：'.$sum;
echo "<hr/>";
echo 33;die;
$params = json_decode( $str, true);
echo '<pre>';
print_R($params);
echo '</pre>';
exit;

echo '<pre>';
print_R($urlParam);
echo '</pre>';
exit;


$order['order_pay_price'] =38;
$order['boil_fee'] =0;
$pay_price = bcadd($order['order_pay_price'] ,$order['boil_fee'],2);
echo '<pre>';
print_R($pay_price);
echo '</pre>';
exit;
$res = '5a'+6;
echo '<pre>';
var_dump($res);
echo '</pre>';
exit;

// $dd = numToWord(5);
// echo '<pre>';
// print_R($dd);
// echo '</pre>';
// exit;

function numToWord($num){
    $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');
    $chiStr = '';
    $num_str = (string)$num;
    $count = strlen($num_str);
    $last_flag = true; //上一个 是否为0
    $zero_flag = true; //是否第一个
    $temp_num = null; //临时数字
    $chiStr = '';//拼接结果
    if ($count == 2) {//两位数
        $temp_num = $num_str[0];
        $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
        $temp_num = $num_str[1];
        $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
    }else if($count > 2){
        $index = 0;
        for ($i=$count-1; $i >= 0 ; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag ) {
                        $chiStr = $chiNum[$temp_num]. $chiStr;
                        $last_flag = true;
                    }
                }else{
                    $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;
                    $zero_flag = false;
                    $last_flag = false;
                }
                $index ++;
        }
    }else{
        $chiStr = $chiNum[$num_str[0]];
    }
    return $chiStr;
}

// $aa ='"[{\"itemId\":1000002,\"itemName\":\"中药饮片规格\",\"proId\":1000002,\"listUvgavv\":[{\"proValueId\":1035298,\"proValue\":\"1g*10\"}]}]"';
// $str= json_decode(json_decode($aa ,true),true);
// echo '<pre>';
// print_R($str);
// echo '</pre>';
// exit;

// header("Content-type: image/gif");
// $im =imagecreate(600,200);
// $background_color = ImageColorAllocate ($im, 255, 255, 200);
// $col = imagecolorallocate($im, 0, 51, 102);
// $font="moxiang.ttf"; //字体所放目录
// $come=iconv("gb2312","utf-8","水火不容");
// imagettftext($im,100,0,30,150,$col,$font,$come); //写 TTF 文字到图中
// imagegif($im,'new.gif');
// imagedestroy($im);
// die;


$aa='eyJnZXJlbmFsX25hbWUiOiLnlJ/lp5wiLCJzZWxsaW5nX3BvaW50Ijoi55Sf5aecIiwia2V5d29yZHMiOiLnlJ/lp5wiLCJmaWxlX2dvb2RzIjpbeyJmaWxlX3BhdGgiOiJodHRwczovL2ZpbGUubmV3b2ZmZW4uY29tLy90ZXN0L3lpamlhbmJhby8yMDIyMDkyMS92YXEzbXpuNnN5MWUzZWx0MTY2Mzc0NTM5MTc2OS5qcGciLCJ0eXBlIjoxMCwic29ydCI6MX1dLCJhdHRfdmFsIjpbeyJpdGVtSWQiOjEwMDAwODYsIml0ZW1OYW1lIjoi5oiQ5YiGIiwicHJvVHlwZSI6MSwiaXRlbVR5cGUiOjEsImlzTXVzdCI6MSwiaXNPcGVuIjoxLCJ2YWx1ZVRvdGFsTnVtIjpudWxsLCJzb3J0TnVtIjowLCJsaXN0VXZnYXZ2IjpudWxsLCJpc0RlbGV0ZSI6bnVsbCwicHJvVmFsdWUiOiLnlJ/lp5wiLCJwcm9JZCI6MTAwMDA2NiwicHJvVmFsdWVJZCI6bnVsbH0seyJpdGVtSWQiOjEwMDAwOTAsIml0ZW1OYW1lIjoi5rOo5oSP5LqL6aG5IiwicHJvVHlwZSI6MSwiaXRlbVR5cGUiOjUsImlzTXVzdCI6MSwiaXNPcGVuIjoxLCJ2YWx1ZVRvdGFsTnVtIjpudWxsLCJzb3J0TnVtIjowLCJsaXN0VXZnYXZ2IjpudWxsLCJpc0RlbGV0ZSI6bnVsbCwicHJvVmFsdWUiOiLkuI3lj6/ppa7phZIiLCJwcm9JZCI6MTAwMDA2NiwicHJvVmFsdWVJZCI6bnVsbH0seyJpdGVtSWQiOjEwMDAxMDcsIml0ZW1OYW1lIjoi5pyJ5pWI5pyfIiwicHJvVHlwZSI6MSwiaXRlbVR5cGUiOjMsImlzTXVzdCI6MSwiaXNPcGVuIjoxLCJ2YWx1ZVRvdGFsTnVtIjpudWxsLCJzb3J0TnVtIjowLCJsaXN0VXZnYXZ2IjpbeyJwcm9WYWx1ZUlkIjoxMDAwOTUzLCJwcm9UeXBlIjoxLCJwcm9WYWx1ZSI6IjI05Liq5pyIIiwic3RhdHVzIjoxLCJpc0RlbGV0ZSI6bnVsbH1dLCJpc0RlbGV0ZSI6bnVsbCwicHJvVmFsdWUiOiIyNOS4quaciCIsInByb0lkIjoxMDAwMDY2LCJwcm9WYWx1ZUlkIjpudWxsfSx7Iml0ZW1JZCI6MTAwMDEwNSwiaXRlbU5hbWUiOiLpo5/lk4Hkv53lgaXlsZ7mgKfpobkiLCJwcm9UeXBlIjoxLCJpdGVtVHlwZSI6MiwiaXNNdXN0IjoxLCJpc09wZW4iOjEsInZhbHVlVG90YWxOdW0iOm51bGwsInNvcnROdW0iOjAsImxpc3RVdmdhdnYiOlt7InByb1ZhbHVlSWQiOjEwMDA5MzAsInByb1R5cGUiOjEsInByb1ZhbHVlIjoi6YCC55So5LqO6aOO54Ot5oSf5YaSIiwic3RhdHVzIjoxLCJpc0RlbGV0ZSI6bnVsbH1dLCJpc0RlbGV0ZSI6bnVsbCwicHJvVmFsdWUiOiLpgILnlKjkuo7po47ng63mhJ/lhpIiLCJwcm9JZCI6MTAwMDA2NiwicHJvVmFsdWVJZCI6bnVsbH1dLCJhcHByb3ZhbCI6InBzc2EzMjEzMSIsImJyYW5kX2lkIjo5ODQsImZhY3RvcnlfaWQiOjI2NDc5LCJhdHRyX2lkIjoiIiwiaXNfZHJ1Z190eXBlIjozLCJpc19jaGxvcnByb21hemluZSI6MiwiY2F0ZV9pZHMiOlsxMDAwMDQyLDEwMDA0NzAsMTAwMDQ4MV0sImNob29zZVVuaXQiOnsidW5pdE5hbWUiOiLlhYsiLCJ1bml0SWQiOjd9LCJpbmRpY2F0aW9uc0l0ZW0iOiIiLCJwYXJhbV91c2UiOltdLCJkcnVnX3BhcmFtX2lkIjoiIiwiaXNfcGxhbnQiOjEsImNhbl91c2VfY291cG9uIjoxLCJpZF9jYXJkIjoyLCJpc2V4cHJlc3MiOjAsInNoYXJlX3RpdGxlIjoiIiwic2hhcmVfZGVzY3JpcHRpb24iOiIiLCJzYXBwX2ltZyI6W10sIndlY2hhdF9pbWciOltdLCJoYW5kbGUiOjEsIm11c2ljIjpbXSwiZmFsc2VfbG9va19yYW5kIjoiIiwid2VpeGluX3JlcGx5X2NvbnRlbnQiOiIiLCJnb29kc190YWciOiIiLCJ3ZWl4aW5fcmVwbHlfY29udGVudF9pZCI6MCwibGltaXRfdGltZSI6IiIsImxpbWl0X2RheSI6IiIsInBsYWNlX3RpbWUiOltdLCJ0aWNrZXRUeXBlIjoxLCJpc19yZXNlcnZhdGlvbiI6MiwiY29tbXVuaXR5X2lkIjoiIiwiY2F0ZWdvcnlfbmFtZSI6WyLmipfoj4zoja8iLCLmipfoj4zoja8iXSwiYnlfa25vdyI6IiIsImNvbnRlbnQiOiI8cD4zMjEzMTwvcD4iLCJtZWFsX3RvZ2V0aGVyX2J1eSI6IjIiLCJzdWJzY3JpYmUiOjIsIndlZWsiOltdLCJib29rX3JhbmdlIjoiIiwicmVzZXJ2YXRpb25fYXVkaXQiOjIsImlzX2ZvcmNlX2NvZGUiOjIsImlzX3ByZV9zaG9wcGluZyI6MSwiaXNfbWVhbCI6IjEiLCJtZWFsX2lzX3Nob3dfc3RvY2siOjIsIm1lYWwiOltdLCJpc19hdWRpdCI6IiIsImdpZnQiOltdLCJyZWZ1bmRfcm9vdCI6MywicmVmdW5kX29wZW4iOjIsInJlZnVuZF9yYXRpbyI6IiIsImxlYWRlcl9pZCI6IjAiLCJzZXJ2aWNlIjoxLCJzZXJ2aWNlX2RheSI6MCwic2VydmljZV9kYXRlIjoiIiwic2VydmljZV9kYXRlX29wZW4iOjIsInJlY190eXBlIjo0LCJjb2xsb2NhdGlvbiI6W10sImlzX3J4X2RydWciOjEsImdvb2RzX25hbWUiOiLnlJ/lp5wiLCJvZmZsaW5lX3Byb2R1Y3RzX2lkIjoiIiwiaXNfY2hhbmdlX3ByaWNlIjoxLCJza3VfbmFtZSI6IiIsImJhbGFuY2UiOnsicmViYXRlIjoyLCJtYXhfcmViYXRlX21vbmV5IjoiIiwiZ2l2ZV9pbnRlZ3JhbCI6IiJ9LCJidXlfdGlwIjoiMyIsImlzX3NldHRsZW1lbnQiOjEwLCJzZXR0bGVtZW50IjowLCJzYWxlX3R5cGUiOiIiLCJjYXNoYmFja19pZCI6MCwiY2F0ZWdvcnlfaWQiOls2OTAsNjk5XSwiYnVzX2lkIjpbXSwic19pZCI6IiIsImltYWdlIjpbeyJmaWxlX2lkIjo1OTAxMiwic3RvcmFnZSI6InFjbG91ZCIsImZpbGVfbmFtZSI6InRlc3QveWlqaWFuYmFvLzIwMjIwOTIxL3ZhcTNtem42c3kxZTNlbHQxNjYzNzQ1MzkxNzY5LmpwZyIsImZpbGVfdHlwZSI6ImltYWdlIiwiZmlsZV91cmwiOiJodHRwczovL2ZpbGUubmV3b2ZmZW4uY29tLyIsIm9yaWdpbm5hbWUiOiJ0ZXN0L3lpamlhbmJhby8yMDIyMDkyMS92YXEzbXpuNnN5MWUzZWx0MTY2Mzc0NTM5MTc2OS5qcGciLCJmaWxlX3BhdGgiOiJodHRwczovL2ZpbGUubmV3b2ZmZW4uY29tLy90ZXN0L3lpamlhbmJhby8yMDIyMDkyMS92YXEzbXpuNnN5MWUzZWx0MTY2Mzc0NTM5MTc2OS5qcGcifV0sImxvbmdfaW1hZ2UiOltdLCJzdHJhaWdodF9pbWFnZSI6W10sInZpZGVvIjpbXSwidHJhbnNwb3J0X3R5cGUiOjcwLCJzZXJ2ZSI6WyI0Il0sImdvb2RzX2RpeV9pZCI6IiIsImdyb3Vwc19kaXlfaWQiOiIiLCJzcGlrZV9kaXlfaWQiOiIiLCJ1cHBlcnRpbWUiOiIiLCJvcGVuX3NhbGVfdGltZSI6IiIsInVuZGVyX3RpbWUiOiIiLCJpc191bmRlciI6MTAsImxpbWl0X2J1eSI6MjAsImxpbWl0X251bSI6IiIsInZhbGlkaXR5IjoxMCwidmFsaWRpdHlfZGF5IjoiIiwidmFsaWRpdHlfdGltZSI6IiIsImV4cGlyZV90aXBzIjoiIiwiaXNfZXhwaXJlX3RpcHMiOjIsInBhcmFtIjoiIiwidXNlX3BhcmFtIjoiIiwic3BlY190eXBlIjoyMCwiaXNfdmlkZW9fbGlzdCI6IjIiLCJpc192aWRlb19kYW5tYWt1IjoyLCJoaWRlX2luZm8iOltdLCJza3UiOlt7ImlkIjoiaXRsOWh1b29tdmE4ZyIsInRpdGxlIjoiMTBnIiwic2t1X2ltYWdlIjoiaHR0cHM6Ly9maWxlLm5ld29mZmVuLmNvbS8vdGVzdC95aWppYW5iYW8vMjAyMjA5MjEvdmFxM216bjZzeTFlM2VsdDE2NjM3NDUzOTE3NjkuanBnIiwiZ29vZHNfc2t1X2lkIjoiIiwiaW1hZ2VfaWQiOjU5MDEyLCJza3VfaWQiOiIiLCJnb29kc19hdHRyIjpbeyJpdGVtSWQiOjEwMDAwODMsIml0ZW1OYW1lIjoi6aKc6ImyIiwicHJvSWQiOjEwMDAwNjQsImxpc3RVdmdhdnYiOlt7InByb1ZhbHVlSWQiOiIxMDAxMDUwLTAtMSIsInByb1ZhbHVlIjoiMTBnIn1dfV0sIm9mZmxpbmVfcHJvZHVjdHNfaWQiOiIzNDMyIiwiYmFyX2NvZGUiOiIzNDMyIiwiZ29vZHNfbm8iOiIzNDMyIiwibGluZV9wcmljZSI6IjEwMCIsImdvb2RzX3ByaWNlIjoiMC4xIiwic3RvY2tfbnVtIjoiMTAwIiwiZ29vZHNfd2VpZ2h0IjoiMSIsImlzX3Nob3ciOiIxIiwidW5pdF9pZCI6NywidW5pdF9uYW1lIjoi5YWLIn1dLCJndWlkaW5nX3RpdGxlIjoiIiwiZGVkdWN0X3N0b2NrX3R5cGUiOiIiLCJkZWxpdmVyeV9pZCI6MSwiZm9ybV9pZCI6IiIsImlzenQiOjEsImdvb2RzX3N0YXR1cyI6MTAsImlzX29wZW5fc2FsZSI6MCwic2FsZXNfaW5pdGlhbCI6IiIsImdvb2RzX3NvcnQiOiIiLCJhZnRlcl9zYWxlcyI6MTAsImlzX3NhbGVzIjoxLCJpc19zdG9jayI6MSwidmlydHVhbF9zYWxlcyI6IiIsImlzX2xpa2UiOjEsInZpcnR1YWxfbGlrZSI6IiIsImFmdGVyX3NhbGVzX2RldGFpbCI6eyJzdGF0dXMiOiIxMCIsImFsb3dfaG91c2UiOiIiLCJhbG93X2RheSI6IiJ9LCJwcm90ZWN0IjoxLCJwcm90ZWN0X3RpbWUiOjEsImNvZGVfdHlwZSI6MSwiY29kZV9saW1pdCI6MSwiaXNfY29kZSI6MSwiY29kZV9saW1pdF93aGV0aGVyIjowLCJjaG9vc2VzaG9wIjozLCJjb2RlX251bSI6MSwiZWZmZWN0IjoxMCwidmFsaWRpdHlfc3RhcnR0aW1lIjpbIiIsIiJdLCJuZWVkX2RldmljZSI6MCwiZGV2aWNlX2lkIjoiIiwiZnJvbSI6W3sidHh0Ijoi5aeT5ZCNIiwibmFtZSI6Im5hbWUiLCJzd2l0Y2giOnRydWV9LHsidHh0Ijoi5oCn5YirIiwibmFtZSI6InNleCIsInN3aXRjaCI6dHJ1ZX0seyJ0eHQiOiLmiYvmnLrlj7fnoIEiLCJuYW1lIjoicGhvbmUiLCJzd2l0Y2giOnRydWV9LHsidHh0Ijoi5bm06b6EIiwibmFtZSI6ImFnZSIsInN3aXRjaCI6dHJ1ZX1dLCJzdG9yZV9pZCI6W10sInN1YnNjcmliZVRpbWUiOltdLCJkcnVnU2VhcmNoIjoiIiwic2VsRHJ1Z0RhdGEiOiIiLCJwYXJhbXMiOiIiLCJzcGVjX2dvb2RzX2xpc3QiOlt7Iml0ZW1JZCI6MTAwMDA4NywiaXRlbU5hbWUiOiLnm5IiLCJwcm9UeXBlIjozLCJpdGVtVHlwZSI6NCwiaXNNdXN0IjoxLCJpc09wZW4iOjEsInZhbHVlVG90YWxOdW0iOm51bGwsInNvcnROdW0iOjAsImxpc3RVdmdhdnYiOlt7InByb1ZhbHVlSWQiOjEwMDA5MjcsInByb1R5cGUiOjMsInByb1ZhbHVlIjoi55uSIiwic3RhdHVzIjoxLCJpc0RlbGV0ZSI6bnVsbH1dLCJpc0RlbGV0ZSI6bnVsbCwicHJvVmFsdWUiOm51bGwsInByb0lkIjoxMDAwMDY1LCJwcm9WYWx1ZUlkIjpudWxsLCJjaG9vc2VCb3giOlsi55uSIl19LHsiaXRlbUlkIjoxMDAwMDkzLCJpdGVtTmFtZSI6IumAmueUqOWQjeensCIsInByb1R5cGUiOjMsIml0ZW1UeXBlIjoxLCJpc011c3QiOjEsImlzT3BlbiI6MSwidmFsdWVUb3RhbE51bSI6bnVsbCwic29ydE51bSI6MCwibGlzdFV2Z2F2diI6bnVsbCwiaXNEZWxldGUiOm51bGwsInByb1ZhbHVlIjoi55Sf5aecLSIsInByb0lkIjoxMDAwMDY1LCJwcm9WYWx1ZUlkIjpudWxsfSx7Iml0ZW1JZCI6MTAwMDA5NywiaXRlbU5hbWUiOiLkuLvmsrvlip/og70iLCJwcm9UeXBlIjozLCJpdGVtVHlwZSI6NCwiaXNNdXN0IjoxLCJpc09wZW4iOjEsInZhbHVlVG90YWxOdW0iOm51bGwsInNvcnROdW0iOjAsImxpc3RVdmdhdnYiOlt7InByb1ZhbHVlSWQiOjEwMDA5NDQsInByb1R5cGUiOjMsInByb1ZhbHVlIjoi5YeJ6KGAIiwic3RhdHVzIjoxLCJpc0RlbGV0ZSI6bnVsbH1dLCJpc0RlbGV0ZSI6bnVsbCwicHJvVmFsdWUiOm51bGwsInByb0lkIjoxMDAwMDY1LCJwcm9WYWx1ZUlkIjpudWxsLCJjaG9vc2VCb3giOlsi5YeJ6KGAIl19XX0=';
$data = json_decode(base64_decode($aa), true);
echo '<pre>';
print_R($data);
echo '</pre>';
exit;
echo '<pre>';
var_dump($now_date>= 9 && $now_date<=18);
echo '</pre>';
exit;

$aa='{"totalChanel":{"wxapp":{"open":"true","value":"\u5fae\u4fe1\u5c0f\u7a0b\u5e8f"},"H5":{"open":"true","value":"H5"},"wechat":{"open":"true","value":"\u5fae\u4fe1\u516c\u4f17\u53f7"},"aliapp":{"open":"false","value":"\u652f\u4ed8\u5b9d\u5c0f\u7a0b\u5e8f"},"baiduapp":{"open":"false","value":"\u767e\u5ea6\u5c0f\u7a0b\u5e8f"},"toutiaoapp":{"open":"false","value":"\u5934\u6761\u5c0f\u7a0b\u5e8f"},"android":{"open":"false","value":"\u5b89\u5353"},"ios":{"open":"false","value":"\u82f9\u679c"}},"totalPlugins":{"coupon":{"open":"true","value":"\u4f18\u60e0\u5238"},"message":{"open":"true","value":"\u6d88\u606f\u63a8\u9001"},"spike":{"open":"true","value":"\u79d2\u6740"},"member":{"open":"true","value":"\u4f1a\u5458\u5361"},"dealer":{"open":"true","value":"\u5206\u9500"},"groups":{"open":"true","value":"\u62fc\u56e2"},"luck":{"open":"true","value":"\u62bd\u5956"},"diyposter":{"open":"true","value":"\u81ea\u5b9a\u4e49\u6d77\u62a5"},"bargain":{"open":"false","value":"\u780d\u4ef7"},"redenvelopes":{"open":"false","value":"\u7ea2\u5305"},"cgp":{"open":"false","value":"\u793e\u533a\u56e2\u8d2d"},"theme":{"open":"true","value":"\u4e3b\u9898\u914d\u8272"},"form":{"open":"true","value":"\u81ea\u5b9a\u4e49\u8868\u5355"},"praise":{"open":"false","value":"\u96c6\u8d5e\u62d3\u5ba2"},"examination":{"open":"true","value":"\u4f53\u68c0"},"cart":{"open":"true","value":"\u8d2d\u7269\u8f66"}}}';
$list =json_decode($aa,true);
echo '<pre>';
print_R($list);
echo '</pre>';
exit;
$product_list=[
  [
	'yyc_user_id' =>  '10381',
	'spu_name'  =>'复方氨基酸注射液(18AA)',
	'it_number' =>  '80',
	'spu_factory' =>  '石家庄四药有限公司',
	'factory_id'  =>  0,
	'sup_approval_number' =>  '国药准字H20013240',
	'spu_brand' =>  '123',
	'product_tag' =>    0,
	'is_spirit_drug'  =>  0,
	'drug_type' =>  0,
	'sku_specs' =>  '500ml;25g/20瓶',
	'sku_stock' =>  0,
	'sku_unit'  =>  '瓶',
	'sku_regional_price'  =>  '6.0621',
	'spu_pic' =>  '',
    ],
    [
	'yyc_user_id' =>  '10381',
	'spu_name'  =>'鼻炎片',
	'it_number' =>  '1',
	'spu_factory' =>  '武汉中联药业集团股份有限公司',
	'factory_id'  =>  0,
	'sup_approval_number' =>  'ZZ-0382-鄂卫药准字(1981)第001138号',
	'spu_brand' =>  '片剂',
	'product_tag' =>    1,
	'is_spirit_drug'  =>  1,
	'drug_type' =>  0,
	'sku_specs' =>  '200ml/24盒',
	'sku_stock' =>  44,
	'sku_unit'  =>  '片剂',
	'sku_regional_price'  =>  '20',
	'spu_pic' =>  'https://file.newoffen.com//uvp/yyc/20211126/dfij1rxryi3exos11637916892334.jpg',
    ],

    /*

[yyc_user_id] => 10085
[id] => 1
[spu_name] => 鼻炎片
[it_number] => 2
[spu_factory] => 武汉中联药业集团股份有限公司
[factory_id] => 7
[spu_approval_number] => ZZ-0382-鄂卫药准字(1981)第001138号
[spu_brand] => 片剂
[product_tag] => 1
[is_spirit_drug] => 1
[drug_type] => 1
[sku_specs] =>
[sku_stock] => 444
[sku_unit] => 片剂
[sku_regional_price] => 20
[spu_pic] => https://file.newoffen.com//uvp/yyc/20211126/dfij1rxryi3exos11637916892334.jpg
[spu_details] => 123456
     */

];

foreach($product_list as $goods){
	 $data = erpGoodsData($goods,'goods');
	 echo json_encode($data,JSON_UNESCAPED_UNICODE);
	 die;
}


//转换Java的erp数据信息
function erpGoodsData($goods_info= [] , $fun_type='goods'){
	if(!$goods_info){
		return array('error_no'=>'300010','error_msg'=>'参数不全');
	}

	if(!in_array($fun_type , ['goods' ,'sku','price'])) {
		return array('error_no'=>'300011','error_msg'=>'同步类型错误');
	}
	if( $fun_type == 'goods'){
		$erpGoods['userId'] = $goods_info['yyc_user_id'];//用户id
		$erpGoods['agent'] = 1;//是否代理商品 1 不是 2是
		$erpGoods['approvalNo'] =$goods_info['sup_approval_number'];//批准文号
		$erpGoods['attrParametList'] =[];//规格名称,用于列表展示
		$erpGoods['cateId'] = 0;//三级类目id
		$erpGoods['cateIdOne'] = 0;//一级商品类目ID
		$erpGoods['cateIdSecond'] = 0;//二级类目
		$erpGoods['cateName'] = '';//三级类目名称
		$erpGoods['deliverType'] = 1; //发货方式：1厂家发货 2.商品发货（商品是有谁发货）
		$erpGoods['domainId'] = 'P1640413709799E3D2';//域id
		$erpGoods['goodsBrand'] = $goods_info['spu_brand'] ?? 0;//厂家品牌
		$erpGoods['goodsBrandId'] = 0;//商品品牌id
		$erpGoods['goodsClassify'] = $goods_info['drug_type']; //药品分类：1西药；2中药；3中成药；0其他
		$erpGoods['goodsExplainList'] =  [];//三、说明书
		$imageList[] =[
			'goodsImgUrls'	=>['https://file.newoffen.com/prod/wms/erp/20220803/rfl8dbvo3dtwghdl1659515039389.png'],
			'imgType'	=>1,
		];
		$erpGoods['goodsImageTextList'] =$imageList;
		$erpGoods['goodsInfo'] = "<p>" . $goods_info['spu_name'] . "</p>";//商品详情
		$erpGoods['goodsName'] = $goods_info['spu_name'];//商品名称
		$specList[]=[
			'areaPriceSetList'=>[],
			'firstOptionId'=>0,
			'firstOptionVal'	=>$goods_info['sku_specs'],
			'secondOptionId'	=>'',
			'secondOptionVal'	=>'',
			'specImage'	=>'https://file.newoffen.com/prod/wms/erp/20220803/rfl8dbvo3dtwghdl1659515039389.png',
			'stockNum'	=> 0,
		];
		$erpGoods['goodsSkuSpecsList'] =$specList;
		//销售属性（多规格--商品规格）
		$erpGoods['goodsSpecsList']=[
			[
				'itemId'	=> 0,
				'itemName'	=>'',
				'proValueList'	=>[
					[
						'proValue'	=> $goods_info['sku_specs'],
						'proValueId'	=> 0,
					]

				]
			]
		]; //属性自增id
		$erpGoods['goodsType'] = $goods_info['product_tag'];//处方类型 产品类型 1-处方药，2-甲类非处方药，3-乙类非处方药，4-保健品，0-其他
		$erpGoods['goodsUnitId'] = 0;//药品单位id
		$erpGoods['goodsUnitName'] = $goods_info['sku_unit'];
		$erpGoods['isExpiring'] = 0;//是否近有效期
		$erpGoods['isSpiritDrug'] =$goods_info['is_spirit_drug'] ? 1: 0; //是否为精神用药
		$erpGoods['manufactorId'] = $goods_info['factory_id'] ?? 0;
		$erpGoods['manufactorName'] = $goods_info['spu_factory'];//厂家名称
		$erpGoods['minSaleNum'] = 10; //起批数量
		$erpGoods['priceLimit'] = 1; //是否限价： 是否限价 1 不限制 2  限制
		$erpGoods['priceType'] =  1;//价格类型：1云仓销售价格（商家不能改价）；2厂家供货价（商家自定义销售价
		$erpGoods['referencePrice'] = 0; //市场参考价+```
		$erpGoods['retailPrice'] = 0;//建议零售价
		$erpGoods['validityEndtime'] =time();//有效期结束日期
		$erpGoods['validityStarttime'] = strtotime(date('Y-m-d H:i:s', strtotime("+2 year")));;//validityStarttime
		$erpGoods['thirdProductCode'] = $goods_info['it_number'];//第三方的商品编码
		$erpGoods['transferSource'] = 1;//代表第三方同步商品 1：erp同步、2：  其他
	}else{
		//判断库存和价格商品数据封装
		/*
			"areaId": "",
			"areaPrice": 0,
			"businessId": 0,
			"domainId": "",
			"goodsId": 0,
			"requestHeader": "",
			"reviewId": 0,
			"skuId": 0,
			"specsId": 0,
			"stockNum": 0,
			"updateModify": 0,
			"userId": 0

		 */
		$erpGoods['userId'] = $goods_info['yyc_user_id']; //用户id
		$erpGoods['areaId']  =0;//区域id
		$erpGoods['areaPrice'] = $fun_type == 'price' ?  $goods_info['new_price'] : 0;//价格同步
		$erpGoods['businessId'] = '';//商家id
		$erpGoods['domainId'] = '';//域id
		$erpGoods['goodsId'] = 0;//商品id
		$erpGoods['thirdProductCode'] = $goods_info['it_number'];//第三方编码
		$erpGoods['requestHeader'] = ''; //建议零售价
		$erpGoods['reviewId'] = 0; //商品审核Id，有值则修改，无值则新增
		$erpGoods['skuId'] = 0; //商品skuid
		$erpGoods['specsId'] = 0;//商品规格明细id
		$erpGoods['stockNum'] = $fun_type =='stock' ? $goods_info['new_stock'] : 0;//同步库存信息
		$erpGoods['updateModify'] = 0;
	}
	return $erpGoods;
}


$servers = array(
    'tcp://172.21.0.7:16380',
    'tcp://172.21.0.2:16380',
    'tcp://172.21.0.16:16380',
    'tcp://172.21.0.7:16381',
    'tcp://172.21.0.2:16381',
    'tcp://172.21.0.16:16381',
);

//获取网卡的MAC的地址
$asset  = getMAC();
echo '<pre>';
print_R($asset);
echo '</pre>';
exit;
function getMAC() {
    @exec("ipconfig /all",$array);
     for($Tmpa;$Tmpa<count($array);$Tmpa++){
      if(eregi("Physical",$array[$Tmpa])){
           $mac=explode(":",$array[$Tmpa]);
           return $mac[1];
      }
    }
}

phpinfo();
die;
$aa =new Redis();
echo '<pre>';
print_R($aa);
echo '</pre>';
exit;
$client = new Predis\Client($servers, array('cluster' => 'redis'));
echo '<pre>';
print_R($client);
echo '</pre>';
exit;
?>