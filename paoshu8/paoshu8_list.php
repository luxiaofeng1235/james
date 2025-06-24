<?php
require_once dirname(__DIR__) . '/library/init.inc.php';
require_once dirname(__DIR__) . '/library/file_factory.php';
echo "start_time：" . date('Y-m-d H:i:s') . "\r\n";

use QL\QueryList;

$factory = new FileFactory($mysql_obj, $redis_data);

$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL'); //小说详情页表信息
$db_conn = 'db_master';
$urls = [
    'http://www.paoshu8.info/xuanhuanxiaoshuo/',
    'http://www.paoshu8.info/xiuzhenxiaoshuo/',
    'http://www.paoshu8.info/dushixiaoshuo/',
    'http://www.paoshu8.info/chuanyuexiaoshuo/',
    'http://www.paoshu8.info/wangyouxiaoshuo/',
    'http://www.paoshu8.info/kehuanxiaoshuo/',
];

$list = StoreModel::swooleRquest($urls);
$range_update = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_update'];
$update_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['update_list'];
$novelList = [];
foreach ($list as $key => $html) {
    if (!$html)
        continue;
    $storyList = QueryList::html($html)
        ->rules($update_rules)
        ->range($range_update)
        ->query()
        ->getData();
    $storyList = $storyList->all();
    $novelList = array_merge($novelList, $storyList);
}

foreach ($novelList as $key => &$val) {
    $val['source'] = 'paoshu8';
    $val['tag'] = "";
}

$insertData = [];
$num++;
foreach ($novelList as $key => $val) {
    $num++;
    $val['tag'] = $val['cate_name'] ?? '';
    //查询是否存在次本小说
    $source = $val['source'];
    $storyInfo = NovelModel::getNovelInfoById($val['story_id'], $source);
    if (empty($storyInfo)) {
        $val['title'] = trimBlankSpace($val['title']); //小说名称
        $val['author'] = $val['author'] ? trimBlankSpace($val['author']) : '未知'; //小说作何
        $val['is_async'] = 0; //是否同步状态
        $val['syn_chapter_status'] = 0; //是否同步章节
        $val['source'] = $source; //来源
        $val['createtime'] = time(); //时间
        $insertData[] = $val;
        echo "num = {$num} title={$val['title']}\t author = {$val['author']} is to insert this data\r\n";
    } else {
        $i_num++;
        //更新对应的状态信息，需要改成is_async 0,方便进行同步
        echo "num = {$num} exists store_id = {$storyInfo['store_id']} \t title={$storyInfo['title']}\t author = {$storyInfo['author']} ，status is update,next to run\r\n";
        $note = $storyInfo['note'] ?? '';
        if (!empty($note)) {
            $note = '';
        }
        $factory->updateUnRunInfo($storyInfo['store_id'], $note); //更新当前的小说状态为同步
    }
}

echo "===========实际待需要插入的小说有 " . count($insertData) . "本，会自动同步\r\n";
echo "******************已存在的小说有 {$i_num}本，状态会自动处理为待同步\r\n";
if ($insertData) {
    //同步数据
    $ret = $mysql_obj->add_data($insertData, $novel_table_name, $db_conn);
    if (!$ret) {
        echo "数据库数据同步失败\r\n";
    }
    echo "同步小说列表成功 \r\n";
} else {
    echo "暂无小说需要插入的数据同步\r\n";
}

echo "finish\r\n";
echo "finish_time：" . date('Y-m-d H:i:s') . "\r\n";
