<?
ini_set("memory_limit", "5000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');
$table_novel_name ='ims_category';
$sql = "select count(id) as ts_count from $table_novel_name where is_async = 0"; //取出来所有的数据集合
$ts_count = $mysql_obj->fetch($sql ,'db_slave');
$countNum = $ts_count['ts_count'] ?? 0;
if(!$countNum){
    echo "未发现需要同步的数据";
}
echo '<pre>';
print_R($countNum);
echo '</pre>';
exit;
?>