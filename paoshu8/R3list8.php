<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :synR3Client.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要: 利用守护进程来同步文件到oss上去，自动进行存储同步
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php'; //初始化配置
require_once dirname(__DIR__).'/library/R3ClientObject.class.php'; //存储桶的配置
$db_conn_novel = 'db_master';
$table_novel_table = "mc_book";
$exec_start_time = microtime(true);


$is_aws_store = 0;//只计算未同步到云盘的
$condition = "( instr(source_url,'http')>0 or instr(source_url,'qijitushu')>0 ) and id>300000 and id<360000 ";
$condition .= " and is_aws_store = {$is_aws_store}";
$id = isset($argv[1]) ? intval($argv[1]) : 0;
$order_by = " order by id asc";
if($id){
    $condition .=" and id =" . $id;
}
$sql = "select count(1) as num from {$table_novel_table} where {$condition}";
$ts_count = $mysql_obj->fetch($sql,$db_conn_novel);
$count = intval($ts_count['num']);
if(!$count){
    NovelModel::killMasterProcess(); //安装啥掉价昵称的程序
    echo "no data to syn oss\r\n";
    exit;
}
$storeCient = new R3ClientObject();
$limit =50;
$list_pages = ceil($count/$limit);
echo "共有 {$count}本书需要去同步 ,一共有{$list_pages}页数据，每页配置{$limit}条数据\r\n";
for($i = 0;$i<$list_pages;$i++){
    $sql = "select id,book_name,author,pic,source_url from {$table_novel_table} where {$condition}";
    if($order_by){
        $sql .=$order_by;
    }
    $sql .=" limit ".($i*$limit).", {$limit}";
    echo "sql = {$sql}\r\n";
    $list = $mysql_obj->fetchAll($sql , $db_conn_novel);
    //如果不为空就直接去执行远程的
    if($list || !empty($list)){
        //同步远程文件
        synToRemoteFile($list,$limit);
    }
    echo "list of  current_pages page = (".($i+1)."/".($list_pages).") \r\n" ;
}



/**
* @note 同步远程oss的文件
*
* @param  $list object 同步小说列表
* @param $limit int 步长
* @return  array
*/
function synToRemoteFile($list,$limit){
    if($list){
        echo "每次同步数据记录的总数为：".count($list)."\t配置的步长为：{$limit}\r\n";
        $synOssList = [];
        $ids =array_column($list, 'id') ;//需要处理的ID
        echo "需要处理的同步的小说ID = ".var_export($ids,true)." \r\n";
        $base_txt_path =Env::get('SAVE_NOVEL_PATH');
        foreach($list as $key =>$val){
            if(!$val) continue;
            $id = (int) $val['id']; //小说ID
            $book_name = trim($val['book_name']); //书名
            $author = trim($val['author']); //作者名
            $pic = trim($val['pic']);//小说封面
            echo "处理小说 ————key ={$key} \t id={$id} \t book_name = {$book_name} \t author = {$author}\r\n";
            //获取json的目录章节列表
            $json_file = NovelModel::getBookFilePath($book_name , $author); //获取书名
            //获取加密串
            $md5_str= NovelModel::getAuthorFoleder($book_name ,$author);
            $txtPath =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;
            //获取当前的文本信息
            $storeData['pic'] = $pic;
            if(file_exists($json_file)){
                $storeData['json_path'] =$json_file;
            }
            //获取的读取文件信息
            if(is_dir($txtPath)){
                //读取当前目录下的所有文件
                $txtFileList = getFilesInDirectory($txtPath);
                if(!empty($txtFileList)){
                    //存储获取小说的列表数据
                    $chapterList= getFileBaseForList($txtFileList,$txtPath);
                    $storeData['chapter_list'] = $chapterList;
                }
            }
            synFileToOss($storeData,$id); //同步文件到云端cloudfare中的桶里
            echo " index ={$key} \t id ={$id} \t title={$book_name}\t author = {$author} \t同步OSS的cloadfare完成\r\n";
        }
        NovelModel::killMasterProcess(); //安装啥掉价昵称的程序
    }else{
        echo "no records data\r\n";
        NovelModel::killMasterProcess(); //安装啥掉价昵称的程序
    }
}



