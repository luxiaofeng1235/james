<?php
///querylist测试程序
require_once(__DIR__.'/library/init.inc.php');
use QL\QueryList;

//使用过滤
$html =<<<STR
    <div id="content">

        <span class="tt">作者：xxx</span>

        这是正文内容段落1.....

        <span>这是正文内容段落2</span>

        <p>这是正文内容段落3......</p>

        <span>这是广告</span>
        <p>这是版权声明！</p>
    </div>
STR;

// DOM解析规则
$rules = [
     //设置了内容过滤选择器
    'content' => ['#content','html','-.tt -span:last -p:last']
];
$rt = QueryList::rules($rules)->html($html)->query()->getData();

$reg = [
    'content'   =>['#content','html']
];

$rt  = QueryList::get('https://www.xsw.tw/book/1229150/226958802.html');
$item = $rt
            ->rules($reg)
            ->query()
            ->getData();
$item = $item->all();
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


?>