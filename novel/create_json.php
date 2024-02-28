<?
require_once(dirname(__DIR__).'/library/init.inc.php');
$table_local_name ='ims_novel_info';
$sql = "select title,story_id,pro_book_id from ".$table_local_name." where pro_book_id>0";
$list =$mysql_obj->fetchAll($sql,'db_slave');
$basedir = 'E:\novel_json\\';
foreach($list as $key =>$val){
    $story_id = $val['story_id'] ?? '';
    $pro_id = $val['pro_book_id'];
    if(!$story_id)
        continue;
    $sql = "select chapter_id,createtime,link_name,CONCAT('".Env::get('APICONFIG.PAOSHU_HOST')."',link_url) link_url from ims_chapter where story_id='".$story_id."'";
    $item = $mysql_obj->fetchAll($sql,'db_slave');
    if(!empty($item)){
        $data  = [];
        foreach($item as $key =>$val){
            $data[] = [
                'id'    =>$key+1 ,
                'sort'  =>$key+1,
                'chapter_link'  =>$val['link_url'],
                'chapter_name'  =>$val['link_name'],
                'vip'   =>  0,
                'cion'  =>  0,
                'is_first' =>   0,
                'is_last'   => 0,
                'text_num'  => 2000,
                'addtime'   =>$val['createtime'],
            ];
        }
        //存储的路径的位置
        $filename = $basedir.$pro_id.'.json';
        if(!file_exists($filename)){
            file_put_contents($filename, json_encode($data,JSON_UNESCAPED_UNICODE));
        }
    }
}
echo "over\r\n";

?>