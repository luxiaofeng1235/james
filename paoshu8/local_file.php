<?
/*
 * @param $str 将小说的章节自动同步到对应的目录中去
 * @param $data array 需要处理的
 * @return mixed
 */
ini_set("memory_limit", "5000M");
set_time_limit(0);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

$id = isset($argv[1]) ? trim($argv[1]) : '';

if(!empty($id)){
    $run_data = explode(',',$id);
    //通过cli的方式来进行配置开启多个窗口抓取

    $run_ids = [];
    foreach($run_data as $val){
        $run_ids[]="'".$val."'";
    }
    $ss = join(',',$run_ids);
   $where ="story_id in (".$ss.")";
}else{
    $where = "story_id in ('0_1')";
}

use QL\QueryList;##引入querylist的采集器

$exec_start_time = microtime(true); //执行开始时间
$sql = "select story_id,story_link,pro_book_id from ims_novel_info where $where limit 3000";
$list = $mysql_obj->fetchAll($sql,'db_slave');

$num = 200;//一次性抓取30个页面
foreach($list as $key =>$val){
    $pro_book_id = intval($val['pro_book_id']); //线上的对应的小说id
    $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $pro_book_id;//下载路径
    // echo $download_path;die;
    // //创建地址目录信息
    if(!is_dir($download_path)){
        createFolders($download_path);
    }
    $story_id = trim($val['story_id']);
    if(!$story_id){
        continue;
    }
    $sql = "select novelid,link_name, link_url  from ims_chapter where story_id ='".$story_id."'";
    $chapter_item = $mysql_obj->fetchAll($sql,'db_slave');
    if(!$chapter_item) continue;
    $items = array_chunk($chapter_item,$num);
    $ids = [];
    foreach($items as $k =>&$v){
        //抓取内容信息
        $html_data= getContenetNew($v);
        $ids = array_column($v, 'novelid');
        //更新对应的is_async状态
        // //保存本地存储数据
        saveLocalFile($download_path,$html_data);
    }
    echo "succes story_id：".$val['story_id']."\t拉取本地章节：".count($chapter_item)."\turl:".$val['story_link']."\r\n";
}
$exec_end_time =microtime(true); //执行结束时间
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".($executionTime/60)." minutes \r\n";

/**
* @note 生成保存的文件
*
* @param $save_path string 保存路径
* @param $data array 需要处理的数据
* @return unknow
*/

function saveLocalFile($save_path,$data){
    foreach($data as $key =>$val){
        $content = $val['content'] ?? '';//提交的内容
        $filename = $save_path .DS. md5($val['link_name']).'.txt';
        // echo $val['link_name'];
        // echo "\r\n";
        // echo $filename;die;
        //应该是这里的问题导致部分没有写入，标题中含有特殊字符的原因，需要到时候处理一下link_name的内容
        $res= file_put_contents($filename,$content); //防止文件名出错
    }
}
?>