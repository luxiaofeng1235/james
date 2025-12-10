<?php

/*
 * 通过条件来关联删除一些不必要的数据信息，一些有问题的书籍信息 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php'; //初始化配置

$table_name = Env::get('TABLE_MC_BOOK');
$db_conn = 'db_master';


$condition = "chapter_num<100 and  `status` = 0 and source_url!='qijitushu'";
$id = isset($argv[1]) ? $argv[1] : 0;
if($id){
   $condition .=" and id = {$id}"; 
}
$exec_start_time = microtime(true);


// $sql = "SELECT  * from ims_novel_info_bak WHERE empty_status = 1";
// $list = $mysql_obj->fetchAll($sql,'db_master');
// $ids = array_column($list,'pro_book_id');




$sql = "select id,book_name,author,source_url,pic,addtime from {$table_name} where  book_name regexp '测试' or author regexp '测试'";
$order_by= ' order by id asc';
$sql .=$order_by;
$sql .=" limit 50000";

echo "sql = {$sql} \r\n";
exit;
$list = $mysql_obj->fetchAll($sql,$db_conn);
    
if($list){
    foreach($list as $key =>$val){
        $book_name = trimBlankSpace($val['book_name']);
        $author = trimBlankSpace($val['author']);
        $pic = $val['pic'];
        $id = intval($val['id']);
         if(!$val || !$id) continue;
        //获取当前的文件所在的基础的json目录
        $file_name  =NovelModel::getBookFilePath($book_name,$author);
        $md5_str = NovelModel::getAuthorFoleder($book_name, $author);
        $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;//章节的内容路径
        echo "key =".($key+1)."\tid = {$id}\tbook_name = {$book_name}\tauthor ={$author}\t pic = {$pic}\t chapter = {$file_name}\t txt = {$download_path}\r\n";
        //检查是否有当前的url信息
        if(!$md5_str){
            echo "id = {$id}\t当前的加密串对应的串不对，禁止执行\r\n";
            continue;
        }
        //删除图片
        if($pic && file_exists($pic)){
            echo "删除图片url = {$pic}\r\n";
            unlink($pic);  
        }else{
            echo "图片url={$pic}\t不存在\r\n";
        }
        //删除json的哪个文件
        if($file_name && file_exists($file_name)){
            echo "删除json的目录文件 = {$file_name}\r\n";
            unlink($file_name);  
        }else{
            echo "章节目录url={$file_name}\t不存在\r\n";
        }
        //删除章节对应的内容
        if($download_path && is_dir($download_path)){
            echo "删除章节内容的所在目录 = {$download_path}\r\n";
            deleteDirectory($download_path);
        }else{
            echo "章节目录 = {$download_path}\t不存在\r\n";
        }
        echo "删除迅搜索引对应的ID = {$id} \r\n";
        //删除迅搜索引同步
        XunSearch::delDocumentCli($id);
        //删除SQL对应的数据
        $sql = "delete from {$table_name} where id = {$id} limit 1";
        $res = $mysql_obj->query($sql ,$db_conn);
        if(!$res){
            echo "id = {$id}\t删除语句失败\r\n";
        }else{
            echo "删除的对应的delete sql = {$sql} \r\n";
        }
    }
}else{
    echo "no book data \r\n";
}

echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>