<?php
/*
 * 同步小说里的封面图到服务器上的图片信息
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
$db_name = 'db_novel_pro';
$sql ="select pic ,id as pro_book_id,book_name as title,author,source_url  from ".Env::get('TABLE_MC_BOOK')." where id<100000 and source_url regexp 'paoshu8' and pic regexp 'image' limit 20000";
$info = $mysql_obj->fetchAll($sql,$db_name);
if(!$info) $info = array();
if(!empty($info)){
    $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['info'];//抓取的规则
    foreach($info as $value){
        $image_path = $value['pic'] ?? '';
        $pro_book_id = intval($value['pro_book_id']);
        if(!$image_path) continue;
        if(!file_exists($image_path)){
            $url  = parse_url($value['source_url']);
            $files = Env::get('SAVE_HTML_PATH').DS.'detail_'.str_replace('/','',$url['path']).'.'.NovelModel::$file_type;
            $html = readFileData($files);
            if($html){
                $info_data=QueryList::html($html)
                    ->rules($rules)
                    ->query()->getData()->all();
                $cover_logo = $info_data['cover_logo'];
                //同步图片信息
                $img_path = NovelModel::saveImgToLocal($cover_logo,$value['title'],$value['author']);
                $update_sql = "update ".Env::get('TABLE_MC_BOOK')." set pic = '".$img_path."' where id = ".$pro_book_id;
                $mysql_obj->query($update_sql,$db_name);
                echo "update id：".$pro_book_id."\tpic：".$img_path.PHP_EOL;
                die;
            }
        }
    }
}else{
    echo "no data\r\n";

}
echo "count-num:".count($info)."\r\n";
echo "over\r\n";
?>
