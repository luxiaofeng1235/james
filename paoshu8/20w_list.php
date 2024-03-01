<?php
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');


$sql = 'select story_link from ims_link_url order by id asc limit 2';
$list = $mysql_obj->fetchAll($sql,'db_slave');
$t_data = [];
foreach($list as $key =>$val){
    extract($val);
    $linkData = explode('/',$story_link);
    $story_id = $linkData[3] ?? '';
    if(!$story_id) continue;
    $sql ='select store_id from ims_novel_info where story_id=\''.$story_id.'\'';
    $info = $mysql_obj->fetch($sql,'db_slave');
    if(empty($info)){
        $t_data[] =[
            'story_id'  =>  $story_id,
            'story_link'    =>$story_link,
            'source'   =>   Env::get('APICONFIG.PAOSHU_STR'),
            'createtime'   =>   time(),
        ];
    }
}
$result = $mysql_obj->add_data($t_data,'ims_novel_info');
echo "total-num：".count($t_data) . "\r\n";
echo "over\r\n";
?>