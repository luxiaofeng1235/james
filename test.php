<?

require_once(__DIR__.'/library/init.inc.php');

use QL\QueryList;##引入querylist的采集器



$url = 'https://www.baidu.com';

$proxy = '202.63.172.220';
$proxyauth = '3e42fd4e:a8c776b5';


$list=MultiHttp::curlGet(['https://www.baode.cc/ob/18767/6992970.html','https://www.baode.cc/ob/18767/6998393.html','https://www.baode.cc/ob/101785/19252540.html','https://www.baode.cc/ob/101785/19253327.html','https://www.baode.cc/ob/101785/19321012.html','https://www.baode.cc/ob/101768/19252546.html','https://www.baode.cc/ob/101768/19252545.html','https://www.baode.cc/ob/101768/19252546.html','https://www.baode.cc/ob/101768/19252550.html','https://www.baode.cc/ob/101768/19252553.html','https://www.baode.cc/ob/101768/19253327.html','<a href="https://www.baode.cc/ob/101768/19279974.html','https://www.baode.cc/ob/101768/19293205.html','https://www.baode.cc/ob/101768/19321012.html','https://www.baode.cc/ob/101768/19345822.html','https://www.baode.cc/ob/101768/19400348.html','https://www.baode.cc/ob/101768/19428010.html','https://www.baode.cc/ob/101768/19435965.html','https://www.baode.cc/ob/101768/19474937.html','https://www.baode.cc/ob/101768/19475380.html','https://www.baode.cc/ob/101768/19485825.html','https://www.baode.cc/ob/101768/19485825.html'],[]);

$rules = array(
    'text' => array('.bookname h1','text'),//采集class为two下面的超链接的链接
    // 'content'   =>array('#content','html'),
);
$newdata=[];
foreach($list as $key =>$html){
    $data = QueryList::html($html)->rules($rules)->query()
        ->getData();
    $list = $data->all();
    $newdata[] =$list;
}
echo '<pre>';
print_R($newdata);
echo '</pre>';
exit;

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
// curl_setopt($ch, CURLOPT_PROXYPORT, '47394');
// curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HEADER, 0);

// $curl_scraped_page = curl_exec($ch);
// $httpcode = curl_getinfo($ch);
// echo '<pre>';
// print_R($curl_scraped_page);
// echo '</pre>';
// exit;

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