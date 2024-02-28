<?php
/**
*名称:syc_novel_list
*作用:移动文件夹到指定的目录下
*说明:
*版权:
*作者:Red QQ:513072539
*时间:2024/2/28
*/
require_once(dirname(__DIR__).'/library/init.inc.php');
$table_local_name ='ims_novel_info';
$sql = "select title,story_id,pro_book_id from ".$table_local_name." where pro_book_id>0 and story_id='174_174667'";
$list = $mysql_obj->fetchAll($sql,'db_slave');
$remove_dir = 'E:\chapter\\';
foreach($list as $key =>$val){
    $download_path = ROOT . 'log' . DS . 'chapter' .DS . $val['story_id'];
    $save_path = $remove_dir.$val['pro_book_id'];
    $shell_cmd = "mv ".$download_path.' '.$save_path;
    if(is_dir($download_path)){
        exec($shell_cmd,$output,$status);
    }
}
echo "over\r\n";
?>