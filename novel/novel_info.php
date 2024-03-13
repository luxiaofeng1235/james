<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
require_once dirname(__DIR__).'/library/file_factory.php';
require_once dirname(__DIR__).'/library/process_url.php';

$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
$novel_table_url ='ims_collect_url';
use QL\QueryList;##引入querylist的采集器
$exec_start_time = microtime(true);
$startMemory = memory_get_peak_usage();

$id = $argv[1] ?? 0;//collect_url表的ID
if(!$id){
    echo '请选择要抓取的内容id'.PHP_EOL;
    exit();
}

$sql = "select story_link from ".$novel_table_url." where id = ".$id;
$novelInfo = $mysql_obj->fetch($sql,'db_slave');
if(!$novelInfo){
    echo "记录不存在或状态不对\r\n";
    exit();
}

//请求的地址
$story_link = trim($novelInfo['story_link']);

$rules = [
    'cover_logo'       =>array('#fmimg img','src'),//小说封面
    'author'    => array('#info p:eq(0) a','text'),//小说作者
    'title'     =>array('#info h1','text'),//小说标题
    'cate_name' =>array('meta[property=og:novel:category]','content'),//分类
    'status'    =>array('meta[property=og:novel:status]','content'),//小说的状态
    'third_update_time'    =>array('#info p:eq(3)','text'), //最近的更新时间
    'nearby_chapter'    =>array('meta[property=og:novel:latest_chapter_name]','content'), //最近的文章
    'intro' =>array('meta[property=og:description]','content'),
    'tag'   => array('meta[property=og:novel:category]','content'),
    'location'  =>  array('.con_top','text'),//小说的面包屑位置
];

$info_data=QueryList::get($story_link)
            ->rules($rules)
            ->query()->getData();
$store_data = $info_data->all();
if(!empty($store_data)){
    $store_data['story_link'] = $story_link;
    $urlPath = parse_url($story_link);
    $id = str_replace('/Partlist/','',$urlPath['path']);
   //处理空字符串
    $location = str_replace("\r\n",'',$store_data['location']);
    $location =trim($location);
    $store_data['location'] = $location;
    $update_time  = str_replace('更新时间：','',$store_data['third_update_time']);
    $third_update_time = $update_time;
    $third_update_time = strtotime($third_update_time);
    $rule_text = [
        'status'    =>['.infotop td:eq(1)','text'],
    ];
   $statusInfo=QueryList::get('https://www.xpaoshuba.com/Book/'.$id)
            ->rules($rule_text)
            ->query()->getData();
    $status_text = $statusInfo->all();
    $status = $status_text['status'] ?? '';
    $status = str_replace('小说状态：','',$status);
    $store_data['status'] = $status;
    $store_data['third_update_time'] = $third_update_time;
    $store_data['source'] = Env::get('APICONFIG.PAOSHU_STR');
    //转义标题
    $store_data['title'] = addslashes(trim($store_data['title']));
    //处理作者并转义
    $store_data['author']  = addslashes(trim($store_data['author']));


    //章节也需要处理特殊的转义字符
    $store_data['nearby_chapter'] = addslashes($store_data['nearby_chapter']);
    $intro = addslashes($store_data['intro']);//转义 特殊字符
    $intro = cut_str($intro,200); //切割字符串
    $store_data['intro'] = $intro;
    $store_data['tag'] = str_replace('小说','',$store_data['tag']);
    $store_data['story_id'] = $id;//按照story_id存储
    //执行更新操作
    // $store_data['updatetime'] = time();
    // $store_data['createtime']  = time();

    $novel_id = syncNovelData($store_data);
    echo '<pre>';
    print_R($novel_id);
    echo '</pre>';
    exit;
}

//同步novel_info表的信息
function syncNovelData($store_data){
    if(!$store_data){
        return false;
    }
    if(!isset($store_data['title']) || !isset($store_data['author']))
        return false;
    global $mysql_obj,$novel_table_name;
    $where_condition =  "title = '".$store_data['title']."' and author ='".$store_data['author']."' limit 1";
    $sql = "select store_id from ".$novel_table_name." where ".$where_condition;
    $info = $mysql_obj->fetch($sql , 'db_slave');
    if(!$info){
        $store_data['createtime'] = time();
        $novel_id  = $mysql_obj->add_data($store_data,$novel_table_name,'db_master');
        if(!$novel_id)
            return false;
    }else{
        $store_data['updatetime'] = time();
        $where_conf = "store_id = '".$info['store_id']."' limit 1";
        $novel_id = $mysql_obj->update_data($store_data,$where_conf,$novel_table_name);
    }
    return $novel_id;

}

?>