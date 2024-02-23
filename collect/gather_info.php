<?php
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
use QL\QueryList;##引入querylist的采集器

if(is_cli()){
    $store_id = $argv[1] ?? 0;
}else{
    $store_id  = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
}
if(!$store_id){
    echo '请选择要抓取的内容id';
    exit();
}
$table_novel_name =Env::get('APICONFIG.TABLE_NOVEL'); //小说基本信息表
$info = $mysql_obj->get_data_by_condition('store_id = \''.$store_id.'\'',$table_novel_name);
$url = Env::get('APICONFIG.PAOSHU_API_URL'); //获取配置的域名信息
if($info){
    $story_link = trim($info[0]['story_link']);//小说地址
    if($info[0]['is_async'] == 1){
        echo "url：---".$story_link."---当前数据已同步，请勿重复同步\r\n";
        exit();
    }
    //定义抓取规则
    $rules = array(
        'cover_logo'       =>array('#fmimg img','src'),//小说封面
        'author'    => array('#info p:eq(0)','text'),//小说作者
        'title'     =>array('#info>h1','text'),//小说标题
        'status'    =>array('meta[property=og:novel:status]','content'),//小说的状态
        'third_update_time'    =>array('#info p:eq(2)','text'), //最近的更新时间
        'nearby_chapter'    =>array('meta[property=og:novel:latest_chapter_name]','content'), //最近的文章
        'intro' => array('#intro','text'),//小说的简介
        'location'  =>  array('.con_top','text'),//小说的面包屑位置
        // 'link_url'    =>array('.place a:eq(2)','href'),//当前书籍的url
        // 'novel_url'   =>array('.info a:eq(2)','href'),//获取小说的跳转地址
    );
    //爬取相关规则下的类
    $info_data=QueryList::get($story_link)
            ->rules($rules)
            ->query()->getData();
    echo '<pre>';
    print_R($info_data);
    echo '</pre>';
    exit;
    $store_data = $info_data->all();
    if(!empty($store_data)){

        $story_id = trim($info[0]['story_id']); //小说的id
        //处理空字符串
        $location = str_replace("\r\n",'',$store_data['location']);
        $location =trim($location);
        $store_data['location'] = $location;

        $update_time  = str_replace('最后更新：','',$store_data['third_update_time']);
        $third_update_time = $update_time.' 00:00:00';
        $third_update_time = strtotime($third_update_time);
        $store_data['third_update_time'] = $third_update_time;

        //处理作者
        $author_data = explode('：',$store_data['author']);
        $store_data['author']  = $author_data[1] ?? '';
        $store_data['updatetime'] = time();
        //执行更新操作
        $where_data = "story_id = '".$story_id."'";
        $update_ret = $mysql_obj->update_data($store_data,$where_data,$table_novel_name);
        echo '<pre>';
        print_R($update_ret);
        echo '</pre>';
        exit;
    }
}else{
    echo "no data";
}
?>