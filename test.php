<?

require_once(__DIR__.'/library/init.inc.php');
use QL\QueryList;##引入querylist的采集器

$str = "第四百二十六章 我们能赢2_2.txt";
$newStr = preg_replace('# #', '', $str);


$a= file_put_contents('./'.$newStr,'11112');
echo '<pre>';
var_dump($a);
echo '</pre>';
exit;
$a='第四百二十六章 我们能赢（2/2）.txt';
$aa = trim($a);
echo '<pre>';
var_dump($aa);
echo '</pre>';
exit;
$url = 'http://www.paoshu8.info/files/article/image/195/195081/195081s.jpg';
$savePath = './image.jpeg'; // 保存路径及文件名

// 获取远程图片内容并写入本地文件
if (($content = file_get_contents($url)) !== false) {
    if (file_put_contents($savePath, $content) !== false) {
        echo "成功保存图片！";
    } else {
        echo "无法保存图片到指定位置。";
    }
} else {
    echo "无法从指定URL获取图片内容。";
}
die;




//获取代理的配置信息
$proxy_data = getProxyInfo($redis_data);
$url = 'http://www.paoshu8.info/185_185961/';
$proxy = $proxy_data['ip'];
$port = $proxy_data['port'];
$proxyauth = $proxy_data['username'].':'.$proxy_data['password'];
// //https://202.63.172.110:11890:1eb2ab2f:fb1abba5
// //1eb2ab2f:fb1abba5

// // $res = MultiHttp::curlGet(['http://www.paoshu8.info/92_92763/'],null,true);
// // echo '<pre>';
// // print_R($res);
// // echo '</pre>';
// // exit;

$res = MultiHttp::curlGet(['http://www.paoshu8.info/185_185961/'],[],true);
echo '<pre>';
print_R($res);
echo '</pre>';
exit;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_PROXYPORT, $port);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);

$curl_scraped_page = curl_exec($ch);
$httpcode = curl_getinfo($ch);
echo '<pre>';
var_dump($httpcode);
echo '</pre>';
exit;

if(curl_exec($ch) === false)
{
    echo 'Curl error: ' . curl_error($ch);
}
else
{
    echo '操作完成没有任何错误';
}
die;
echo '<pre>';
var_dump($curl_scraped_page);
echo '</pre>';
exit;
;
echo '<pre>';
print_R($curl_scraped_page);
echo '</pre>';
exit;

$url ='https://www.baidu.com';
$rs = webRequest($url,'GET',[],[]);
echo '<pre>';
print_R($rs);
echo '</pre>';
exit;

$opts = [
    // Set the http proxy
    'proxy' => '202.63.172.220:47394',
    //Set the timeout time in seconds
    'timeout' => 30,
     // Fake HTTP headers
    'headers' => [
        'Referer' => 'https://www.baidu.com/',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        // 'Accept'     => 'application/json',
        'X-Foo'      => ['Bar', 'Baz'],
        'Cookie'    => 'abc=111;xxx=222'
    ]
];
// echo '<pre>';
// print_R($opts);

//hk01.hostmjj.net:28620:n12ngyzr:a1htrmns
$urlParams = ['param1' => 'testvalue','params2' => 'somevalue'];
$ql =QueryList::getInstance();
$res  = $ql->get('http://www.baidu.com',[],$opts);
echo '<pre>';
var_dump($res);
echo '</pre>';
exit;

// $html = <<<STR
// <div class="bH7m7 "><div class="_2bKNC"><div class="_1HGmt"><a href="https://author.baidu.com/home?from=bjh_article&amp;app_id=1747184539382150" target="_blank"><span data-testid="author-name" class="_2gGWi">超人坎娱</span></a><span class="_2sjh9" data-testid="updatetime">2023-06-12 02:41</span><!--18--><span class="_2Wctx" data-testid="address">四川</span><!--19--></div></div><!--20--></div>
// STR;

// for ($i=0; $i <100 ; $i++) {
//     $url = 'https://www.souduw.com/api/novel/chapter/transcode.html?novelid=327782&chapterid=19&page=1';
//     $headers  = [
//         'headers' => [
//             'Referer'             =>    'https://www.souduw.com/GuoYunTeXu_XianZhanHouZou/19_1.html',
//             'Cache-Control'       =>    'Cache-Control',
//             'X-Requested-With'    =>    'XMLHttpRequest',

//         ]
//     ];
//     $ql =QueryList::getInstance();
//     $response = $ql::postJson($url,[],$headers);
//     $info = $response->getHtml();
//     $info = str_replace("}</p>",'}',$info);

//     $info = str_replace(array("\r\n","\r","\n"),"",$info);
//     $s = json_decode($info,true);
//     echo '<pre>';
//     print_R($s);
//     echo '</pre>';
//     exit;
//     echo "执行了第".($i+1)."次的数据信息\r\n";
// }
// die;



 // $url = 'https://www.qiuxiaoshuo.org/book/2756.html';
 // echo '<pre>';
 // print_R(file_get_contents($url));
 // echo '</pre>';
 // exit;
 //    $rules = array(
 //        // 'text' => array('#one','text'),//采集class为two下面的超链接的链接
 //        // 'link'=> array('.two>a','href'),//采集class为two下面的下的文字
 //        // 'link_name' =>array('.two>a','text'),//采集class为two下面的连接地址里的文字
 //        // 'img'=> array('.two>img:eq(1)','src'),//采集two标签里的第二个图片的src的地址
 //        // 'other' => array('span','html'),//采集span标签里的html
 //        'title' =>array('#h1','text'),

 //        // 'recommand' =>array('.lm','text'),
 //        'content'   =>array('#txtContent','html'),//采集div的id为one的em下的内容
 //        // 'ss'        =>array('._2Wctx','text'),
 //        // 'time'  =>array('._2sjh9','text'),
 //        // 'href'  =>array('.bookname h1','text'),

 //        // 'img'   =>array('.f_center img','src'),
 //    );

$ql = QueryList::getInstance();
echo '<pre>';
print_R($ql);
echo '</pre>';
exit;
$ql->browser('https://m.toutiao.com',false,[
    '--proxy' => '192.168.1.42:8080',
    '--proxy-type' => 'http'
]);
// $data = QueryList::get($url)->rules($rules)->query()
//     ->getData();
//     echo '<pre>';
//     var_dump($data->all());
//     echo '</pre>';
//     die;
?>
