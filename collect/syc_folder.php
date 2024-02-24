<?php
ini_set("memory_limit", "5000M");
// set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

$limit = isset($argv[1]) ? intval($argv[1]) : 0;

$sql = "select story_id,story_link from ims_novel_info ";
if($limit>0){
    $sql .=" limit ".$limit;
}
$list =$mysql_obj->fetchAll($sql , 'db_slave');
$save_file = 'json.txt';//需要存储的txt文件信息
foreach($list as $key=>$val){
    $sql ="select CONCAT('".Env::get('APICONFIG.PAOSHU_HOST')."',link_url)  as link_url,link_name,story_id  from ims_chapter where story_id ='".$val['story_id']."'";
    $chapter_list = $mysql_obj->fetchAll($sql,'db_slave');
    $download_path = ROOT . 'log' . DS . 'paoshu8' .DS . 'chapter'.DS.$val['story_id'];
    //创建目录信息
    if(!is_dir($download_path)){
        createFolders($download_path);
    }
    if($chapter_list){
        //获取关联的章节信息列表
        $json_content = getItemData($chapter_list);
        $save_json_file = $download_path . DS . $save_file;
        echo "url:".$val['story_link']."====success：".$save_json_file;
        echo "\n";
        //写入对应的目录中区
        file_put_contents($save_json_file, $json_content);
    }
}
echo "over\r\n";

//拼装需要的数据返回
function getItemData($item){
    if(!$item){
        return [];
    }
    $list =[];
    foreach($item as $key =>$val){
        $list[] = [
            'story_id'    =>$val['story_id'],
            'id'    =>$key+1,
            'url'   =>$val['link_url'],//连接地址
            'adddate'   =>time(),//添加时间
            'link_name' =>$val['link_name'],//连接地址
        ];
    }
    $json_string= json_encode($list,JSON_UNESCAPED_UNICODE);
    return $json_string;
}
?>