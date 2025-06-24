<?php
/*
 * 查询有断章的书籍并导入到ims_novel_info_bak表方便做统计 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$exec_start_time = microtime(true);
$db_conn_novel = 'db_novel_pro';
$db_conn_base ='db_master';
$table_novel_table = "mc_book";
$table_sync_table = "ims_novel_info_bak";
$sql = "select id ,book_name as title,author,source_url from {$table_novel_table} where  instr(source_url,'siluke520')>0 and id = 261104";
echo "sql = {$sql} \r\n";
$list = $mysql_obj->fetchAll($sql,$db_conn_novel);

$num = 0;
foreach($list as $key =>$val){
	$num++;
    $book_name = $val['title']??'';
	$author = $val['author'] ?? '';
	$id = intval($val['id']);
	$source_url  = $val['source_url'] ?? ''; //网站源url
	    
	$md5_str= NovelModel::getAuthorFoleder($book_name ,$author);
	$download_path =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;//下载路径;   
	$file_name =NovelModel::getBookFilePath($book_name,$author);
	// echo "json_path = {$file_name}\r\n";
	$json_data = readFileData($file_name);
	if(empty($json_data)) {
        echo "id ={$id}\t当前小说未生成json文件\r\n";
        continue;
    }
    $chapter_item = json_decode($json_data,true);
    if(!$chapter_item){
    	echo "暂无关联章节目录\r\n";
        continue;
    }
    // echo md5('第2776章刺客');
    // exit;
        
    echo "-----------------------------------------------------------------\r\n";
    $contentData = [];

    $flag= 0;//只有为1的时候去进行保存对应的数据信息
    foreach($chapter_item as $tkey =>$txtVal){
       $chapter_name = trimBlankSpace($txtVal['chapter_name']);
       $filename =$download_path .DS . md5($chapter_name).'.'.NovelModel::$file_type;
        $content = readFileData($filename);
        //如果当前内容不正常就记录下来
        if(($content == "" || $content =='未完待续...' || strstr($content, '未完待续...'))){
            echo "number = {$tkey}\tbook_name  = {$book_name}\tauthor={$author}\tchapter_name = {$txtVal['chapter_name']} \tfile_name ={$filename}\t url = {$txtVal['chapter_link']}\t章节内容有问题，重新抓取 \r\n";
            $contentData['story_link'] = $source_url;
            $contentData['pro_book_id'] = $id;
            $contentData['title'] = $book_name;
            $contentData['author'] = $author;
            $flag = 1;
        }else{
            // echo "number = {$tkey}\tbook_name  = {$book_name}\tauthor={$author}\tchapter_name = {$txtVal['chapter_name']} \t章节内容正常\r\n";
        }
    }

    echo "-----------------------------------------------------------------\r\n";
    // if($contentData){
    //      //同步数据
    //      $mysql_obj->add_data($contentData, $table_sync_table, $db_conn_base);
            
    // }
    // if($flag){
    // 	//同步有问题的章节信息
    // 	$sql = "update ims_novel_info_bak set empty_status = 1 where store_id = {$store_id}";
    //     echo "update_sql = {$sql} \r\n";
    // 	$mysql_obj->query($sql,$db_conn_base);//更新为空的状态
    // }
	    
}

echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>
?>