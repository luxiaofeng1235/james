<?
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');


$where_condition = 'id>0';
$num = $mysql_obj->fetch('select count(1) as num from ims_link_url where '.$where_condition,'db_slave');
$all_num = $num['num'] ?? 0;
$limit = 5000;
$t =ceil($all_num/$limit);
$res = [];
$have_all = 0;
for ($i=0; $i <$t ; $i++) {
    $str = $i*$limit.','.$limit;//设置执行的步长
    $sql  = "select story_link from ims_link_url where  {$where_condition} limit ".$str;
    $query = $mysql_obj->fetchAll($sql,'db_slave');
    foreach($query as $value){
        $parse_url = explode('/',$value['story_link']);
        $file_name = Env::get('SAVE_HTML_PATH').DS.'detail_'.$parse_url[3].'.'.NovelModel::$file_type;
        if(!file_exists($file_name)){
            $have_all++;
            //file_put_contents('./compare_diff.txt',$value['story_link'].PHP_EOL,FILE_APPEND);
            // echo 1;die;
        }
    }
}
echo "count:".$have_all."<br/>";

?>