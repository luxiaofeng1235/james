<?php
///querylist测试程序
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once(__DIR__.'/library/init.inc.php');
use QL\QueryList;
use QL\Ext\CurlMulti;
// $aa = guzzleHttp::multi_req([
//     'https://www.xsw.tw/book/1263567/231595388.html',
//     'https://www.xsw.tw/book/1263567/231595256.html',
//     'https://www.xsw.tw/book/1322907/247788880.html',
//     'https://www.xsw.tw/book/1322907/236948732.html',
//     'https://www.xsw.tw/book/1322907/236948792.html',
//     'https://www.xsw.tw/book/1322907/236949357.html',
//     'https://www.xsw.tw/book/1322907/236949460.html',
//     'https://www.xsw.tw/book/1322907/237194028.html',
//     'https://www.xsw.tw/book/1322907/236949492.html',
//     'https://www.xsw.tw/book/1322907/236949828.html',
//     'https://www.xsw.tw/book/905845/248207375.html',
//     'https://www.xsw.tw/book/905845/248190253.html',
//     'https://www.xsw.tw/book/905845/248175273.html',
//     'https://www.xsw.tw/book/905845/248163971.html',
//     'https://www.xsw.tw/book/905845/248149626.html',
//     'https://www.xsw.tw/book/905845/248136859.html',
//     'https://www.xsw.tw/book/1129080/248189962.html',
//     'https://www.xsw.tw/book/1129080/248176604.html',
//     'https://www.xsw.tw/book/1129080/248176602.html',
//     'https://www.xsw.tw/book/1129080/248176600.html'
// ]);
// echo '<pre>';
// print_R($aa);
// echo '</pre>';
// exit;

$ql = QueryList::getInstance();
$ql->use(CurlMulti::class);
https://www.payeasy.com.tw/PWelfareWeb/COMMON/invitation/media/media_20240117.html
$ql->rules([
    // 'title' => ['.pic img','src'],
    // 'link' => ['h3 a','href']
])->curlMulti([
     'https://www.payeasy.com.tw/PWelfareWeb/COMMON/invitation/media/media_20240117.html',
])->success(function (QueryList $ql,CurlMulti $curl,$r){
    echo '<pre>';
    var_dump($r);
    echo '</pre>';
    exit;
    echo "Current url:{$r['info']['url']} \r\n";
    $data = $ql->query()->getData();
    print_r($data->all());
})->start();
//使用过滤
// $html =<<<STR
//     <div id="content">

//         <span class="tt">作者：xxx</span>

//         这是正文内容段落1.....

//         <span>这是正文内容段落2</span>

//         <p>这是正文内容段落3......</p>

//         <span>这是广告</span>
//         <p>这是版权声明！</p>
//     </div>
// STR;


// $html =<<<STR
//     <div id="demo">
//         xxx
//         <a href="/yyy">链接一</a>
//         <a href="/zzz">链接二</a>
//     </div>
// STR;

// $baseUrl = 'http://xxx.com';

//获取id为demo的元素下的最后一个a链接的链接和文本
//并补全相对链接

//方法一

// $ql = QueryList::get('http://www.baidu.com/s?wd=QueryList')->rules([
//     'title'=>array('h3','text'),
//     'link'=>array('h3>a','href')
// ]);

// $str = '<meta http-equiv="mobile-agent" content="format=html5; url=https://m.xsw.tw/1229150/226958802.html">';
// preg_match('/url=([^&=]+\.html)/',$str,$aa);
// echo '<pre>';
// print_R($aa);
// echo '</pre>';
// exit;


// // DOM解析规则
// $rules = [
//      //设置了内容过滤选择器
//     'content' => ['#content','html','-.tt -span:last -p:last']
// ];
// $rt = QueryList::rules($rules)->html($html)->query()->getData();


// //////////////获取文章的正文和内容信息
// $reg = [
//     'content'   =>['#content','html'],
//     'banner'    =>['.bread-crumbs li:eq(3)','text'],
//     'page'      =>['meta[http-equiv="mobile-agent"]','content'],
// ];

/**
$rt  = QueryList::get('https://www.xsw.tw/book/1229150/226958802.html',[],
    [
        // 'proxy' => 'socks5://43.152.113.72:11295',
        //设置超时时间，单位：秒
        'timeout' => 30,
    ]
);
$item = $rt
            ->rules($reg)
            // ->removeHead()
            ->query()
            ->getData(function($item){
                preg_match('/url=([^&=]+\.html)/',$item['page'],$matches);
                $item['page'] = $matches[1] ?? '';
                return $item;
            });
$item = $item->all();
dd($item);

$content = $item['content'] ?? '';
if(empty($content)){
    echo "获取数据失败\r\n";
}else{
    $str = fantiCovert($content);
    //转换程=为实体标签
    $string = html_entity_decode($str);
    $replace_str = '/<center.*?>.*?<\/center>/ism';
    $t = preg_replace($replace_str, '', $string);
    $store_content = str_replace("<p>",'',$t);
    $store_content = str_replace("</p>",'',$store_content);
    //把br替换成\n标签存储
    $store_content = str_replace("<br>","\n",$store_content);
    echo '<pre>';
    print_R($store_content);
    echo '</pre>';
    exit;
    file_put_contents('nginx.txt',$store_content);
}
**/


/////////////获取章节列表信息
// $rules =[
//     'chaper_name' =>['a','text'],
//     'chaper_link'   =>['a','href','',function($content){
//         $baseUrl = 'https://www.xsw.tw';
//         return $baseUrl.$content;
//     }],
// ];
// //采集章节列表信息
// $range  = '.liebiao li';
// $rt = QueryList::get('https://www.xsw.tw/book/230000/');
// $item = $rt->rules($rules)
//             ->range($range)
//             ->query()
//             ->getData();



//自动获取首页信息并爬取到本地
// $page = 'https://www.xsw.tw/allvisit_1.html';
// $reg = [
//     'img' =>['.pic img','src','',function($return) use($page) {
//          if(!strstr($return, 'https')){
//             $url = parse_url($page);
//             $referer = $url['scheme'] . '://'.$url['host'];
//             $return  =$referer .$return;
//          }
//          return $return;
//     }],
//     'title' =>['.title a','text'],
//     'author'    =>  ['.title span','text'],
//     'update_zhangjie'   =>['.sys a','text'],
//     'intro' =>['.intro','text'],
//     'chapter_link'  =>  ['.pic a','href','',function($item)  use($page){
//             $url = parse_url($page);
//             $referer = $url['scheme'] . '://'.$url['host'];
//             return $referer . $item;
//     }],
// ];
// $range  ='#alistbox';
// $rt = QueryList::get($page);
// $list = $rt->rules($reg)
//             ->range($range)
//             ->query()
//             ->getData(function($data){
//                 foreach($data as &$val){
//                     $val  = traditionalCovert($val);
//                 }
//                 //过滤作者
//                 $data['author'] =str_replace('作者：','',$data['author']);
//                 // //保存图片
//                 // $localSrc = 'image/'.md5($data['img']).'.jpg';
//                 // $stream = webRequest($data['img'],'GET');
//                 // file_put_contents($localSrc,$stream);
//                 //后续可以使用生成后的图片信息
//                 return $data;
//             });
// $list = $list->all();
// echo '<pre>';
// print_R($list);
// echo '</pre>';
// exit;



///多线程采集

?>