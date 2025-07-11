<?php
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :deal_page.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:处理列表中的数据并保存到对应的目录中区
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
use Yurun\Util\HttpRequest;
use Overtrue\Pinyin\Pinyin; //导入拼音转换类
$pinyin = new Pinyin();

$exec_start_time = microtime(true);

//创建对应的目录
$download_path =Env::get('SAVE_CACHE_INFO_PATH');//下载路径;
if(!is_dir($download_path)){
    createFolders($download_path);
}
$cateId = isset($argv[1]) ? $argv[1] : 1;
$limit = Env::get('TWCONFIG.RUN_LIST_PAGE');
if(!$limit){
    exit("请输入完本的起止页码数");
}
$size  = explode(',',$limit);
$start = $size[0] ?? 0;
$end = $size[1] ?? 0;

for ($i=277; $i <=$end ; $i++) {
    $page = $i;
    $list = getPageList($cateId,$page);
    if(!$list){
        return '数据为空不需要处理';
    }
    ////同步到特定的JSON文件中去
    $ret = asyncJsonFile($list ,$cateId,$page);
    sleep(1); //没戏休息三秒钟
}
echo "================================================\r\n";
echo "over\r\n";

/**
* @note 同步爬取下来的内容到json文件里
*
* @param $item array 需要处理的数据
* @return array
*/
function asyncJsonFile($list , $cateId, $page= 1){
    if(!$list){
        return [];
    }
    global $pinyin,$download_path;
    $list = double_array_exchange_by_field($list ,'story_id');

    $returnList= [];
    ///合并两部分的数据信息
    foreach($list as $key =>$val){
        $ext_data =$pinyin->name($val['title'] , PINYIN_KEEP_NUMBER);
        $val['english_name'] = $ext_data ? implode('',$ext_data) : '';
        $returnList[] = $val;
    }

    //保存的json文件信息的路径
    $save_json_file = $download_path . DS . StoreModel::$detail_page .$cateId .'_'.$page.'.json';
    if(empty($returnList)){
        return "数据为空不需要处理\r\n";
    }
    echo "写入json文件的小说总数：".count($returnList)."\r\n";
    //保存的关联的基础数据信息
    $json_data =  json_encode($returnList ,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    $t = writeFileCombine($save_json_file,$json_data);
    if(!$t){
        echo "数据写入失败\r\n";
    }else{
        echo "success -- {$save_json_file} \r\n";
    }
    echo "finish \r\n";
}


/**
* @note 获取DOM结构中的query字符串长度
*
* @param $page int 1 分页列表
* @param $cateId int 分类ID
* @return
*/

function getPageList($cateId=1,$page= 1){
    if(!$page) return false;
    global $urlRules;
    //获取分页的列表文件信息
    $pageFile = Env::get('SAVE_PAGE_PATH').DS. StoreModel::$page_name.$cateId.'_'.$page.'.'.StoreModel::$file_type;
    echo "page = {$page} of html path ----{$pageFile} \r\n";
    $files  =  readFileData($pageFile);
    if(!$files){
        echo "this story files is no data\r\n";
        return false ;
    }

    // echo '<pre>';
    // print_R($files);
    // echo '</pre>';
    // exit;
    $range = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_range']; //循环的列表范围
    $rules = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_list']; //获取分页的具体明细

//     $files= '<div class="list-out">
// <span class="flex w80"><em><a href="https://www.twking.cc/237_237328/" target="xs">星海最強暴力輔助</a></em><em><a href="https://www.twking.cc/237_237328/125490766.html" target="zj">443.第424章 少年遊</a></em></span>
// <span class="gray">隐藏的狼人&nbsp;04-30</span>
// </div>';

    // $ql = QueryList::html($files);
    // echo '<pre>';
    // print_R($rules);
    // echo '</pre>';

    // $item = $ql->rules($rules)
    //             ->query()
    //             ->getData();
    // echo '<pre>';
    // print_R($item);
    // echo '</pre>';
    // exit;



    // echo '<pre>';
    // print_R($range);
    // echo '</pre>';
    // echo '<pre>';
    // print_R($rules);
    // echo '</pre>';
    // exit;
    $ql = QueryList::html($files);
    $item = $ql->rules($rules)
                ->range($range)
                ->query()
                ->getData();
    echo '<pre>';
    print_R($item);
    echo '</pre>';
    exit;
    $item = $item->all();
    $http = new HttpRequest;
    $data = StoreModel::traverseEncoding($item);
    return $data ?? [];
}

?>