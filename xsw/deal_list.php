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
$exec_start_time = microtime(true);

//创建对应的目录
$download_path =Env::get('SAVE_CACHE_INFO_PATH');//下载路径;
if(!is_dir($download_path)){
    createFolders($download_path);
}

$page = 1;
$list = getPageList($page);
if(!$list){
    return '数据为空不需要处理';
}

////按照对应的可以去分割数据
$list = array_slice($list, 0, 1);
$list = double_array_exchange_by_field($list ,'story_id');



$urls = array_column($list , 'detail_link');
//设置配置细腻
$item = StoreModel::swooleRquest($urls);

$arrList = getDetailList($item);

//最终需要更新的章节信息去变更处理
$returnList = [];
///合并两部分的数据信息
foreach($list as $key =>$val){
    if(isset($arrList[$key])){
        $val = array_merge($val , $arrList[$key]);
    }
    $returnList[] = $val;
}
echo '<pre>';
print_R($returnList);
echo '</pre>';
exit;


/**
* @note 获取详情页的数据信息
*
* @param $item array 获取的基础数据信息
* @return  array
*/
function getDetailList($item = []){
    if(!$item){
        return [];
    }
    global $urlRules;
    $arrList = [];
    foreach($item as $key =>$val){
        $rules = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['detail_info'];
        $item = QueryList::html($val)
                        ->rules($rules)
                        ->query()
                        ->getData();
        $item = $item->all();
        if(!empty($item)){
            $detailInfo = StoreModel::traverseEncoding($item);
            $arrList[$detailInfo['story_id']] = $detailInfo;
        }
    }
    return $arrList;
}

/**
* @note 获取DOM结构中的query字符串长度
*
* @param $page int 1 分页列表
* @return
*/

function getPageList($page= 1){
    if(!$page) return false;
    //获取分页的列表文件信息
    $pageFile = Env::get('SAVE_PAGE_PATH').DS. StoreModel::$page_name.$page.'.'.StoreModel::$file_type;
    $files  =  readFileData($pageFile);
    $ql = QueryList::html($files);
    global $urlRules;
    $range = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_range']; //循环的列表范围
    $rules = $urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['page_list']; //获取分页的具体明细
    $ql = QueryList::html($files);
    $item = $ql->rules($rules)
                ->range($range)
                ->query()
                ->getData();
    $item = $item->all();
    $data = StoreModel::traverseEncoding($item);
    return $data ?? [];
}

?>