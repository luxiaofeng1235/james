<?

require_once(__DIR__.'/library/init.inc.php');

use QL\QueryList;##引入querylist的采集器

// $html = <<<STR
// <div class="bH7m7 "><div class="_2bKNC"><div class="_1HGmt"><a href="https://author.baidu.com/home?from=bjh_article&amp;app_id=1747184539382150" target="_blank"><span data-testid="author-name" class="_2gGWi">超人坎娱</span></a><span class="_2sjh9" data-testid="updatetime">2023-06-12 02:41</span><!--18--><span class="_2Wctx" data-testid="address">四川</span><!--19--></div></div><!--20--></div>
// STR;

for ($i=0; $i <100 ; $i++) {
    $url = 'https://www.souduw.com/api/novel/chapter/transcode.html?novelid=327782&chapterid=19&page=1';
    $headers  = [
        'headers' => [
            'Referer'             =>    'https://www.souduw.com/GuoYunTeXu_XianZhanHouZou/19_1.html',
            'Cache-Control'       =>    'Cache-Control',
            'X-Requested-With'    =>    'XMLHttpRequest',

        ]
    ];
    $ql =QueryList::getInstance();
    $response = $ql::postJson($url,[],$headers);
    $info = $response->getHtml();
    $info = str_replace("}</p>",'}',$info);

    $info = str_replace(array("\r\n","\r","\n"),"",$info);
    $s = json_decode($info,true);
    echo '<pre>';
    print_R($s);
    echo '</pre>';
    exit;
    echo "执行了第".($i+1)."次的数据信息\r\n";
}
die;






for ($i=0; $i < 50; $i++) {
    $url = 'https://www.163.com/news/article/IRGEBJ98000189FH.html?clickfrom=w_yw';
    $rules = array(
        // 'text' => array('#one','text'),//采集class为two下面的超链接的链接
        // 'link'=> array('.two>a','href'),//采集class为two下面的下的文字
        // 'link_name' =>array('.two>a','text'),//采集class为two下面的连接地址里的文字
        // 'img'=> array('.two>img:eq(1)','src'),//采集two标签里的第二个图片的src的地址
        // 'other' => array('span','html'),//采集span标签里的html
        'tdk'   =>array('.post_title','text'),//采集div的id为one的em下的内容
        // 'ss'        =>array('._2Wctx','text'),
        // 'time'  =>array('._2sjh9','text'),
        'href'  =>array('.post_info a','href'),

        'img'   =>array('.f_center img','src'),
    );
    $data = QueryList::get($url)->rules($rules)->query()
    ->getData();
    echo '<pre>';
    var_dump($rules);
    echo '</pre>';

    echo "获取数据的第".($i+1)."次</br>";
    echo "<hr/>";
}
die;

$data = QueryList::get('https://baijiahao.baidu.com/s?id=1768432743460355484&wfr=spider&for=pc')
    // 设置采集规则
    ->rules([
        'title'=>array('.q1J1i','text'),
        // 'link'=>array('h3>a','href')
    ])
    ->query()->getData();
echo '<pre>';
print_R($data);
echo '</pre>';
exit;
echo "<pre>";
print_r($data->all());
?>