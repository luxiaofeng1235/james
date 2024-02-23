<?

// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_detail.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:采集小说的列表信息
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
use QL\QueryList;##引入querylist的采集器

$url = Env::get('APICONFIG.PAOSHU_API_URL'); //需要抓取的小说的url
// 定义采集规则
$rules = [
    'cate_name' => ['h2','text'], //取小说的标题
    'dataItem' => ['ul', 'html'], //文章的一直
];
$range = '.novellist'; //循环的范围
//读取当前的从小说网拔下来的数据
$data = readFileData('./jsonList.txt');
//定义每本小说的标题信息
$item_rules = [
    // 'title'     =>  ['a','title'],
    'story_link'       => ['a','href'],
    'title'     =>  ['a','text'],
];
$storeData = json_decode($data,true);
if(!empty($storeData)){
    $range = 'li';
    foreach($storeData as $key =>$val){
        $html = $val['dataItem'];
        $cate_name = trim($val['cate_name']);
        if(!$html) continue;
        $itemList = QueryList::html($html)
                ->rules($item_rules)
                ->range($range)
                ->query()
                ->getData();
        if(!empty($itemList)){
            $info =[];
            foreach($itemList->all() as $k =>$v){
                $info['cate_name'] = $cate_name;
                $info = array_merge($info,$v);
                $return[] = $info;
            }
        }
    }
    $now_time = time();
    if(count($return)>0){
        //处理需要入库的主要信息
        foreach($return as $gkey =>$gval){
            $link_url = str_replace('http:/','',$gval['story_link']);
            $article_url = Env::get('APICONFIG.PAOSHU_HOST') . '/'.$link_url;
            $novelid_str = str_replace('/','',$link_url);
            $return[$gkey]['story_link'] = $article_url;
            $return[$gkey]['story_id'] = $novelid_str;
            $return[$gkey]['createtime'] = $now_time;
            $return[$gkey]['source'] = Env::get('APICONFIG.PAOSHU_STR');//标记
            $where_data = "story_id = '".$novelid_str."'";
            //查是否存在当前小说信息
            $info = $mysql_obj->get_data_by_condition($where_data,$novel_table_name,'store_id');
            if(!empty($info)){
                unset($return[$gkey]);
            }
        }
    }
    $return = array_merge(array(),$return);
    // $test_data = array_slice($return , 0 ,1);
    $result = $mysql_obj->add_data($return ,$novel_table_name);
    if(!$result){
        echo "complate error";
    }
    echo "最新文章同步完成=======共同步".count($return)."篇小说";
}
?>