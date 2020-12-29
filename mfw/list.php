<?php
ini_set("memory_limit",'7000M');
set_time_limit(300);

$dir = dirname(dirname(__FILE__));
$dir= str_replace("\\",'/',$dir);
require_once ($dir.'/library/init.inc.php'); #加载函数变更操作
#查询地址表的相关数据信息
$post_url = 'https://www.mafengwo.cn/ajax/router.php';
//获取相关的数据接口
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
/*
sAct: KMdd_StructWebAjax|GetPoisByTag
iMddid: 10208
iTagId: 0
iPage: 2
_ts: 1608627623802
_sn: 313cc4042e

 */
//转到对饮的函数调用
//$arr = Array_transfrom_new($list);

 
//for ($i=1; $i <=10 ; $i++) {
//	$post_data = array(
//		'sAct'	=>'KMdd_StructWebAjax|GetPoisByTag',
//		'iTagId'	=>'0',
//		'iMddid'	=>'10035',
//		 'iPage'		=>$i,
//		'_ts'	=>'1608627852597',
//		'_sn'	=>'794fac3c62'
//	);
// $result = $http_client->get_curl_depoly($post_url , $post_data);
//
//}


//$dataArr= json_decode($result,true);
//if($dataArr['data']['list']){
//	$contents = $dataArr['data']['list'];
//	 preg_match_all('/<a[^>]+href="(([^"]+)")/i',$contents,$matchRes);
//	 if(isset($matchRes[2])){
//	 	$goodsList = $matchRes[2];
//	 }
//	 echo '<pre>';
//	 print_R($goodsList);
//	 echo '</pre>';
//	 exit;
//
//}
?>