<?
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;

set_time_limit(0);
$file = file('./copare_diff.txt');
foreach($file as &$val){
    $val = str_replace("\r\n",'',$val);
    //判断远端url是否存在
}
$size = 300;//控制长度
$item = array_chunk($file,$size);
foreach($item as $key =>$val){
    //保存文件到本地
    curlGetHtml1($val);
}

echo "over\r\n";

function curlGetHtml1($urls){
    if(!$urls) return false;
    $rules = array(
        'text' => array('meta[property=og:novel:read_url]','content'),//采集class为two下面的超链接的链接
    );
    //暂时先不开启代理
    $items = MultiHttp::curlGet($urls,null,true);
    if($items){
        $html_data = [];
        foreach($items as $key =>$val){
                $data = QueryList::html($val)
                    ->rules($rules)->query()
                    ->getData();
            // $prefix = str_replace('/','',$key);
            $json_data = $data->all();
            $html_data[$json_data['text']] =$val;
        }
        $save_dir=  'E:\html_data';
        if(!empty($html_data)){
            foreach($html_data as $k =>$v){
                 $index = str_replace('/','',$k);
                 $file_name = $save_dir . DS . 'detail_'.$index.'.txt';
                 // echo $file_name;die;
                 @file_put_contents($file_name,$v);
            }
        }
    }

}

//82_82356

// $exec_start_time =microtime(true);
// $where_condition = 'id<143000';
// $num = $mysql_obj->fetch('select count(1) as num from ims_link_url where '.$where_condition,'db_slave');

// $all_num = $num['num'] ?? 0;
// $limit = 5000;
// $t =ceil($all_num/$limit);
// $res = [];
// for ($i=0; $i <$t ; $i++) {
//     $str = $i*$limit.','.$limit;//设置执行的步长

//     $sql  = "select story_link from ims_link_url where  {$where_condition} limit ".$str;
//     $query = $mysql_obj->fetchAll($sql,'db_slave');
//     foreach($query as $value){
//         $parse_url = explode('/',$value['story_link']);
//         $file_name = 'E:\html_data'.DS.'detail_'.$parse_url[3].'.'.NovelModel::$file_type;
//         if(!file_exists($file_name)){
//             echo $value['story_link'];
//             echo "<br />";
//             $res[]=$value['story_link'];
//         }
//     }
// }
// die;


?>