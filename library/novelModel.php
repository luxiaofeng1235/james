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


    public static $redis_expire_time = 7200; //默认2个小时
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

   public static $prefix_html = 'detail_'; //html的前缀

   protected static $run_status = 1;//已经运行完毕的

   public static $is_no_async = 0;//未同步的


   public static $file_type = 'txt'; //存储为txt的格式文件

   public static $json_file_type ='json';//存储为json文件格式

   //过滤不必要的章节
    private static $filterWords = [
        '新书',
        '发布',
        '新书预告',
        '番外',
        '高考祝愿',
        '上架',
        '通知',
        '冲榜求票',
        '请假',
        '注释',
        '完本感言',
        '完结'
    ];
    private static $db_conn = 'db_novel_pro';
    private static $table_name = 'mc_book';



     /**
    * @note 返回对应的cmd的具体的路径信息
    * @param $data 需要转换的数据
    * @return array
    *
    */
    public static function cmdRunPath(){
      $base_dir = ROOT.'library' .DS;
      $base_dir = str_replace('\\','/',$base_dir);
      return $base_dir;
    }

    /**
    * @note 转换数据信息
    * @param $data 需要转换的数据
    * @return array
    *
    */
    public static function changeChapterInfo($data){
        if(!$data)
          return false;
         foreach($data as $key =>$val){
          $link_url = $val['chapter_link'] ?? '';
            $pathData = parse_url($link_url);
            $data[$key]['link_url'] = $pathData['path'] ?? '';
            $data[$key]['link_name'] = $val['chapter_name']??'';
         }
        return $data;
    }


     /**
    * @note 移除不必要的广告章节
    * @param $data array需要处理的数据
    *
    */
    public static function removeAdInfo($data){
      if($data){
         foreach($data as $key =>$val){
              foreach(self::$filterWords as $v){
                   if( strstr($val['link_name'] , $v)){
                          unset($data[$key]);
                          break;
                   }
              }
         }
         $list = array_values($data);
         return $list;
      }
    }

   /**
  * @note 获取上次运行的最大ID
  *
  */
    public static function getMaxRunId(){
      global $mysql_obj;
      $sql = "select max(store_id) as last_store_id from ".Env::get('APICONFIG.TABLE_NOVEL')." where syn_chapter_status =".self::$run_status;
      $info = $mysql_obj->fetch($sql,'db_slave');
      $last_max_id = $info['last_store_id'] ?? 0;
      return $last_max_id;
    }

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
    * @note 从特定的url中获取对应的数据信息
    * @param $html string 文本内容
    * @return array
    *
    */
   public static function getCharaList($html){
      if(!$html){
        return '';
      }
      //</div><dt>
      preg_match('/<\/div>.*?>.*?<\/dl>/ism',$html,$list);
      if(isset($list[0]) && !empty($list)){
           $list_item= preg_split('/<dt>/', $list[0]);
           $contents  = $list_item[2] ?? '';
           if($contents){
              preg_match_all('/<a.+?href=\"(.+?)\".*>/i',$contents,$link_list);//匹配链接
              preg_match_all('/<a href=\"[^\"]*\"[^>]*>(.*?)<\/a>/i',$contents,$link_text);//匹配文本
              $len = count($link_list[1]);
              $chapter_list = [];
              for ($i=0; $i <$len ; $i++) {
                 $chapter_list[] =[
                    'link_name' => $link_text[1][$i],
                    'link_url'  => $link_list[1][$i],
                 ];
              }
              return $chapter_list;
           }
      }else{
        return array();
     }
   }

  /**
  * 简单的日志信息输出
  * @param string $msg
  */
  public function log($message=""){
    echo "[" . date ( "Y-m-d H:i:s", time () ) . "]--" . $message . "\n";
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
      //判断文件是否存在，如果不存在就直接保存到本地
      if(!file_exists($filename)){
        $save_img_path =Env::get('SAVE_IMG_PATH');
        if(!is_dir($save_img_path)){
            createFolders($save_img_path);
        }
        //开启使用代理IP去请求
        $res = MultiHttp::curlGet([$url],null,true);
        $img_con = $res[0] ?? '';
        // $img_con = self::curl_file_get_contents($url);
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
      $info['author'] = $info['author'] ? trim($info['author']) : '未知';
      //处理图片的存储路径
      if(!empty($info['pic'])){
          $cover_logo =  explode('/',$info['pic']);
          $info['pic'] = Env::get('SAVE_IMG_PATH') . DS . end($cover_logo);
      }
      //根据书籍名称和坐着来进行匹配
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
  public static function createJsonFile($info=[],$data=[],$pro_book_id = 0){
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
                'addtime'   =>(int) $val['createtime'],
          ];
      }
      $save_path = Env::get('SAVE_JSON_PATH');//保存json的路径
      if(!is_dir($save_path)){
        createFolders($save_path);
      }
      $filename = $save_path . DS . $pro_book_id.'.'.self::$json_file_type;
      //保存对应的数据到文件中方便后期读取
      $json_data = json_encode($json_list ,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
      file_put_contents($filename,$json_data);//把json信息存储为对应的目录中去
      return $json_list;
  }


   /**
    * @note 从远程获取相关内容并存储到缓存里
    *
    * @param $url string 链接地址
    * @param $timeout stirng 缓存的超时时间
    * @param $tag string 缓存标记
    * @return string
    */
  public static function getRemoteHmtlToCache($url , $tag ='',$timeout = 0){
      if(!$url || !$tag){
          return '';
      }
      if(!is_array($url)){
          $t_url[] = $url;
      }else{
          $t_url = $url;
      }
      global $redis_data;
      $content = $redis_data->get_redis($tag);
      if(!$content){
            //使用代理获取数据
            $info = MultiHttp::curlGet($t_url,null,true);
            $content = $info[0] ?? '';//获取内容信息
            $redis_data->set_redis($tag, $content,$timeout);
      }
      return $content;
  }
}
?>