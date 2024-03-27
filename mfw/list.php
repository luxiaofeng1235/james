<?php
ini_set("memory_limit",'7000M');
set_time_limit(300);

$dir = dirname(dirname(__FILE__));
$dir= str_replace("\\",'/',$dir);
require_once ($dir.'/library/init.inc.php'); #加载函数变更操作
#查询地址表的相关数据信息
$post_url = 'https://www.mafengwo.cn/ajax/router.php';
//获取相关的数据接口
$goodsArr = [];
$prov_arr = $mysql_obj->get_data_by_condition("upid=0",'jishigou_common_distrinct');
if(!empty($prov_arr)){
    $cdata = array_chunk($prov_arr,10);
    foreach ($cdata as $page =>$item) {
        if(!$item)
            continue;
        //对数据进行重组赋值
        $goodsArr[$page+1] = $item;
    }
}
if(!$goodsArr) $goodsArr = [];
echo '<pre>';
print_R($goodsArr);
echo '</pre>';
exit;