/**
* @note 同步当前文件到云端
*
* @param  $chapterInfo array 小说基本存储文件
* @param $len_size int 切割的小说长度
* @param $id int 数据ID
* @return array
*/
function synFileToOss($chapterInfo=[],$id,$len_size = 200){
    if(!$chapterInfo){
        return false;
    }
    global $storeCient , $table_novel_table,$mysql_obj,$db_conn_novel;//初始化OSS实例
    $pic = $chapterInfo['pic']; //图片信息
    $json_path = $chapterInfo['json_path'];//json文件信息
    $chapter_list = $chapterInfo['chapter_list'];//小说目录
    $storeCient->handlePutFiles($pic);//同步图片
    $storeCient->handlePutFiles($json_path);//同步章节列表
    //由于小说的txt比较多，按照指定长度来进行分割
    $txtList = array_chunk($chapter_list, $len_size);
    // $pages = count($txt_list);
    $txtPath = dirname(reset($chapter_list));//获取上传的目录名称
    $allPages = count($txtList);//总分页书
    echo "upload picPath={$pic}\r\n";
    echo "upload jsonPath={$json_path}\r\n";
    echo "upload txtPath = {$txtPath} *********** 总分页数：{$allPages}\t章节目录总数 = ".count($chapter_list)."\t配置总长度={$len_size}\r\n";
    $processes = [];
    foreach($txtList as $key =>$pageList){
        // $ree = swooleTasks($pageList,$storeCient);
        $storeCient->multiPutFiles($pageList);//同步小说的txt文本
        echo "|||||||||||||||| chapter *********** this  current_pages = ( ".($key+1)."/".$allPages." ) \t id  ={$id} \t complate \r\n\r\n";
    }
    $sql = "update {$table_novel_table} set is_aws_store = 1 where id = {$id}";
    $mysql_obj->query($sql , $db_conn_novel); //处理完毕后更新对应的状态信息
    return true;
}

//利用swoole来完成批量任务
function swooleTasks($data=[],$storeCient = object){
    if(!$data){
        return false;
    }
    // 存放子进程的数组
    $processes = [];
    $urls = count($data);
   // 为每个 URL 创建一个子进程
    foreach ($data as $key =>$value) {
        $process = new Swoole\Process(function(Swoole\Process $worker) use ($key,$value,$storeCient) {
            //同步cloudfare流程信息
            $file_path = trim($value);
            $storeCient->handlePutFiles($file_path);
            // 将抓取到的内容写入管道
            $content = "index = {$key}\tfile = ".basename($value)." \t同步OSS操作信息";
            $worker->write($content);
        }, true); // 第二个参数表示开启管道通信

        // 启动子进程
        $pid = $process->start();
        $processes[$pid] = $process;
    }
    // 等待子进程完成并获取结果
    foreach ($processes as $pid => $process) {
        // 读取子进程的管道内容
        $content = $process->read();
        // echo "Content from process：".$pid."\r\n";
        echo "Content from process $pid: \t$content\n";
    }
    // 等待所有子进程退出
    Swoole\Process::wait(true);
    return true;
}


/**
* @note 获取当前文件的基准路径
*
* @param  $txt_list object 文件列表
* @param $txtPath string 文本路径
* @return array
*/
function getFileBaseForList($txt_list=[],$txtPath=""){
    if(!$txt_list){
        return [];
    }
    //过滤空字符串
    $txt_list = array_filter($txt_list, function($value) {
        return $value !== '';
    });
    $chapterList= [];
    foreach($txt_list as $key =>$chapter_path){
        if(!$chapter_path) continue;
        $chapterList[$key] = $txtPath . DS . $chapter_path;
    }
    return $chapterList;
}

echo "over\r\n";
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间

echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";