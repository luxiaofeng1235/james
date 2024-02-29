<?php
/*
 * 处理小说的主要模型业务（暂时放在这里）
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
class NovelModel{


    /**
    * @note 自动加载分类配置文件
    *
    */
    public static function getCateConf(){
        if (is_file(dirname(__DIR__) . '/config/novel_class.php')) {
            $config = require  dirname(__DIR__) . '/config/novel_class.php';
            return $config;
        }else{
            return false;
        }
    }


    /**
    * @note 根据小说内容获取对应的分类id
    *
    * @param $cate_name str分类名称
    * @return string
    */

    public static function getNovelCateId($cate_name=''){
        $cate_list = self::getCateConf();
        if(!$cate_list)
            return false;
         $cate_id = 0;
         foreach($cate_list as $key =>$category_id){
            //根据标签的关键字来进行匹配分类
           if( strstr($cate_name , $key)){
                $cate_id = $category_id ;
                break;
            }
        }
         return $cate_id;
    }
}
?>