<?php
/*
 * 同步服务器上的图片信息
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
$sql ="select pic from ".Env::get('TABLE_MC_BOOK')." where id<100000 and pic!='' limit 3";
$info = $mysql_obj->fetchAll($sql,'db_novel_pro');
if(!empty($info)){
    foreach($info as $value){
        $image_path = $value['pic'] ?? '';
       if(!$image_path)
        continue;
        echo '<pre>';
        print_R($image_path);
        echo '</pre>';
        exit;
    }
}
?>