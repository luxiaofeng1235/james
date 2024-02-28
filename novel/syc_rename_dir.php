<?php
/**
*名称:syc_novel_list
*作用:同步小说列表
*说明:
*版权:
*作者:Red QQ:513072539
*时间:2024/2/28
**/

require_once(dirname(__DIR__).'/library/init.inc.php');

///从本地取出来数据
$table_pro_name = 'mc_book';
$table_local_name ='ims_novel_info';
$sql = "select title,story_id from ".$table_local_name." where story_id='202_202191'";
$list = $mysql_obj->fetchAll($sql,'db_slave');
foreach($list as  $key =>$val){
    $download_path = ROOT . 'log' . DS . 'chapter' .DS . $val['story_id'];//下载路径
    $sql ="select novelid,link_name,link_url from ims_chapter where story_id='".$val['story_id']."'";
    $item = $mysql_obj->fetchAll($sql,'db_slave');
    // $a = getDir($download_path);
    // echo '<pre>';
    // print_R($a);
    // echo '</pre>';
    // exit;
    //重新命名文件
    renameFile($item,$download_path);
    //filename = $save_local .DS.$val['link_name'].'.txt';
    // $link_name = $val['link_name'];
    // $name =str_replace('****','  ',$link_name);
    // $name =str_replace('*','',$name);
    // $name =str_replace('**','',$name);
    // $name =str_replace('***','',$name);
    // $name =str_replace('****','',$name);
    // $name =str_replace('?','',$name);
    // $name =str_replace("\t",'',$name);
    echo "story_id：".$val['story_id']."\tpath：" . $download_path."\r\n";
}
echo "over\r\n";

$err_log = '';
//重新命名文件
function renameFile($data=[],$download_path){
    if(!$data)
        return false;
    $t = [];
    $logpath=$logpath1= [];
    foreach($data as $key =>$val){
        $link_name = $val['link_name'];
        $chapter_name =str_replace('****','  ',$link_name);
        $chapter_name =str_replace('*','',$chapter_name);
        $chapter_name =str_replace('**','',$chapter_name);
        $chapter_name =str_replace('***','',$chapter_name);
        $chapter_name =str_replace('****','',$chapter_name);
        $chapter_name =str_replace('?','',$chapter_name);
        $chapter_name =str_replace("\t",'',$chapter_name);
        $filename = $download_path .DS.$chapter_name.'.txt';
        //新的路径文件
        $new_file_name =$download_path.DS. md5($chapter_name).'.txt';
        //只有不为空才进行判断
        $str= $download_path.DS. md5($chapter_name).'.txt';
        if(!file_exists($str)){
            $t[]=$val;
        }
        if(file_exists($filename)){
            $logpath[]=[
                'old_file'  => $filename,
                'new_file'  =>  $new_file_name,
            ];
        }
    }
    $all_log_path =array_merge($logpath,array());
    // if(!empty($t)){
    //     //如果没有开启就选择本地服务直接批量抓取
    //     saveLocalFile($download_path,$t);
    // }
    if(!empty($all_log_path)){
        foreach($all_log_path as $key =>$val){
            $old_file = trim($val['old_file']);
            $new_file = trim($val['new_file']);
            // echo $old_file;
            // echo "\r\n";
            // echo $new_file;die;
            //直接判断文件是否存在
            if(file_exists($old_file)){
                @rename($old_file,$new_file);
            }
        }
    }
}

function saveLocalFile($save_local,$data){
    if(!$data)
        return false;
    $html_data= getContenetNew($data);
    foreach($html_data as $key =>$val){
        $link_name = $val['link_name'];
        $chapter_name =str_replace('****','  ',$link_name);
        $chapter_name =str_replace('*','',$chapter_name);
        $chapter_name =str_replace('**','',$chapter_name);
        $chapter_name =str_replace('***','',$chapter_name);
        $chapter_name =str_replace('****','',$chapter_name);
        $chapter_name =str_replace('?','',$chapter_name);
        $chapter_name =str_replace("\t",'',$chapter_name);
        $content = $val['content'] ?? '';//提交的内容
        $filename = $save_local .DS.md5($chapter_name).'.txt';
        $aaa[]=$filename;
        // //应该是这里的问题导致部分没有写入，标题中含有特殊字符的原因，需要到时候处理一下link_name的内容
        $res  =file_put_contents($filename,$content); //防止文件名出错
    }
}

function getDir($path){
    $dir = opendir($path);
    while(($file =readdir($dir)) !==false){
        if($file !="." && $file !=".."){
         //判断遍历的是否是一个目录
         $file_path = $path."/".$file;// 给文件添加一个绝对路径
         if(is_dir($file_path)){
            // echo "{$file_path}是一个目录<br>";
            getDir($file_path);
        }else{
            $items[]=$file_path;
            // echo "<span style='color:red'>{$file_path}是一个文件</span>\r\n";
        }
        }
    }
    return $items;
}
?>