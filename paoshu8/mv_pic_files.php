<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :批量移动文件夹处理.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
$db_conn_novel = 'db_master';
$table_novel_table = "mc_book";
$exec_start_time = microtime(true);


$where = "source_url REGEXP 'http' or source_url REGEXP 'qijitushu'";
$sql = "select count(1) as num from {$table_novel_table} where $where";
$limit = 500;
$count_info = $mysql_obj->fetch($sql,$db_conn_novel);

$num = $count_info['num'] ?? 0;
$pages = ceil($num / $limit);
for($i = 0;$i <$pages;$i++){
    echo "current page = {$pages}/".($i+1)." \r\n";
    $sql = "select id,book_name,author,pic from {$table_novel_table} where {$where} limit ".($i*$limit).",{$limit}";
    $list = $mysql_obj->fetchAll($sql,$db_conn_novel);
    if($list){
        foreach($list as $key =>$val){
            $pic = $val['pic'] ?? '';
            if(file_exists($pic)) {
                //如果图片已损坏，也进行保存数据
                if (!@getimagesize($pic)) {
                    writeFileAppend('./pic_ids.txt',$val['id']); //追加的方式把ID存起来进行接下来的采集
                    echo "index = {$key}\tid={$val['id']}\ttitle={$val['book_name']}\tauthor={$val['author']}\tpic ={$pic}\t图片已损坏，保存ID信息\r\n";
                }else{
                    echo "index = {$key}\tid={$val['id']}\ttitle={$val['book_name']}\tauthor={$val['author']}\tpic ={$pic}\t图片已存在\r\n";
                }
            }else{
                //图片未存在，就直接保存起来
                writeFileAppend('./pic_ids.txt',$val['id']); //追加的方式把ID存起来进行接下来的采集
                echo "index = {$key}\tid={$val['id']}\ttitle={$val['book_name']}\tauthor={$val['author']}\tpic ={$pic}\t图片未生成，保存ID信息\r\n";
            }
        }
    }
}
echo "over\r\n";
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
exit;





// $list = getDir($base_dir.DS.$remote_dir);

if($sortedFiles){
    $sortList = array_chunk($sortedFiles, 50);
    $count = count($sortList);
    $index = 0;
    foreach($sortedFiles as $path){
        $old_path = $dirpath . DS. $path;
        $index++;
        echo "index={$index}\r\n";
        $basename = $path;
        $sql = "select id,book_name,author,addtime from {$table_novel_table} where instr(`pic`,'{$basename}')>0";
        $res = $mysql_obj->fetchAll($sql ,$db_conn_novel);
        if(!$res){
            echo "no data\r\n";
            continue;
        }
        $info = $res[0] ?? [];
        $addtime = $info['addtime'] ?? 0;
        $s_addtime = date('Ym',$addtime);
        !$s_addtime && $s_addtime= date('Ym');
        $save_path = $base_dir.DS . $s_addtime.DS.$basename;
        $ids = array_column($res, 'id');
        $sql = "update {$table_novel_table} set pic ='{$save_path}' where id in (".implode(',', $ids).")";
        echo $sql."\r\n";
        $mysql_obj->query($sql , $db_conn_novel);
        if(rename($old_path, $save_path)){
            echo "id = {$info['id']}\tbook_name={$info['book_name']}\t from:{$old_path}\t to:{$save_path}\t移动成功\r\n";
        }else{
            echo "id = {$info['id']}\tbook_name={$info['book_name']}\t from:{$old_path}\t to:{$save_path}\t移动失败\r\n";
        }
    }
}

echo "over\r\n";



?>