<?php
/*
 * 同步小说的主程序
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "5000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
//取出来所有未同步的数据信息
$where =  "is_async = ".NovelModel::$is_no_async;
// $where .=" and story_id ='95_95678'";

$sql = "select store_id from ".Env::get('APICONFIG.TABLE_NOVEL')." where $where limit 1";
// echo $sql;die;
$list = $mysql_obj->fetchAll($sql,'db_slave');
echo '<pre>';
print_R($list);
echo '</pre>';
exit;
if($list){
    //如果列表中存在可用数据信息
    foreach($list as $key =>$value){
        $store_id = intval($value['store_id']);
        $pro_book_id = intval($value['pro_book_id']);
        if(!$store_id){
            continue;
        }
        //挂起在后台去运行
        $shell_cmd = 'cd '.NovelModel::cmdRunPath().' && nohup '.Env::get('PHP_BIN_PATH').' gather_info_local.php '.$store_id.'  > '.Env::get('NOHUP_BIN_PATH').' 2>&1 &';
        echo 'shell-cmd：'. $shell_cmd . PHP_EOL;
        die;
        exec($shell_cmd , $output,$status);
        echo '<pre>';
        print_R($shell_cmd);
        echo '</pre>';
        echo PHP_EOL;
        echo " run store_id：".$store_id."\r\n";
    }
}
echo "over\r\n";
?>