<?
ini_set("memory_limit", "5000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
$table_name ='ims_category';
$where_data =  "1 and is_async = 0 ";
$sql = "select count(id) as ts_count from $table_name where ".$where_data; //取出来所有的数据集合
$ts_count = $mysql_obj->fetch($sql ,'db_slave');
$countNum = $ts_count['ts_count'] ?? 0;
if(!$countNum){
    echo "未发现需要同步的数据";
}

$num =50;
$pages = ceil($countNum /$num);
$root_path = ROOT.'request/aa.php';
$shell_cmd = 'php '.$root_path;
echo $shell_cmd;
// echo $shell_cmd;die;
// $shell_cmd =  str_replace("\\",'/' , $shell_cmd);
exec($shell_cmd , $output , $status);
echo '<pre>';
print_R($output);
echo '</pre>';


// for ($i=0; $i <$pages ; $i++) {
//     $page = $i* $num;
//     // $sql ="select id as cate_id from ".$table_name." where ".$where_data." limit ".$page .",".$num;
//     $sql ="select id as cate_id from ".$table_name." where ".$where_data." limit 2";
//     $list = $mysql_obj->fetchAll($sql,'db_slave');
//     echo '<pre>';
//     print_R($list);
//     echo '</pre>';
//     exit;
//     // echo $sql;exit;
//     // foreach($list as $key =>$value){
//     //     $cate_id = intval($value['cate_id']);
//     //     if(!$cate_id) continue;

//     // }
//     // sleep(1);
//     echo $sql."\r\n";
// }
?>