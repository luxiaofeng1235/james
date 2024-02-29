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
foreach($list as $key=>$val){
    $sql ="select CONCAT('".Env::get('APICONFIG.PAOSHU_HOST')."',link_url)  as link_url,link_name,story_id  from ims_chapter where story_id ='".$val['story_id']."'";
    $chapter_list = $mysql_obj->fetchAll($sql,'db_slave');
    $download_path = ROOT . 'log' . DS . 'paoshu8' .DS . 'chapter';
    //创建目录信息
    if(!is_dir($download_path)){
        createFolders($download_path);
    }
    if($chapter_list){
        //获取关联的章节信息列表
        $json_content = getItemData($chapter_list);
        $save_json_file = $download_path . DS . $val['story_id'] .'.json';
        echo "url:".$val['story_link']."====success：".$save_json_file;
        echo "\r\n";
        //写入对应的目录中区
        file_put_contents($save_json_file, $json_content);
    }else{
        echo 11;die;
    }
}
echo "over\r\n";

//拼装需要的数据返回
function getItemData($item){
    if(!$item){
        return [];
    }
    $list =[];
    /*
"id": 1,
    "sort": 1,
    "chapter_link": "https://www.biquge34.net/article/14453/6400281.html",
    "chapter_name": "第一章 倒斗天师",
    "vip": 0,
    "cion": 0,
    "is_first": 0,
    "is_last": 0,
    "text_num": 2000,
    "addtime": 1704911323
 */
    foreach($item as $key =>$val){
        $list[] = [
            'id'    =>$val['story_id'],//小说ID
            'sort'    =>$key+1,//默认排序
            'chapter_link'   =>$val['link_url'],//连接地址
            'chapter_name' =>$val['link_name'],//连接地址
            'vip'   =>  0,//是否为VIP
            'cion'  =>  0,//是有有图标
            'is_first'  =>  0,//附加字段
            'is_last'   => 0,//附加字段
            'text_num'  => 2000,//默认先给2000
            'addtime'   =>time(),//添加时间
        ];
    }
    $json_string= json_encode($list,JSON_UNESCAPED_UNICODE);
    return $json_string;
}
?>
