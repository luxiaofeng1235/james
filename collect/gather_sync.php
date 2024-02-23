<?
ini_set("memory_limit", "8000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

$limit = isset($argv[1]) ? intval($argv[1]) : 10;
$start = isset($argv[2]) ? intval($argv[2]) : 0;//控制脚本的起止位置
$end = isset($argv[3]) ? intval($argv[3]) : 0;//控制语句的控制条件


$exec_start_time = microtime(true); //执行开始时间
$table_novel_name ='ims_novel_info';
$where_data =  "is_async =0 and  source='".Env::get('APICONFIG.PAOSHU_STR')."'";
// $where_data .=" and store_id>".$start." and store_id<".$end; ##先限制这个条件吧
$sql = "select count(store_id) as ts_count from $table_novel_name where ".$where_data; //取出来所有的数据集合
$ts_count = $mysql_obj->fetch($sql ,'db_slave');
$countNum = $ts_count['ts_count'] ?? 0;
if(!$countNum){
    echo "未发现需要同步的数据";
}

// $num =50;

$pages = ceil($countNum /$limit);
$php_path = dirname(dirname(dirname(__DIR__))).'/Extensions/php/php7.2.9nts/php.exe';//定义PHP扩展的路径
$php_path = str_replace('\\','/',$php_path);

if(!$start && !$end){
    echo "请输入起止位置";
    die;
}
echo "total-num：".$countNum."\r\n";
echo "当前列表：共需要执行".$pages."页，每页".$limit."条数据的小说明细去拉取章节\r\n";
for ($i=0; $i < $pages; $i++) {
     $sql ="select store_id from ".$table_novel_name." where ".$where_data." order by store_id asc limit ".($i*$limit).','.$limit;
    echo "currenet_page：".($i+1)."\r\n";
    $list = $mysql_obj->fetchAll($sql,'db_slave');
    // echo '<pre>';
    // print_R($list);
    // echo '</pre>';
    // echo "ts_count:".count($list)."\r\n";
    if(!empty($list)){
        foreach($list as $key =>$val){
            $store_id = intval($val['store_id']);
            if ( !$store_id ) continue;
            //定义需要执行的语句
            $shell_cmd = $php_path . ' '.ROOT . 'collect/gather_info.php '.$store_id;
            echo $shell_cmd ."\r\n";
            exec($shell_cmd , $output , $status);
        }
        //循环外输出最后的
        echo '<pre>';
        print_R($output);
        echo '</pre>';
    }else {
        echo "page:".($i+1)."no data current\r\n";
    }
}
$exec_end_time =microtime(true); //执行结束时间
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".($executionTime/60)." minutes \r\n";


// for ($i=0; $i <$pages ; $i++) {
//     $page = $i* $num;
//     // $sql ="select id as cate_id from ".$table_name." where ".$where_data." limit ".$page .",".$num;


//     // echo $sql;exit;
//     // foreach($list as $key =>$value){
//     //     $cate_id = intval($value['cate_id']);
//     //     if(!$cate_id) continue;

//     // }
//     // sleep(1);
//     echo $sql."\r\n";
// }
?>