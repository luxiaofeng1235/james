<?php
require_once(__DIR__.'/library/init.inc.php');
use QL\QueryList;

$base_url = Env::get('COMMENT_WEB_URL');
$url = "https://www.yousuu.com/bookstore/?channel&classId&tag&countWord&status&update&sort=score&page=3";


$range = '.full-mode-book';
$rules =[
    'cover_logo'=>['.card-bookInfo-cover','attr(cover)'],
    'book_url'=>['.book-info a','href','',function($item) use($base_url){
        $bookUrl = $base_url . $item;
        $bookUrl = str_replace("//",'/',$bookUrl);
        return $bookUrl;
    }],
    'title'=>['.book-info a','text'],
    'book_id'=>['.card-bookInfo-cover','attr(bookid)'],
    'score_res'=>['.hidden-md-and-up:eq(1)','text'],
    'category_res'=>['.bookinfo-tags','html'], //分类标签处理

];
$res = QueryList::get($url);
$data = $res->rules($rules)
            ->range($range)
            ->query()
            ->getData();

$data = $data->all();
if(!empty($data)){
    $text_reg = '/<a.*?.*?>(.*?)<\/a>/ims';
    foreach($data as $key =>$val){
        if (!$val)
            continue;
        //处理评分
        $score_res = trim($val['score_res']);
        $score_res = str_replace("综合评分：", "", $score_res);
        $ret = explode("(", $score_res);
        if($ret){
            $score = intval($ret[0]);
            $comment_count = str_replace("人)","",$ret[1]);
            $comment_count = intval($comment_count);
        }
        //正则匹配文本
        preg_match_all($text_reg, $val['category_res'], $link_text); //匹配文本;
        if(!empty($link_text) && isset($link_text[1])){
            $category = implode('-', $link_text[1]??[]);
        }
        if(isset($val['category_res'])) unset($data[$key]['category_res']);
        if(isset($val['score_res'])) unset($data[$key]['score_res']);
        $data[$key]['score'] = $score;
        $data[$key]['comment_count'] = $comment_count;
        $data[$key]['category'] = $category;
        $data[$key]['addtime'] = time();
    }
}
$table_name = 'mc_book_comment';
$conn = 'db_master';
$ids= $mysql_obj->add_data($data,$table_name, $conn);
if(!$ids){
    echo "同步失败\r\n";
    exit();
}
echo "数据同步完不成，共完成".count($data)."本小说数据\r\n";
?>