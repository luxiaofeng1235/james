<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$db_conn_novel = 'db_novel_pro';
$table_novel_table = "mc_book";
$where = "1";
$order_by = "id asc";
$sql = "select count(1) as num  from {$table_novel_table} where {$where}";
$ts_count= $mysql_obj->fetch($sql,$db_conn_novel);
$exec_start_time = microtime(true);
$count = $ts_count['num'] ?? 0;
$limit = 500;
$base_dir = Env::get('SAVE_JSON_PATH');
$pages = ceil($count/$limit);
function getFilesInDirectory($path)
{
    //判断目录是否为空
    if(!file_exists($path)) {
        return [];
    }
    $files = scandir($path);
    $fileItem = [];
    foreach($files as $v) {
        $newPath = $path .DIRECTORY_SEPARATOR . $v;
        if(is_dir($newPath) && $v != '.' && $v != '..') {
            // $fileItem = array_merge($fileItem, getDir($newPath));
        }else if(is_file($newPath)){
            $fileItem[] = $newPath;
        }
    }

    return $fileItem;
}


$newList = [];
$res = getFilesInDirectory($base_dir);

if(!$res){
    exit("not files\r\n");
}
if($res){
    foreach($res as $val){
        if(strpos($val, '.json')){
            $newList[] = $val;
        }
    }
}



$num = 0;

$allCount = count($newList);
foreach($newList as $path){
    $num++;
    $basename = basename($path);
    $md5_str = str_replace(".json", '', $basename);
    $sql ="select id,book_name,author from {$table_novel_table} WHERE MD5(CONCAT(book_name,author)) = '{$md5_str}'";
    $info = $mysql_obj->fetch($sql ,$db_conn_novel);
    if(!$info){
        echo "path = {$path} 无此记录自动删除删除操作\r\n";
        if(unlink($path)){
            echo "index=($allCount/{$num})\tpath ={$path}\t删除无用记录-----删除成功\r\n";
        }else{
            echo "index=($allCount/{$num})\tpath ={$path}\t删除失败\r\n";
        }
    }else{
        echo "index=($allCount/{$num})\tpath = {$path} exists id ={$info['id']}\r\n";
    }
}



// echo "total-num-book:{$count} size ={$limit} all_page={$pages}\r\n";

// for ($i=0; $i < $pages; $i++) {
//     echo "current page ： ".($pages-1)."/{$i}\r\n";
//     $start = $limit * $i;
//     $sql = "select id ,book_name ,author from {$table_novel_table} where {$where} order by {$order_by} limit {$start},{$limit}";
//     $list  =$mysql_obj->fetchAll($sql,$db_conn_novel);
//     if(!empty($list)){
//         $index =0;
//         foreach($list as $value){
//             $index++;
//             $book_name = trim($value['book_name']);
//             $author = trim($value['author']);
//             $id = intval($value['id']);
//             //以当前的目录进行创建前两个子
//             $md5_str= NovelModel::getAuthorFoleder($book_name ,$author);
//             if(!$md5_str){
//                 continue;
//             }
//             $new_dir = $base_dir.DS.substr($md5_str, 0 ,2); //新的目录结构
//             //创建新的文件夹
//             if(!is_dir($new_dir)){
//                 createFolders($new_dir);
//             }
//             $file_name =Env::get('SAVE_JSON_PATH') .DS .$md5_str.'.' .NovelModel::$json_file_type;
//             if(file_exists($file_name)){
//                 echo 111;exit;
//                 // //按照新的文件进行复制
//                 // $new_filename = $new_dir.DS.basename($file_name);
//                 // if(copy($file_name, $new_filename)){
//                 //     echo "index={$index}\tid = {$id}\ttitle={$book_name}\tauthor={$author}\tfrom={$file_name}\tto={$new_filename}\t复制成功\r\n";
//                 // }else{
//                 //     echo "index={$index}\tid = {$id}\ttitle={$book_name}\tauthor={$author}\tfrom={$file_name}\tto={$new_filename}\t复制失败\r\n";
//                 // }
//             }else{
//                 echo "id = {$id}\ttitle={$book_name}\tauthor={$author}\t文件不存在\r\n";
//             }
//         }
//     }
// }
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
?>