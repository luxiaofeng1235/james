<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年6月12日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :auto_update_book.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:自动更新书籍上架
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
echo "开始寻找未开售的小说\r\n";
$db_conn_novel = 'db_master';
$db_conn_base ='db_master';
$table_novel_table = "mc_book";
$table_sync_table = "ims_novel_info";

//过滤条件信息
$where_data[] = "status =0";
$where_data[] = "(instr(source_url,'banjiashi')>0 or instr(source_url,'paoshu8')>0  or  instr(source_url,'ipaoshuba')>0)";
$where_data[] = "addtime<".time();
$where_data[] = "id not in (".Env::get('BLACK_BOOK_IDS').")";

$condition = implode(' and ',$where_data);

$sql = "select id,book_name,author,source_url from {$table_novel_table} where $condition";
$sql .=" order by id asc";
echo "sql = {$sql} \r\n";
$list = $mysql_obj->fetchAll($sql,$db_conn_novel);

if (!$list) {
    echo "暂无需要同步的书籍数据\r\n";
    exit();
    // code...
}
echo "需要同步的小说总数：".count($list)."\r\n";
foreach($list as $key =>$val){
   if(!$val){
        continue;
   }
   $pro_book_id = intval($val['id']);
   $book_name = trim($val['book_name']);
   $author = trim($val['author']);
   //检查当前是否有断章的书，如果有没有就同步更新
   checkBookIsAll($pro_book_id,$book_name,$author);
}



/**
* @note 检查当前小说是否存在断章宾更新
*
* @param  $pro_book_id int 小说ID
* @param $book_name string 小说名
* @param $author string 作者
* @return  unknow
*/
function checkBookIsAll($pro_book_id,$book_name,$author){
    if(!$book_name || !$author){
        return false;
    }
    $gofound = new GoFound();
    $file_name  =NovelModel::getBookFilePath($book_name,$author);
    $json_data = readFileData($file_name);
    if(!$json_data) {
        echo "当前小说未生成json数据，请稍后重试\r\n";
        return false;
    }

    $md5_str= NovelModel::getAuthorFoleder($book_name ,$author);
    #下载文件存储路径
    $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;
    $chapter_item = json_decode($json_data,true);
    $chapterListNum = count($chapter_item);
    $sucNum = 0;

    foreach($chapter_item as $key => &$val){
        if(!$val){
            continue;
        }
        $filename =$download_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
        $content = readFileData($filename); //读取文件内容
        if(
            !file_exists($filename)
            || !$content
            || $content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  
            || strstr($content,'暂无相关章节信息')
            || strstr($content, '未完待续...')

        ){
        }else{
            $sucNum++;
        }
    }
    echo "JSON文件里的总章节总数：".$chapterListNum."\t拉取下来的章节总数：{$sucNum}\r\n";
    //判断是否有断章
    if ($chapterListNum == $sucNum){
        echo "index = {$key} \t id ={$pro_book_id}\tbook_name ={$book_name}\t author = {$author} 章节内容无断章，更新完毕\r\n";
        $info['id'] = $pro_book_id;
        $info['book_name'] = $book_name;
        $info['author']  = $author;
        //同步迅搜索引数据
        XunSearch::addDocumentCli($info['id'] , $info['book_name'] ,$info['author']); 
        //更新状态为显示
        updateNovelState($pro_book_id); //更新小说信息
        //更新索引文件
    }else{
        echo "index = {$key} \t id ={$pro_book_id}\tbook_name ={$book_name}\t author = {$author} 存在断章，待处理完毕后更新\r\n";
    }
}

/**
* @note 更新小说状态
*
* @param  $pro_book_id int 小说ID
* @param $status int 小说状态
* @return  unknow
*/

function updateNovelState($pro_book_id=0,$status = 1){
    if(!$pro_book_id){
        return false;
    }
    $updateData['status'] = $status;
    global $mysql_obj,$db_conn_novel,$table_novel_table;
    $where = "id ={$pro_book_id} limit 1";
    $res = $mysql_obj->update_data($updateData , $where ,$table_novel_table,false,0,$db_conn_novel);
    if(!$res){
        return false;
    }
    return true;
}
echo "over \r\n";
?>