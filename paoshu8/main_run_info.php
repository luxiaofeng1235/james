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
$sql = "select store_id from ".Env::get('APICONFIG.TABLE_NOVEL')." where is_async = ".NovelModel::$is_no_async." limit 1";
$list = $mysql_obj->fetchAll($sql,'db_slave');

if($list){
    foreach($list as $key =>$value){
        $store_id = intval($value['store_id']);
        if(!$store_id){
            continue;
        }
        $shell_cmd = 'nohup php gather_info_local.php '.$store_id.' &';
        exec($shell_cmd , $output,$status);
        echo '<pre>';
        print_R($shell_cmd);
        echo '</pre>';
    }
}
echo "over\r\n";
?>