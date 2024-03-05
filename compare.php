<?
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;

set_time_limit(0);
$file = file('./compare_diff.txt');
foreach($file as &$val){
    $val = str_replace("\r\n",'',$val);
    //判断远端url是否存在
}
$size = 200;//控制长度
$item = array_chunk($file,$size);
foreach($item as $k =>$v){
    //保存文件到本地
    curlGetHtml1($v);
}

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

echo "over\r\n";


?>