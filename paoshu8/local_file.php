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
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');
$where = '1 and syn_chapter_status = 0 ';//只搜索状态为0 的
if(!empty($id)){
    $run_data = explode(',',$id);
    //通过cli的方式来进行配置开启多个窗口抓取

    $run_ids = [];
    foreach($run_data as $val){
        $run_ids[]="'".$val."'";
    }
    $ss = join(',',$run_ids);
   $where.=" and story_id = ".$ss."";
}else{
    $where.= "and story_id = '0_1'";
}

use QL\QueryList;##引入querylist的采集器

$exec_start_time = microtime(true); //执行开始时间
$sql = "select story_id,story_link,pro_book_id,title from ims_novel_info where $where limit 1";
$list = $mysql_obj->fetchAll($sql,'db_slave');
if($list){
    $num = 200;//一次性抓取30个页面
    foreach($list as $key =>$val){
        $pro_book_id = intval($val['pro_book_id']); //线上的对应的小说id
        $story_id = trim($val['story_id']);
        $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $pro_book_id;//下载路径;
        // //创建地址目录信息
        if(!is_dir($download_path)){
            createFolders($download_path);
        }
        if(!$pro_book_id){
            echo "未同步线上小说id\r\n";
            continue;
        }
        //读取json的目录信息
        $file_name =Env::get('SAVE_JSON_PATH') .DS .$pro_book_id.'.' .NovelModel::$json_file_type;
        $json_data = readFileData($file_name);
        if(!$json_data) continue;
        $chapter_item = json_decode($json_data,true);
        if(!$chapter_item) continue;

        //对数据做字段 转换，主要修改的程序太多了
        $chapter_item = NovelModel::changeChapterInfo($chapter_item);
        $items = array_chunk($chapter_item,$num);
        $ids = [];
        foreach($items as $k =>&$v){
            //抓取内容信息
            $html_data= getContenetNew($v);
            $ids = array_column($v, 'id');
            //更新对应的is_async状态
            // //保存本地存储数据
            saveLocalFile($download_path,$html_data);
        }

        /***********更新小说里的章节已同步状态 start*****************/
        $update_novel_data= ['syn_chapter_status'=>1];
        $where_up_data = "story_id='".$story_id."' limit 1";
        $mysql_obj->update_data($update_novel_data,$where_up_data,$novel_table_name);
        /***********更新小说里的章节已同步状态 end******************/
        printlog('当前小说('.$va['title'].')同步完成，线上小说id：'.$pro_book_id.'---story_id：'.$story_id);

        echo "succes story_id：".$story_id."\t拉取本地章节：".count($chapter_item)."\turl:".$val['story_link']."\r\n";
    }
    $exec_end_time =microtime(true); //执行结束时间
    $executionTime = $exec_end_time - $exec_start_time;
    echo "Script execution time: ".sprintf('%.2f',($executionTime/60))." minutes \r\n";
}else{
    echo "暂未发现需要去同步的章节的小说\r\n";
}

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
        $filename = $save_path .DS. md5($val['link_name']).'.'.NovelModel::$file_type;
        // echo $val['link_name'];
        // echo "\r\n";
        // echo $filename;die;
        //应该是这里的问题导致部分没有写入，标题中含有特殊字符的原因，需要到时候处理一下link_name的内容
        $res= file_put_contents($filename,$content); //防止文件名出错
    }
}
?>