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

    public static $dict_exchange = [
       'title'     =>  'book_name',//小说书名
      'cover_logo'       =>  'pic',//小说封面
      'author'          =>      'author',//作者
      'tag'       =>  'tags',//标签
      'intro'          =>  'desc',//简介
      'nearby_chapter'             =>   'last_chapter_title',//最新章节
      'story_link'          =>  'source_url',//采集来源
      'cate_name'          =>  'class_name',//小说分类名称
  ];
    private static $db_conn = 'db_novel_pro';
    private static $table_name = 'mc_book';

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

    /**
    * @note 获取远程图片
    *
    * @param $cate_name str分类名称
    * @return string
    */
    public static function curl_file_get_contents($durl){
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $durl);
       curl_setopt($ch, CURLOPT_TIMEOUT, 2);
       curl_setopt($ch, CURLOPT_ENCODING,'gzip');
       // curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
       curl_setopt($ch, CURLOPT_REFERER,0);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $r = curl_exec($ch);
       curl_close($ch);
       return $r;
    }


     /**
    * @note 远程抓取图片保存到本地
    *
    * @param $cate_name str分类名称
    * @return string
    */
    public static function saveImgToLocal($url){
      if(!$url){
          return false;
      }
      $save_img_path = Env::get('SAVE_IMG_PATH');
      $t= explode('/',$url);
      $filename = $save_img_path . DS . end($t);
      if(!file_exists($filename)){
        $save_img_path =Env::get('SAVE_IMG_PATH');
        if(!is_dir($save_img_path)){
            createFolders($save_img_path);
        }
        $img_con = self::curl_file_get_contents($url);
        @file_put_contents($filename, $img_con);
      }
    }

     /**
    * @note 转换对应的字段信息并同步数据到mc_book表
    *
    * @param $data 预处理的数据
    * @param $mysql_obj string 连接句柄
    * @return string
    */
  public static  function exchange_book_handle($data,$mysql_obj){
      if(!$data)
          return false;
      //先按照源数据进行判断
      $ex_key =[];
      foreach(self::$dict_exchange as $key  => $val){
          if(!$key)
              continue;
          $ex_key[$key] = 1;
      }
      foreach($data as $key =>$val){
          if(isset($ex_key[$key])){
              $info[self::$dict_exchange[$key]]=trim($val);
          }
      }

      $info['chapter_title'] = $info['last_chapter_title'];
      $info['cid'] = self::getNovelCateId($info['class_name']);
      $info['addtime']  = time();

      //处理图片的存储路径
      if(!empty($info['pic'])){
          $cover_logo =  explode('/',$info['pic']);
          $info['pic'] = Env::get('SAVE_IMG_PATH') . DS . end($cover_logo);
      }
      $where_data = "book_name ='".trim($info['book_name'])."' and author ='".trim($info['author'])."' limit 1";
      $novelInfo = $mysql_obj->get_data_by_condition($where_data,self::$table_name,'id',false,self::$db_conn);
      if(empty($novelInfo)){
          $data=  handleArrayKey($info);
          $id =  $mysql_obj->add_data($data, self::$table_name ,self::$db_conn);
      }else{
          $id = intval($novelInfo[0]['id']);
      }
      return $id;
  }


    /**
    * @note 创建生成json文件
    *
    * @param $data 预处理的数据
    * @param $mysql_obj string 连接句柄
    * @return string
    */
  public static function createJsonFile($info,$data){
    if(!$data || !$info){
      return false;
    }
    /*
    $data[] = [
                'id'    =>$key+1 ,
                'sort'  =>$key+1,
                'chapter_link'  =>$val['link_url'],
                'chapter_name'  =>$val['link_name'],
                'vip'   =>  0,
                'cion'  =>  0,
                'is_first' =>   0,
                'is_last'   => 0,
                'text_num'  => 2000,
                'addtime'   =>$val['createtime'],
            ];

     */
      $json_list= [];
      foreach($data as $key =>$val){
         $json_list[] = [
                'id'    =>$key+1 ,
                'sort'  =>$key+1,
                'chapter_link'  =>Env::get('APICONFIG.PAOSHU_HOST') . $val['link_url'],
                'chapter_name'  =>$val['link_name'],
                'vip'   =>  0,
                'cion'  =>  0,
                'is_first' =>   0,
                'is_last'   => 0,
                'text_num'  => 2000,
                'addtime'   =>$val['createtime'],
          ];
      }
      return $json_list;
  }
}
?>