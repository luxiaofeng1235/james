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
require_once dirname(__DIR__).'/library/rndChinaName.class.php';
use QL\QueryList;
$db_master = 'db_master';
$table_novel_table = "ims_import_book";
$exec_start_time = microtime(true);
function getFilesInDirectory($dir) {
    // Ensure the directory exists
    if (!is_dir($dir)) {
        return [];
    }
    // Scan directory and filter out '.' and '..'
    $files = array_diff(scandir($dir), ['.', '..']);
    // Create an associative array with filenames and their modification times
    $fileModTimes = [];
    foreach ($files as $file) {
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_file($filePath)) {
            $fileModTimes[$file] = filemtime($filePath);
        }
    }
    // Sort files by modification time in descending order
    arsort($fileModTimes);
    return array_keys($fileModTimes);
}

// $aa = readFileData('/data/import/1234.txt');
// $aa = iconv('gb2312', 'utf-8//ignore', $aa);
// echo '<pre>';
// var_dump($aa);
// echo '</pre>';
// exit;

$name_obj = new rndChinaName();

$save_path  = Env::get('SAVE_IMPORT_BOOK');
$list = getFilesInDirectory($save_path);
$insertData= [];
$i_num = 0;
if(!empty($list)){
    foreach($list as $val){
        if(!$val){
            continue;
        }
        $book_name = str_replace('.txt','',$val);
        $sql = "select id from {$table_novel_table} where title='{$book_name}'";
        $info = $mysql_obj->fetch($sql,$db_master);
        if(!$info){
            $bookInfo['title'] = $book_name; //标题
            $bookInfo['author'] = $name_obj->getName(2); //作者
            $bookInfo['img_path'] = $save_path.DS.'封面'.DS.$book_name.'.jpg'; //书籍的封面
            $bookInfo['createtime'] = time() ;
            $bookInfo['book_path'] = $save_path.DS.$val;
            $insertData[]=$bookInfo;
        }else{
            $i_num++;
            echo "id ={$info['id']}\tbook_name= {$book_name} \texists\r\n";
        }
    }
}

echo "===========实际待需要插入的小说有 " . count($insertData) . "本，会自动同步\r\n";
echo "******************已存在的小说有 {$i_num}本，状态会自动处理为待同步\r\n";
if ($insertData) {
    //同步数据
    $ret = $mysql_obj->add_data($insertData, $table_novel_table, $db_master);
    if (!$ret) {
        echo "数据库数据同步失败\r\n";
    }
    echo "导入小说列表成功 \r\n";
} else {
    echo "暂无小说需要插入的数据同步\r\n";
}

echo "finish\r\n";
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
echo "over\r\n";
// $executionTime = $exec_end_time - $exec_start_time; //执行时间
// echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";


?>