<?php
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');

set_time_limit(0);
$where  = "is_run = 0";
$sql  = "select count(1) as num from ims_link_url where ".$where;
$info = $mysql_obj->fetch($sql,'db_slave');
$num = $info['num'] ?? 0;

 $sql = 'select id,story_link from ims_link_url  where '.$where.'  limit 5000';
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
            $ids[] =$val['id'];
            $t_data[] =[
                'story_id'  =>  $story_id,
                'story_link'    =>$story_link,
                'source'   =>   Env::get('APICONFIG.PAOSHU_STR'),
                'createtime'   =>   time(),
            ];
        }
    }
echo '<pre>';
print_R($t_data);
echo '</pre>';
exit;


if($num>0){
    $limit =500;
    $pages = ceil($num/$limit);
    for ($i=0; $i < $pages; $i++) {
        // code...
        echo "current page：".($i+1) . PHP_EOL;

        $list = $mysql_obj->fetchAll($sql,'db_slave');
        $ids =  $t_data = [];
        if($list){
            foreach($list as $key =>$val){

                extract($val);
                $linkData = explode('/',$story_link);
                $story_id = $linkData[3] ?? '';
                if(!$story_id) continue;
                $sql ='select store_id from ims_novel_info where story_id=\''.$story_id.'\'';
                $info = $mysql_obj->fetch($sql,'db_slave');
                if(empty($info)){
                    $ids[] =$val['id'];
                    $t_data[] =[
                        'story_id'  =>  $story_id,
                        'story_link'    =>$story_link,
                        'source'   =>   Env::get('APICONFIG.PAOSHU_STR'),
                        'createtime'   =>   time(),
                    ];
                }
            }
            if($ids){
                //更新对应的是否运行的状态
                $id_str= join(',',$ids);
                $sql = 'update ims_link_url set is_run = 1 where id in ('.$id_str.')';
            }
            $mysql_obj->query($sql,'db_master');//更新对应的状态
            $result = $mysql_obj->add_data($t_data,'ims_novel_info');
        }
        // echo "total-num：".count($t_data) . "\r\n";
        // echo "match-num：".count($ids)."\r\n";
    }
    echo "over\r\n";
}else{
    echo "no data";
}


?>