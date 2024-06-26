<?php
use Overtrue\Pinyin\Pinyin; //导入拼音转换类
use QL\QueryList;
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
      'title'          =>      'book_name',//小说书名
      'cover_logo'      =>      'pic',//小说封面
      'author'          =>      'author',//作者
      'tag'             =>      'tags',//标签
      'intro'           =>      'desc',//简介
      'nearby_chapter'  =>      'last_chapter_title',//最新章节
      'story_link'      =>      'source_url',//采集来源
      'cate_name'       =>      'class_name',//小说分类名称
      'status'          =>      'serialize',//是否完本状态
      'third_update_time' =>    'last_chapter_time',//网站的第三方时间
   ];
   private static $validate_type =['curl','ghttp']; //验证类型 支持curl和ghttp验证
   private static $imageType ='jpg';//默认的头像
   public static $prefix_html = 'detail_'; //html的前缀

   protected static $run_status = 1;//已经运行完毕的

   protected static $text_num =2000;//默认的的字数

   public static $is_no_async = 0;//未同步的

   private static $default_pic = '/mnt/book/pic/default_cover.jpg';//默认的图片


   public static $file_type = 'txt'; //存储为txt的格式文件

   public static $json_file_type ='json';//存储为json文件格式

   //过滤不必要的广告章节
    public static $filterWords = null;
    private static $db_conn = 'db_novel_pro';
    private static $table_name = null ;

    //过滤文本需要用的配置
    public static $filterContent = null; //过滤文本中的内容


    /**
  * @note 检测redis代理IP是否可用
  *
  */
    public static function checkProxyExpire(){
        global $redis_data;
        $redis_key = Env::get('ZHIMA_REDIS_KEY');
        if(!$redis_data->get_redis($redis_key)){
            return 0; //代理已过期
        }else{
            return 1; //代理可用
        }
        $content = $redis_data->get_redis($tag);
    }


  /**
  * @note 初始化连载状态以及其他的字段信息
  * @param $store_data array 抓取的数据信息
  * @param array
  *
  */
    public static function initStoreInfo($store_data=[]){
        if(!$store_data){
           return false;
        }
        $story_link = $store_data['story_link'];
        ///判断是否为xs74w网站的数据，因为这个网站的数据没有连载状态给他默认一个
         if(strpos($story_link,'xs74w')){
                $diff_time = date('Y-m-d 00:00:00');
                $unixtime = strtotime($diff_time);
                //判断当前的时间是否大于最后更新的时间，如果大于就是说明已经完本了
                if(strtotime($store_data['third_update_time']) &&   $unixtime > $store_data['third_update_time']){
                    $status = '已经完本';
                }else{
                    $status = '连载中';
                }
                $store_data['status'] = $status;
         }

          //完结 连载
         if($store_data['status'] == '连载' || $store_data['status'] == '连载中'){
              $store_data['status'] = '连载中';
         }else if($store_data['status'] == '完结' || $store_data['status'] == '已完结'){
              $store_data['status'] = '已经完本';
         }else{
              $store_data['status'] = '未知';
         }
         return $store_data;
    }



    /**
  * @note 转换编码格式
  *
  */
    public static function iconv_utf8($data){
         if(!$data){
            return false;
        }
        if(count($data) == count($data,1)){
            foreach($data as &$val){
                //转换数组对象
                $val =iconv('gbk','utf-8',$val);
            }
        }else{
            //处理二维数组的转换处理
            foreach($data as $key =>$val){
                 foreach($val as &$v){
                    $v = $val =iconv('gbk','utf-8',$v);
                 }
                 $data[$key] = $val; //需要赋值一下，才能生效
            }
        }
        return $data;
    }


    /**
  * @note 检测 缓存跑书吧的首页
  * @param $url string 网站的url
  * @param $expire_time stirng 过期时间
  * @return array
  *
  */
    public static function cacheHomeList($url,$expire_time= 86400){
        if(!$url){
            return false;
        }
        //获取网站来源
        $source = NovelModel::getSourceUrl($url);
        $redis_key = $source . '_home_list';
        global $redis_data;
        $data = $redis_data->get_redis($redis_key);
        if(!$data){
            $list = webRequest($url , 'GET');
            $redis_data->set_redis($redis_key , $list,$expire_time);
            $data = $list;
            unset($list);
        }
        return $data;
    }

        /**
    * @note 检测 图片的代理是否可用
    *
    */
    public static function checkImgKey(){
        global $redis_data;
        $redis_key = Env::get('ZHIMA_REDIS_IMG');
        if(!$redis_data->get_redis($redis_key)){
            return 0; //代理已过期
        }else{
            return 1; //代理可用
        }
        $content = $redis_data->get_redis($tag);
    }

     /**
  * @note 检测移动端的key是否可用
  *
  */
    public static function checkMobileKey(){
        global $redis_data;
        $redis_key = Env::get('ZHIMA_REDIS_MOBILE_KEY');
        if(!$redis_data->get_redis($redis_key)){
            return 0; //代理已过期
        }else{
            return 1; //代理可用
        }
        $content = $redis_data->get_redis($tag);
    }

    /**
    * @note 获取缓存里的mc_book表对应的id
    * @param $store_id int 小说id
    * @return interer
    *
    */
    public static function getRedisProId($store_id){
      if(!$store_id){
          return 0;
      }
      global $redis_data;
        $redis_key =  Env::get('REDIS_STORE_KEY') . $store_id;
        $pro_book_id = $redis_data->get_redis($redis_key);
       return $pro_book_id ? $pro_book_id :  0;
    }


  /**
  * @note 获取redis中小说的基础详情信息
  *
  */
    public static function getRedisBookDetail($store_id){
        if(!$store_id){
            return 0;
        }
        global $redis_data;
        $redis_key =  Env::get('REDIS_STORE_DETAIL_KEY');
        $info = $redis_data->hget_redis($redis_key , $store_id);
        if(!$info){
          $info = array();
        }else{
          $info = json_decode($info,true);
        }
        return $info ?? [];

    }

     /**
  * @note 检测移动端补数据的脚本呢的key
  *
  */
    public static function checkMobileEmptyKey(){
        global $redis_data;
        $redis_key = Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA');
        if(!$redis_data->get_redis($redis_key)){
            return 0; //代理已过期
        }else{
            return 1; //代理可用
        }
        $content = $redis_data->get_redis($tag);
    }


     /**
    * @note 返回对应的cmd的具体的路径信息
    * @param $data 需要转换的数据
    * @return array
    *
    */
    public static function cmdRunPath(){
      $base_dir = ROOT.'paoshu8' .DS;
      $base_dir = str_replace('\\','/',$base_dir);
      return $base_dir;
    }

  /**
  * @note 获取要执行的curl命令
  * @return str
  *
  */
    public static function getCommandInfo($list=[]){
      if(!$list)
        return [];
      $proxy_auth= self::getProxyItem();
      if(!$proxy_auth)
        return false;
      $urlArr= [];
      // $list1 = array_values($list);
      $data =array_column($list,'link_url');
      foreach($data as $key =>$val){
          $pathInfo = parse_url($val);
          $link_string= substr($pathInfo['path'], 1);
          $link_string = str_replace('.html','',$link_string);
          $urlArr[]=$link_string;
      }
      $curlHtml= implode(',',$urlArr);
      $str ='{'.$curlHtml.'}';
      $shell_cmd ='curl  -H "Content-Type:text/pln;charset=UTF-8"  -H "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36" --socks5  '.$proxy_auth.' -s  '.Env::get('APICONFIG.PAOSHU_HOST').'/'.$str .'.html';
      return $shell_cmd;
    }

     /**
    * @note 对比新旧数据进行返回处理
    * @param $old array 旧数据
    * @param $new array 新数据
    * @return array
    *
    */
    public static function arrayDiffFiled($old,$new){
      if(!$old || !$new){
          return false;
      }
      $diff_filed= array_diff_assoc($new,$old);
      if( !$diff_filed ){
          $diff_filed = [];
      }
      return $diff_filed;
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
          $data[$key]['link_str'] = $pathData['path'] ?? '';//兼容其他脚本去跑数据
          $data[$key]['link_name'] = $val['chapter_name']??'';
         }
        return $data;
    }


     /**
    * @note 移除不必要的广告章节
    * @param $data array 章节列表
    * @return object
    *
    */
    public static function removeAdInfo($data){
      global $advertisement;
      self::$filterWords = $advertisement['chapter']; //获取广告词
      if($data){
         foreach($data as $key =>$val){
              foreach(self::$filterWords as $v){
                   if( strstr($val['link_name'] , $v) ){
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
    * @note 替换里面的指定空行
    * @param $data array 处理数据
    * @return array
    *
    */
    protected static function replaceListArr($data=[]){
      if(!$data)
        return false;
      foreach($data as &$val){
         $val = str_replace("\r\n",'',$val);
      }
      return $data;
    }


     /**
    * @note 处理指定的HTML中的字符换行问题
    * @param $html string 文本内容
    * @return array
    *
    */
    public static function dealHtmlBr($string){
        if(!$string){
          return false;
        }
        $aa    =preg_split('/\r\n/',$string);
        foreach($aa as &$v){
            // $v = removeTabEnter($v);
        }
        echo "<pre>";
       var_dump($aa);
       exit;
        $data = implode('',$aa);
        //按照</dd>切割换行符进行转换
        $str  =preg_replace('/<\/dd>/',"</dd>\r\n",$data);
        return $str;
    }


    /**
    * @note 从特定的url中获取对应的数据信息
    * @param $html string 小说的详情内容信息
    * @param $title string 小说标题
    * @param $is_fanti_ex bool 是否为繁简体转换 默认false不转换 true：转换
    * @return array
    *
    */
   public static function getCharaList($html,$title='',$is_fanti_ex=false){
      if(!$html || !$title){
        return '';
      }
      $html = array_iconv($html); //转换编码格式
      //处理繁简体的转换文字

      # $link_reg = '/<a.+?href=\"(.+?)\".*>/i'; //匹配A连接
      $link_reg ='/<a href=\"([^"]+)\".*?>/';
      $text_reg ='/<a href=\"[^\"]*\"[^>]*>(.*?)<\/a>/si';//匹配链接里的文本
      //只取正文里的内容信息，其他的更新的简介不要
      //匹配正文章节内容

      //标题处理正则转义字符
      $title = str_replace('(','\(',$title);
      $title = str_replace(')','\)',$title);
      $title = str_replace('.','\.',$title);
      $title = str_replace(',','\,',$title);
      $title = str_replace('[','\[',$title);
      $title = str_replace(']','\]',$title);
      $title = str_replace('$','\$',$title);
      $title = str_replace('?','\?',$title);
      $title = str_replace('{','\{',$title);
      $title = str_replace('}','\}',$title);
      $title = str_replace('*','\*',$title);
      $title = str_replace('+','\+',$title);
      $title = str_replace('$','\$',$title);
      $title = str_replace('^','\^',$title);
      $title = str_replace('|','\|',$title);

      $contents = '';



      //<dt>《毒誓一九四一》正文</dt>
      //兼容这种带正文的正则
      if(preg_match('/《'.$title.'》正文.*<\/dl>/ism',$html,$with_content)){//带有正文的匹配
          $contents = $with_content[0] ?? [];
      }else if(preg_match('/<div id=\"list\".*?>.*<\/dl>/ism',$html ,$list)){//带有id="list"的规则
          $contents = $list[0] ?? [];
      }else if(preg_match('/<div class=\"info-chapters flex flex-wrap\">.*?<\/div>/ism',$html,$list)){//匹配台湾网站
          $contents = $list[0] ?? '';
      }
      //《我在古代办妇联》正文卷
      //《我的女装成长日常》正文卷
      //我的女装成长日常
      // preg_match('/《'.$title.'》正文.*<\/dl>/ism',$html,$list);
      if($contents){
              ////替换style的样式标签，防止采集不到数据
            $contents= str_replace('href =','href=',$contents);
            //处理中间的换行字符,不然匹配会出问题
            preg_match_all($link_reg,$contents,$link_href);//匹配链接
            preg_match_all($text_reg,$contents,$link_text);//匹配文本;
            $len = count($link_href[1]);
            $chapter_list = [];
            //回调函数处理去除换行
            $link_text = array_map('trimBlankLine',$link_text);
            for ($i=0; $i <$len ; $i++) {
               $chapter_list[] =[
                  'link_name' => $link_text[1][$i] ?? '',
                  'link_url'  => $link_href[1][$i] ?? '',
               ];
            }
            //处理繁简体转换
            if($is_fanti_ex){
              $chapter_list =  StoreModel::traverseEncoding($chapter_list);
            }
            return $chapter_list;
        }else{
          //如果上面的没有匹配出来直接从dd里获取对应的连接
          //直接暴力一点
          //直接从链接里开始遍历得了
          preg_match('/《'.$title.'》正文.*<\/dl>/ism',$html,$urls);
          $chapter_list = [];
          if(isset($urls[0])){
             $item = preg_split('/<dd>/', $urls[0]);
             $item = array_filter($item);
             foreach($item as $key =>$val){
                 if(strpos($val,'.html')){
                      preg_match($link_reg,$val,$t1);
                      preg_match($text_reg,$val,$t2);
                      if(isset($t1[1]) && !empty($t1[1])){
                          $chapter_list[] = [
                            'link_name' =>$t2[1]??'',
                            'link_url' =>$t1[1] ?? '',
                          ];
                      }
                 }
             }
          }
          return $chapter_list;
     }
   }

  /**
  * @note 获取M站的连接地址
  * @param string $msg
  * @return array
  */
   public static function mobileLink($url){
      if(!$url)
        return false;
      $urlArr = parse_url($url);
      $items = explode('/',$urlArr['path']);
      $novel_id = 0;
      if(isset($items[1])){
            $data = explode('_',$items[1]);
            $novel_id = $data[1] ?? 0;
      }
      //拼接获取对应的URL地址
      $link = Env::get('APICONFIG.PAOSHU_MOBILE_CHAPTER_URL').'-'.$novel_id.'-'.str_replace('.html','',$items[2]??0);
      $info['path'] = $urlArr['path'];
      $info['link'] = $link;
      return $info;
   }

   /**
  * 根据配置加载文件信息
  * @param integer $store_id 小说id
  */
   public static function reloadChapterTotal($store_id){
      if(!$store_id)
        return [];
      //加载配置文件信息
      $file_path = Env::get('SAVE_MOBILE_NUM_PATH').DS .$store_id.'.'. self::$json_file_type;
      //读取配置文件
      $goods_arr = readFileData($file_path);
      $list = json_decode($goods_arr,true);
      if(!empty($list)){
          //转换数组
          $items = [];
          foreach($list as $val){
            //按照 path=> pages对应去
             $items[$val['path']] = $val['pages'];
          }
          return $items ;
      }else{
        return [];
      }
   }

   /**
* @note 获取当前页面URL里的页码
*
* @param $str string 页面头部信息
* @return interger
*/
   public static function getCurrentPage($str){
    if(!$str){
      return 1;
    }
    $data = explode('/',$str);// (第1/3页) 替换
    if(!$data)
        return 1; //如果没有默认返回第一页
      if(isset($data[0])){
           $novelStr = explode('(',$data[0]);
           //只取最后一个
           $numData = end($novelStr);
           preg_match('/\d/',$numData , $matches);
           if(isset($matches[0])){
              $num = trim($matches[0]) ?? 0;
           }else{
            $num =1;
           }
      }else{
        //没有获取的默认给1
        $num = 1;
      }
      return $num;

   }

   /**
* @note 获取当前页面的总数量z
*
* @param $meta_data array meta信息
* @param $html string 当前抓取到的url
* @param $num 当前页码，默认只计算第一页
* @return string
*/
public static function  getChapterPages($meta_data='' , $first_line='',$num = 1){
    if(!$meta_data ||!$first_line)
        return false;

    $path =substr($meta_data, 0 , -1);
    // $page_num = 0;
    // $con_str = preg_split('/<\/p>/',$html); //按照P标签切割函数
    // $pages =str_replace("\r\n",'', $con_str[0]); //替换里面的空行
    // $content = filterHtml($pages);//过滤特殊字符
    $showData = explode('/',$first_line);// (第1/3页) 替换
    if(!$showData)
        return 1; //如果没有默认返回第一页
    $end_string = end($showData); //只取最后一个，如果有多余的就有问题
    preg_match('/\d/',$end_string,$allPage);
    $everyPages  = $allPage[0] ?? 1;
    $all_num = intval($everyPages); //总的页码数量，需要判断是否有1页以上
    $path_info =  $path . '-'.$num;
    $info[$path_info] = $all_num; // 按个格式进行拼装
    return $info;
}


   /**
  * 转换URL连接诶地址
  * @param array $data 章节类目
  * @param $store_id int 小说ID
  * @param $pro_type string count:同步章节用 empty：补空的章节用
  * @return array
  */
   public static function exchange_urls($data=[] ,$store_id = 3,$pro_type='empty'){
      if(!$data)
        return [];
      // if(!$offset || $offset<0)
      //   $offset =3;
      //加载配置信息
      $count_arr = [];
      if($pro_type != 'count'){
        $count_arr = self::reloadChapterTotal($store_id);
      }

      $infos = [];
      foreach($data as $val){
          $url = trim($val['link_url']);
          if(!$url) continue;
          //转换获取M站的地址
           $mobile_link = NovelModel::mobileLink($url);
           $mobile_url = $mobile_link['link'] ?? '';//连接地址
           $url_path = $mobile_link['path'] ?? '';
           if($pro_type != 'count'){
               //判断是否存在当前加载的页码，如果存在就用配置里的
             if(isset($count_arr[$url_path])){
                $pages = intval($count_arr[$url_path]);
             }else{
                $pages =1;
             }
           }else{//只按照当前页计算
              $pages = 1; //如果是统计章节默认就1
           }
            // echo $pages.PHP_EOL;
           for ($i=0; $i <$pages ; $i++) {
                $s_url = $mobile_url.'-'.($i+1);
                $val['mobile_url'] = $s_url;
                $val['path'] = $url_path;
                $infos[]=$val;
           }
      }
      return $infos;
   }

   /**
  * @note 替换广告和一些特殊字符
  * @param string $str 小说内容
  * @param $referer_url string 回调域名地址
  * @param $html_path  具体的章节目录，方便处理过滤
  * @return $string
  */
   public static function replaceContent($str,$referer_url,$html_path){
      if(!$str){
        $str = '';
      }
     global $advertisement;
      self::$filterContent = $advertisement['content'] ?? [];
      if(!self::$filterContent)
        return '';
      foreach(self::$filterContent as $keywords){
          $matches = strstr($str, $keywords);
          preg_match('/'.$keywords.'/',$str,$matches);
          if(isset($matches[0])){
              $str= str_replace($matches[0],'',$str);
          }
      }

      //过滤实体标签，类似>&nbsp;&nbsp;&nbsp;&nbsp;这样子的过滤一下
     $str = preg_replace('/&[#\da-z]+;/i', '', $str);

     //替换注释部分的信息
     $str = preg_replace('/<!--(.*?)-->/','',$str);

      //////////////////////////广告和标签相关
      //过滤具体的html标签
      $str = preg_replace('/<script.*?>.*?<\/script>/ism','',$str);
      $str = preg_replace('/<br><br><br>/','',$str); //去除三个BR标签，没啥用
      //过滤网站中存在的广告

      $hostUrl = parse_url($referer_url);
      $url = trim($hostUrl['host']); //只需要不带https或者http域名的
      $url = preg_replace('/\//','\/',$url) ;
      $url = preg_replace('/\./','\.',$url);
      $mobile_url = str_replace('www','m',$url); //移动端的地址
      $text_reg="/请记住本书首发域名：{$url}.*?{$mobile_url}/iUs";
      # $text_reg1 ='/请记住本书首发域名.*?https:\/\/www\.xs74w\.com/iUs'
      // $str='请记住本书首发域名：www.mxgbqg.com。梦想文学网手机版阅读网址：m.mxgbqg.com';
      $str = preg_replace($text_reg, '', $str);

      $chapter_link = $referer_url . $html_path; //拼接对应的文章连
       //去除广告中的网页链接 -- (https://www.mxgbqg.com/book/91090932/22106863.html) ,每个章节里都有这样的一段话
      $str = str_replace("({$chapter_link})",'',$str);
      return $str;
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
    * @param $url string  图片地址的url
    * @param $title string 小说标题
    * @param $author string 作者
    * @param $pinyin_class object 拼音转英文翻译类
    * @return string
    */
    public static function saveImgToLocal($url,$title='',$author='',$pinyin_class=''){
      if(!$url){
          return false;
      }
      $save_img_path = Env::get('SAVE_IMG_PATH');
      //转换标题和作者名称按照第一个英文首字母保存
      if($title && $author){
          $imgFileName = self::getFirstImgePath($title,$author ,$url,$pinyin_class);
      }else{
          //默认从规则里url取
          $t= explode('/',$url);
          $imgFileName = end($t);
      }


      // header("Content-type: application/octet-stream");
      // header("Accept-Ranges: bytes");
      // header("Accept-Length: 348");
      // header("Content-Disposition: attachment; filename=".$imgFileName);
      $filename = $save_img_path . DS . $imgFileName;
      //判断文件是否存在，如果不存在就直接保存到本地
      $save_img_path =Env::get('SAVE_IMG_PATH');
      if(!is_dir($save_img_path)){
          createFolders($save_img_path);
      }
      //基准对比时间
      if(!file_exists($filename)){
          //匹配台湾的网站，用挂代理的方式进行访问
          if(preg_match('/twking/', $url)){
             //利用挂代理的方式来实现
              $imgList = StoreModel::swooleRquest($url);
              $res = array_values($imgList);
              $img_con = $res[0] ?? '';
          }else{
            //正常的请求url
            $res = webRequest($url  ,'GET'); //利用图片信息来下载
            $img_con = $res ?? '';
          }
           @writeFileCombine($filename , $img_con);
          // @$t=file_put_contents($filename, $img_con);
      }
      return $filename;
    }

     /**
    * @note 获取网站的url的配对的源
    *
    * @param $story_link array  网站的源URL
    * @return string
    */
    public static function getSourceUrl($story_link=''){
        if(!$story_link){
           return '';
        }
        $url= parse_url($story_link);
        $source = '';
        if(isset($url['host']) && !empty($url['host'])){
          //匹配以www.开头的到.结束的为当前的来源
          //比如www.baidu.com 只取baidu为当前的标识来源
            preg_match('/www\.(.*?)\./',$url['host'],$matrix);
            if(isset($matrix[1])){
                $source = trim($matrix[1]);
            }
        }
        if(!$source){
          $source = 'unknow';//未定义的或者未知的
        }
        return $source;
    }

    /**
    * @note 对返回的数组做排序
    *
    * @param $html_data array  文章列表
    * @return string
    */
    public static function sortHtmlData($html_data=[]){
      if(!$html_data)
        return [];
      foreach($html_data as $key =>$val){
          $pathArr= explode('-',$key);
          $num = end($pathArr);
          array_pop($pathArr);
          $path = implode('-',$pathArr);
          $new_data[$path][]=$num;
      }
      //排序计算
       $sortDataBlank = function ($arr) {
            $items = [];
            foreach ($arr as $k => $v) {
                  sort($v);//按照数组进行排序设置,以免数据错乱
                  $items[$k] = $v;
            }
            return $items;
        };
      //调用构造函数里的排序
      $new_data = $sortDataBlank($new_data);
      $novelList = [];
      if(!empty($new_data)){
        //按照数组来进行排序。返回数组信息
          foreach($new_data as $gkey =>$gval){
              foreach($gval as $k =>$v){
                  $index = $gkey.'-'.$v;
                  //.echo $index."\r\n";
                  if(isset($html_data[$index])){
                      $novelList[$index] = $html_data[$index] ?? '';
                  }
              }
          }
      }
      return $novelList;
    }


     /**
    * @note 获取缓存中的代理里的配置信息
    *
    * @return string
    */
    public static function getProxyItem(){
        $proxy_data = getZhimaProxy();
        if(!$proxy_data)
          return false;
        //获取代理的配置信息凭借wget的参数命令
        $proxyauth = $proxy_data['ip'] . ':' .$proxy_data['port'];
        if(isset($proxy_data['username']) && isset($proxy_data['password'])){
            $proxyauth.= $proxy_data['username'].':'.$proxy_data['password'];
        }
        return $proxyauth;
    }

     /**
    * @note 获取字符的首字母
    *
    * @param $title string  小说名称
    * @param $author string 作者名称
    * @param $url string URL图片信息
    * @param $pinyin_class object 实例化对象类
    * @return string
    */
    public static function getFirstImgePath($title , $author,$url,$pinyin_class = []){
       if(!$title){
          return false;
       }

       if(!empty($pinyin_class)){
          $pinyin = $pinyin_class;
       }else{
          $pinyin = new Pinyin();
       }
          //构造函数
        $trimBlank = function ($arr) use(& $pinyin){
          //保留数字的转换方式
          $ext_data =$pinyin->name($arr , PINYIN_KEEP_NUMBER);//利用多音字来进行转换标题
          $str ='';
          //利用空数据来进行转换
          if(!empty($ext_data)){
              foreach($ext_data as $val){
                //如果匹配到了数字就直接用数字返回，不需要做处理
                if(preg_match('/[0-9]/',$val)){
                    $str .=$val;
                }else{
                    $str .= $val[0];
                }
              }
          }
            return $str;
      };
      $cover_logo = '';
      if(!empty($url)){
          $title_string = $trimBlank($title);
          $author_string = $trimBlank($author);
          $imgInfo = pathinfo($url);
          $extension = $imgInfo['extension'] ?? self::$imageType;
          if(!empty($author_string)){
            //如果作者不为空，进行作者和标题链接
              $cover_logo =  $title_string.'-'.$author_string .'.'. $extension;
          }else{
            //如果作者为空，只计算标题
             $cover_logo =  $title_string .'.'. $extension;
          }
       }
       return $cover_logo;
    }


     /**
    * @note 获取加密的md5串
    *
    * @param $title 小说标题
    * @param $author string 作者名称
    * @return string
    */
    public static function getAuthorFoleder($title='',$author=''){
        if(!$title){
            return false;
        }
        $title =trim($title);
        $author =$author ?  trim($author) : '未知';
        return md5($title.$author);
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

      // $info['chapter_title'] = $info['last_chapter_title'];
      $info['cid'] = self::getNovelCateId($info['class_name']);
      $info['addtime']  = time();
      $info['author'] = $info['author'] ? trim($info['author']) : '未知';
      $info['book_name'] = trim($info['book_name']); //去除首尾空格
       //处理图片的存储路径
      if($info['book_name'] || $info['author']){
         //处理图片的存储路径问题，直接保存对应的按照：中文转换成英文，取英文的首字母，书名+作者的首字母计算返回
          $image_str = Env::get('SAVE_IMG_PATH') . DS. self::getFirstImgePath($info['book_name'],$info['author'],$info['pic']);
          if(!$image_str){
            $image_str =  self::$default_pic;
          }
          $info['pic'] = $image_str;
      }
      //处理小说是否完本状态
      if( $info['serialize'] == '连载中'){
          $serialize = 1;//连载
      }else if( $info['serialize'] == '已经完本'){
          $serialize = 2;//完结
      }else {
          $serialize =3;//太监
      }

      $info['text_num'] = self::getTextNum($info['book_name'],$info['author']);//小说字数
      $info['serialize'] = $serialize;
      $score = getScoreRandom();
      $score = round($score,1);
      $info['score'] = $score;//随机小数评分
      $info['read_count'] = rand(10,100000);//最新阅读数
      $info['hits'] = rand(1000,10000);//浏览数量
      $info['hits_month'] = rand(10000,20000);//月点击
      $info['hits_week'] = rand(5000,10000);//周点击
      $info['hits_day'] = rand(100,5000);//日点击
      $info['shits'] = rand(100,300);//收藏人气
      $info['read_count']  = rand(1000,5000);
      $info['search_count'] = rand(100,599);

      ////获取每次章节的最后一个更新的ID和名称
      $md5_str= NovelModel::getAuthorFoleder($info['book_name'] ,$info['author']);
      $json_file =Env::get('SAVE_JSON_PATH') .DS .$md5_str.'.' .NovelModel::$json_file_type;
      $json_data = readFileData($json_file);
      $chapter_item = json_decode($json_data,true);
      $info['chapter_num'] = count($chapter_item);
      if(!empty($chapter_item)){
          //获取最后一个数组元素
          $return = array_slice($chapter_item,-1,1);
          $infoArr = $return[0] ?? [];
          $info['update_chapter_id'] = $infoArr['id'] ?? 0; //最后一次更新的章节ID
          $info['update_chapter_title'] = $infoArr['chapter_name'] ?? '';//最后一次更新的章节名称
          $info['update_chapter_time'] = time();//最后的更新时间
      }
      //根据书籍名称和坐着来进行匹配
      $where_data = 'book_name ="'.$info['book_name'].'" and author ="'.$info['author'].'"  and source_url not like "%biquge34%" limit 1';
      self::$table_name = Env::get('TABLE_MC_BOOK'); //获取配置信息
      $novelInfo = $mysql_obj->get_data_by_condition($where_data,self::$table_name,'id',false,self::$db_conn);

      if(empty($novelInfo)){
          //插入入库
          $data=  handleArrayKey($info);
          $id =  $mysql_obj->add_data($data, self::$table_name ,self::$db_conn);
      }else{
        //更新书籍的主要信息
        $update_where = "id =".$novelInfo[0]['id'];
        unset($info['addtime']);
        //转义Windows的\更新的转义问题
        if(strpos($info['pic'],'\\')){
            $info['pic'] = str_replace('\\','\\\\' ,$info['pic']);
        }
        //$a= $mysql_obj->update_data($info,$update_where,self::$table_name,false,0,self::$db_conn);
        $id = intval($novelInfo[0]['id']);
      }
      return $id;
  }


    /**
    * @note 获取小说的标题
    *
    * @param $data 预处理的数据
    * @param $mysql_obj string 连接句柄
    * @return string
    */
  public static function getTextNum($title,$author){
     if(!$title || !$author){
        return 0;
     }
      $md5_str= self::getAuthorFoleder($title,$author);
      $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.self::$json_file_type;
      $info = readFileData($json_path);
      $num = 0;
      if($info){
        $t = json_decode($info,true);
        $num  =self::$text_num * count($t);
      }
      return $num;

  }

  /**
  * @note 清洗掉不需要的字段
  *
  * @param $items array 章节列表
  * @param $fileter_key interer 章节类目
  * @return array
  */
  public static function cleanArrayData($items = [],$filter_key=[]){
      if(!$items ||!$filter_key) return [];
      $list = [];
      foreach($items as $key => $val){
          $info = [];
          foreach($val as $k =>&$v){
              //如果在过滤的字段里，直接切除
              if(!in_array($k , $filter_key)){
                  $info[$k] = $v;
              }
          }
          $list[$key] = $info;
      }
      return $list;
  }


    /**
    * @note 创建生成json文件
    *
    * @param $data 预处理的数据
    * @param $mysql_obj string 连接句柄
    * @param $pro_book_id int 线上小说ID
    * @param $referer_url string 回调地址
    * @return string
    */
  public static function createJsonFile($info=[],$data=[],$pro_book_id = 0,$referer_url=''){
    if(!$data || !$info){
      return false;
    }
    //获取标题+文字的md5串
    $md5_str= self::getAuthorFoleder($info['title'],$info['author']);
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
                'chapter_link'  =>$referer_url . $val['link_url'], //抓取的地址信息
                'chapter_name'  =>$val['link_name'], //文件名称
                'vip'   =>  0,
                'cion'  =>  0,
                'is_first' =>   0,
                'is_last'   => 0,
                'text_num'  => rand(3000,10000),//随机生成文本字数统计
                'addtime'   =>(int) $val['createtime'],
          ];
      }
      $save_path = Env::get('SAVE_JSON_PATH');//保存json的路径
      //获取对应的json目录信息
      if(!is_dir($save_path)){
          createFolders($save_path);
      }
      $filename = $save_path . DS . $md5_str.'.'.self::$json_file_type;
      //保存对应的数据到文件中方便后期读取
      $json_data = json_encode($json_list ,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
      writeFileCombine($filename,$json_data);//把json信息存储为对应的目录中去
      return $json_list;
  }

    /**
     * @note 解析当前url的域名
     * @param $url string url地址
     * @return array
     */
  public static function urlHostData($url){
     if(!$url)
      return false;
    $hostData= parse_url($url);
    $referer_url = $hostData['scheme']  . '://' . $hostData['host'];
    return $referer_url;
  }

  /**
   * @note 自动补齐当前的buffer数据信息
   * @param $detail array 抓取到的匹配数据
   * @param $content string HMTL内容返回的数据
   * @return array
   */
  protected static function bufferMetaData($detail=[], $content=[]){
      if(!$detail &&  !$content){
          return false;
      }
      // if(preg_match('/otcwuxi/',$content)){ //校验锡海小说网
      //       if(empty($detail['meta_data']) || empty($detail['href'])){
      //         //匹配这一段开头的数据jumppage到</heade>中间的内容
      //          preg_match('/document\.onkeydown=jumpPage.*?<\/head>/ism',$content,$contentarr);
      //          $html = $contentarr[0] ?? '';
      //          if($html){
      //              //通过正则取出来对应额连接
      //               $meta_reg = '/zzurl=\'(.*?)\'/si'; //meta_data信息
      //               $link_reg  = '/bookurl=\'(.*?)\'/si' ; //href的主要信息
      //               preg_match($meta_reg,$html,$meta_link);
      //               preg_match($link_reg , $html,$href_link);
      //               $detail['meta_data'] = $meta_link[1] ?? '';//获取meta的基础信息
      //               $detail['href'] = $href_link[1] ?? '';
      //          }
      //       }
      // }
      $reg = '/lastread\.set.*<\/script>/';
      preg_match($reg,$content,$matches);
      $results ='';
      if(isset($matches[0])){
          $list = $matches[0] ?? '';
          preg_match('/\"\,\"\/chapter\/.*\.html/',$list,$chapterMatches);
          $results = $chapterMatches[0] ?? '';

          $results = str_replace('"','',$results);
          $results = str_replace(',','',$results);
      }
      $detail['meta_data'] = $results;

      return $detail;

  }

  /**
  *  @note 获取对应的请求数据信息
  * @param $txt_path string 存储的路径
  * @param $data array 需要处理的章节列表
  * @return array
  */
  public static function getDataListItem($data,$txt_path){
        if(!$data)
          return false;
          $chapterList=[];
          foreach($data as $key =>$val){
            //获取请求的域名
            $referer_url = self::urlHostData($val['chapter_link']);
              //章节的连接
            $mobilePath = $val['link_url'] ??'';
            $chapterList[$mobilePath] = [
                //拼装移动端的地址
                'save_path'  =>  $txt_path.DS.md5($val['link_name']).'.'.NovelModel::$file_type,
                'chapter_name'  =>  $val['chapter_name'],
                'chapter_link'  =>  $val['chapter_link'],
                'mobile_url'  => $val['chapter_link'], //兼容老数据
                'chapter_mobile_link' => $val['chapter_link'],
             ];
             $t_url[]=$val['chapter_link'];
          }
          global $urlRules;
          //获取采集的标识
          $valid_curl ='curl';
          $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content_replace'];
          //开启多线程请求,使用当前代理IP去请求，牵扯到部署需要再境外服务器
          //////////////////处理请求的链接start
          // $detail_proxy_type =ClientModel::getCurlRandProxy();//基础小说的代理IP
          $list= StoreModel::swooleRquest($t_url);
          // $list = curl_pic_multi::Curl_http($t_url,$detail_proxy_type);
          //获取随机的代理IP
          // $rand_str = ClientModel::getCurlRandProxy();
          //重试防止有错误的
          // $list  = NovelModel::callRequests($list , $chapetList,$valid_curl,$rand_str);
          $list = StoreModel::swooleCallRequest($list, $chapterList);
          if(!$list){
              return false;
          }


          foreach($list as $gkey =>$gval){
            //非跑书8需要做转换
            if(!preg_match('/paoshu8/',$gval)){
               $gval= iconv('gbk','utf-8//ignore', $gval);
            }
            $data = QueryList::html($gval)
                    ->rules($rules)
                    ->query()
                    ->removeHead()
                    ->getData();
            $html = $data->all();

            // //自动补全meta连接信息连接走下面的逻辑
            // $html  = NovelModel::bufferMetaData($html,$gval);
           $store_content = $html['content']  ?? '';// array_iconv($html['content']) : '';
            // $meta_data = $html['meta_data']??'';
            // $href = $html['href'];
            // if(!empty($meta_data)){
            //   $allnum++;
            // }
            // echo "gkey =".($gkey+1)."\t meta_data = {$meta_data}  \t len = ".strlen($gval)."\r\n";



            //组装html_path的信息
            // $html_path = getHtmlUrl($meta_data,$gval);
            // $html_path = $meta_data ;
            //替换内容里的广告
            $store_content = NovelModel::replaceContent($store_content,$referer_url , $gkey);

            //如果确实没有返回数据信息，先给一个默认值
            if(!$store_content || empty($store_content)){
                $store_content ='未完待续...';
            }
            if($store_content){
              $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
              //替换文本中的P标签
              $store_content = str_replace("<p>",'',$store_content);
              $store_content = str_replace("</p>","\n\n",$store_content); //如果内容是P标签，用P来替换
              $store_content = str_replace("<br>","\n",$store_content);//如果内容是br标签用br来进行替换
              //替换try{.....}cache的一段话JS这个不需要了,还有一些特殊字符
              $store_content = preg_replace('/{([\s\S]*?)}/','',$store_content);
              $store_content = preg_replace('/try\scatch\(ex\)/','',$store_content);
              $store_content = preg_replace('/content1()/','',$store_content);
            }
            // $store_detail[$html_path] = $store_content;
            $store_detail[$gkey] = $store_content;
          }

          $allNovelList =[];
          if(!empty($store_detail)){
              foreach($store_detail as $gtkey=>$gtval){
                //获取对应的键值数据信息
                $chapter_info = isset($chapterList[$gtkey]) ? $chapterList[$gtkey] : '';
                $results =array_merge($chapter_info,['content'=>$gtval ?? '']);
                $allNovelList[$gtkey] = $results ?? [];
              }
        }
        return $allNovelList;
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





  /**
  * @note 获取移动端的组装的链接
  *
  * @param $data str array处理的数据
  * @return $html str 抓取的url 返回抓取后的curl请求连接
  */
  public static function getMobileHtmlUrl($meta_data , $html){
    if(!$meta_data ||!$html)
      return false;
      $path =substr($meta_data, 0 , -1);
      $page_num = 0;
      $con_str = preg_split('/<\/p>/',$html); //按照P标签切割函数
      $pages =str_replace("\r\n",'', $con_str[0]); //替换里面的空行
      $content = filterHtml($pages);//过滤特殊字符
      $t = explode('/',$content);// (第1/3页) 替换
      if(!$t)
        return [];
      preg_match('/\d/',$t[1],$allPage);
      $all_num = intval($allPage[0]); //总的页码数量，需要判断是否有1页以上


      if(isset($t[0]) && !empty($t[0])){
            $info = explode('(',$t[0]);//按照(来分割字符
            $lastElement = array_pop($info); //只取最后一个,如果存在多个(匹配会有问题
            if(isset($lastElement) && !empty($lastElement)){
                 //匹配含有字符的数据内容
                preg_match('/\d/', $lastElement,$matches);
                if(isset($matches[0])){
                    $page_num = intval($matches[0]);
                }
            }
        }
      //组装移动端内容的数据
      $mobile_url = $path . '-'.$page_num;
      return $mobile_url;

  }


    /**
  * @note curl抓取远程章节类目并组装数据
  *
  * @param $data str array处理的数据
  * @return 返回抓取后的curl请求连接
  */

 public static function getHtmlData($data){
      global $urlRules;
      if(!$data)
          return false;
      foreach($data as $key =>$val){

        //处理连接地址有的部分是处理过url了
        $link_url = $val['mobile_url'] ?? '';//移动端的url
        $link_name = $val['link_name'];
        $tdk = parse_url($link_url);
        $link_str = $tdk['path'] ?? '';
        // if( !strstr($val['link_url'] , Env::get('APICONFIG.PAOSHU_HOST')) ){
        //     $link_url = Env::get('APICONFIG.PAOSHU_HOST') .$val['link_url'];
        // }else{
        //      $link_url =  $val['link_url'];
        // }
        $item[$link_str] = $val;
        $item[$link_str]['link_name'] = $link_name;
        $t_url[]= $link_url;

      }
      $t_url = array_unique($t_url); //防止url重复
      $list = guzzleHttp::multi_req($t_url,'empty'); //不适合含有404的页面会阻断程序
      // $list = curl_pic_multi::Curl_http($t_url,3);
      $list =array_filter($list ?? []);
      if(!$list)
        return [];
      //获取对应的curl的信息
      // $shell_cmd = NovelModel::getCommandInfo($item);
      // if(!$shell_cmd)
      //   return [];
      // //执行对应的shell命令获取对应的curl请求
      // $string = shell_exec($shell_cmd);
      // //防止返回空数据的情况，做特殊判断
      // if(!$string){
      //     return [];
      // }
      //按照正则来切割shell返回的内容
      // $s_content=preg_split('/<\/html>/', $string);
      // //过滤空的数组
      // $s_content =array_map('trim', $s_content);
      // //处理过滤账号信息
      // $list = array_filter($s_content);
      //获取采集的标识
       $rules =$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
      // $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
       $store_c =  [];
      foreach($list as $key =>$val){
          //<script id="ab_set_2">ab_set_2();</script>
          $data = QueryList::html($val)->rules($rules)->query()->getData();
          $html = $data->all();
          $store_content = $html['content'] ?? '';
          $meta_data = $html['meta_data']??'';
          // $href = $html['href'] ?? '';
          //获取拼装的html路径信息
          // $html_path = getHtmlUrl($meta_data,$href);
          //获取当前的url信息
          $html_path = self::getMobileHtmlUrl($meta_data,$store_content);
          // echo $html_path.PHP_EOL;
          //判断如果不是503返回且无内容 并且确实这个章节没内容
          //如果是404直接跳出去
          if(strstr($val, '您请求的文件不存在')){
              continue;
          }

          //非503为空访问
          //503 Service
          //请求失败
          if(!strstr($val, '请求失败') && !$store_content){
              $store_content ='此章节作者很懒，什么也没写';
          }

          if($store_content){
              //处理剔除第一行的标题显示和相关广告
              $store_content= self::removeLineData($store_content);
              $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
              //替换文本中的P标签
              $store_content = str_replace("<p>",'',$store_content);
              $store_content = str_replace("</p>","\n\n",$store_content);
              //替换try{.....}cache的一段话JS这个不需要了
              $store_content = preg_replace('/{([\s\S]*?)}/','',$store_content);
              $store_content = preg_replace('/try\scatch\(ex\)/','',$store_content);
          }
          $store_c[$html_path] = $store_content;
      }
      // echo '<pre>';
      // print_R($store_c);
      // echo '</pre>';
      // exit;
      //组装contents的内容
      foreach($item as $k =>$v){
          $item[$k]['content'] = isset($store_c[$k])  ? $store_c[$k] : '';
      }
      //获取对象的内容信息
      $return_list = self::reloadMultiCon($item);
      //还原数据
      $arr_list= array_values($return_list);
      //需要单独处理下内容的切割
      return $arr_list;
  }

   /**
  * @note 剔除第一场的数据不要移动端的标题
  *
  * @param $content str 保存的内容设置
  * @param $tag str 设置按照什么进行切割
  * @return string
  */
  public static function removeLineData($content=''){
    if(!$content)
      return '';
    //按照正则p的标签七个开相关的信息
     preg_match_all('/<p.*?>.*?<\/p>/ism',$content,$matches);
     if(isset($matches[0]) && !empty($matches[0])){
        $list = $matches[0];
        //移除第一行的字样 类似“光明皇帝 (第3/3页)”这种不需要出现在正文中
        if(isset($list[0])){
            array_shift($list);
        }
        if(!$list) return ''; //防止有空数据
        $item = [];
        foreach($list as $key =>$val){
          //只判断最后一行字是否存在这些字样
            if(!strstr($val,'本章未完，请点击下一页继续阅读') || !strstr($val,'点击下一页继续阅读')){
                $item[] = $val;
            }
        }
        if(!$item) return '';
        //还原文章信息
        $return_content = implode('',$item);
        return $return_content;
     }else{
        return '';
     }

  }


  /**
* @note 自动把移动端的内容分割成一个对象中
*
* @param $data str 待保存的数据
* @return 返回抓取后的curl请求连接
*/
  protected static function reloadMultiCon($data){
    if(!$data)
      return [];
    $list = $contents = [];
    foreach($data as $key =>$val){
        $link_url = $val['link_str']; //指定文章的key内容
        $contents[$link_url][]=$val['content'];
        if(isset($val['content']))   unset($val['content']);
        $list[$link_url]=$val;
    }
    //组装内容，这里比较特殊需要处理下
    foreach($list as $k =>$v){
        $content_str = '';
        if(isset($contents[$k])){
             $content_str = implode('',$contents[$k]);
        }
        $list[$k]['content'] = $content_str;
    }
    return $list;
  }



/**
* @note 保存本地文件信息
*
* @param $data str 待保存的数据
* @return 返回抓取后的curl请求连接
*/
public static function saveLocalContent($data){
    if(!$data)
        return false;
    $y = $n = 0;
    foreach($data as $key =>$val){
         if(!$val)
            continue;
        if($val['content']){
            $y++;
            echo  "num：".($key+1)." chapter_name = {$val['link_name']}\t -- success  file to loal path：{$val['file_path']} \r\n";
            //同步写入文件，移动端由于数据被分割成N*M个URL后，可能一篇小说会同时出现在上下分页对应的数据中，所以必须追加，不然会按照最新的显示
            $a = writeFileAppend($val['file_path'],$val['content']);
        }else{
            $n++;
            echo "num：".($key+1)." chapter_name = {$val['link_name']} --  error  ".$val['link_url'] ." no data\r\n";
        }
    }
    echo "this times success_num：".$y." error_num：".$n.PHP_EOL;
}

/**
* @note 重复调用请求，防止有空数据返回做特殊调用
*
* @param $content_arr array  请求的HTML数据
* @param $goods_list array 原始请求的校验数据
* @param $type string 验证类型 curl:curl请求验证 ghttp:采用guzzlehttp来验证
* @param $type 获取移动端的代理配置 4：列表的代理 2：统计移动端页面的代理 3：修补空数据的代理
* @return unnkower
*/
public static function callRequests($contents_arr=[],$goods_list=[],$type='',$proxy_type=''){
   if(!$contents_arr || !$goods_list)
     return [];
    if(!in_array($type,self::$validate_type)){
        $type ='curl'; //默认采用curl来验证
    }
    $goods_list = array_values($goods_list);
   //获取出来成功和失败的数据
    $returnList= NovelModel::getErrSucData($contents_arr,$goods_list,$type);
    // echo '<pre>';
    // print_R($returnList);
    // echo '</pre>';
    // exit;
    //取出来成功和失败的数据
    $sucData = $returnList['sucData'] ?? []; //成功的数据
    $errData = $returnList['errData'] ?? []; //失败的数据
    echo "success_num：".count($sucData)."\terror_num：".count($errData).PHP_EOL;
    $repeat_data = $curl_contents1 =[];
    if(!empty($errData)){
        $successNum = 0;
        $old_num = count($errData);
        $urls = array_column($errData, 'mobile_url'); //进来先取出来
        while(true){//连接总数和请求成功数量不一致轮训
            //重新请求对应的信息
            $curl_contents1 = curl_pic_multi::Curl_http($urls,$proxy_type);
            // echo "================11111\r\n";
            $temp_url =[];//设置中间变量，如果是空的，就需要把对应的URL加到临时变量里
            foreach($curl_contents1 as $tkey=> $tval){
              //防止有空数据跳不出去,如果非请求失败确实是空，给一个默认值
              if($type =='ghttp'){//ghttp验证方式
                //判断返回页面里不为空的情况，还要判断是否存在异常 如果不是200会返回guzzle自定义错误根据这个来判断
                if(empty($tval)){//为空的情况
                    echo "章节数据内容为空，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                 }else if(!preg_match('/id="content"/',$tval) ){//断章处理，包含有502的未响应都会
                    echo "有断章，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                  }else{
                      $repeat_data[] = $tval;
                      unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                      unset($curl_contents1[$tkey]);
                      $successNum++;
                  }
              }else if($type =='curl'){//采用curl来验证
                 if(empty($tval)){//为空的情况
                    echo "章节数据内容为空，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                 }else{
                      $repeat_data[] = $tval;
                      unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                      unset($curl_contents1[$tkey]);
                      $successNum++;
                  }
              }
          }

          $must_count = count($temp_url);
          if($must_count>0){
              echo "---------------当前有（".$must_count."）个URL需要重新去获取\r\n";
          }
          $urls = $temp_url; //起到指针的作用，每次只存失败的连接
          $urls = array_values($urls);//重置键值，方便查找
          $curl_contents1 =array_values($curl_contents1);//获取最新的数组
          //如果已经满足所有都取出来就跳出来
          if($old_num == $successNum){
              echo "数据清洗完毕等待入库\r\n";
              break;
          }
          // sleep(1);
        }
    }
    //合并最终的需要处理的数据
    $finalData = array_merge($sucData , $repeat_data);
    return $finalData;
}

/**
* @note 保存HTML实体到指定目录
*
* @param $noveList arrya 小说列表
* @return bool
*/
public static function saveDetailHtml($novelList=[]){
  if(!$novelList){
    return false;
  }
  $combineData = [];
  $cache_path = Env::get('SAVE_HTML_PATH');
  foreach($novelList as $key =>$val){
      $urlPath  = $cache_path.DS.'detail_'.$val['story_id'].'.'.NovelModel::$file_type;
      //只有检测需要同步的数据才去保存
      if(!file_exists($urlPath)){
          $combineData[$urlPath]=$val;
      }
  }
  if(!$combineData) return true;
  $urls = array_column($combineData,'story_link');
  //获取关联的数据信息
  $list = curl_pic_multi::Curl_http($urls);
  //重复获取数据防止有漏掉的
  $list = NovelModel::callMultiListRquests($list,  $novelList);

  if(empty($list)){
      return false;
  }
  global $urlRules;
  //指定的规则
  $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['detail_url'];
  $store_content = [];
  foreach($list as $key =>$val){
      $data = QueryList::html($val)
          ->rules($rules)
          ->query()
          ->getData();
      $content = $data->all();
      $story_id = $content['path'] ?? '';
      $story_id = str_replace('/','',$story_id);
      //写入指定的文件信息
      $file_path  = $cache_path.DS.'detail_'.$story_id.'.'.NovelModel::$file_type;
      if(!empty($val)){
           $store_content[$file_path] = $val;
      }
      // writeFileCombine($file_path,$val); //写入文件操作
      // echo "path = $path \t";
  }
  if(!$store_content) return false;
  echo "=========================同步文件到指定缓存目录 {$cache_path}\r\n";
  $i = 0;
  foreach($combineData as $k =>$v){
      $i++;
      $content = $store_content[$k] ?? '';
      echo "num =".($i+1)."\ttitle = {$v['title']} \t author = {$v['author']}\t url = {$v['story_link']} path = {$k} \tHTML页面缓存成功\r\n";
      writeFileCombine($k,$content);//自动写入追加文件的HTML页面

  }
  return true;
 }


 /**
* @note 获取小说详情记录
*
* @param $story_id string  第三方网站的id
* @param $source string 来源 paoshu8：泡书吧 xsw：台湾小说
* @return unnkower
*/
 public static function getNovelInfoById($story_id='',$source='',$field ='store_id,title,author'){
    if(!$story_id || !$source){
      return false;
    }
    $sql = "select {$field} from ".Env::get('APICONFIG.TABLE_NOVEL')." where story_id='{$story_id}' and source='{$source}'";
    global $mysql_obj;
    $info = $mysql_obj->fetch($sql,'db_slave');
    return !empty($info) ? $info : [];
 }

/**
* @note 根据小说名+作者获取详情信息
*
* @param $title string  标题
* @param $author stirng 作者
* @param $field string  指定字段信息
* @return unnkower
*/
 public static function getNovelByName($title='',$author='',$field ='store_id,title,author,story_link'){
    if(!$title || !$author){
      return  false;
    }
    //整体思路：先从book_cener表中去搜索是否存在有此本小说
    //假如book_center表中存在此本小说就返回
    //如果条件2中的不满足，还需要从mc_book表中去检索，根据对应的状态去进行返回:
    //判断第三条里的数据是否满足，如果存在了就直接返回，不存在就说明是新书
    $sql = "select {$field} from ".Env::get('APICONFIG.TABLE_NOVEL')." where title='{$title}' and author='{$author}'";
    global $mysql_obj;
    $info = $mysql_obj->fetch($sql,'db_slave');
    if(empty($info)){
        //查询是否在mc_book表里有相关的数据信息
        $sql = "select id as store_id ,book_name as title,author,source_url as story_link from ".Env::get('TABLE_MC_BOOK')." where book_name='{$title}' and author='{$author}'";
        $results= $mysql_obj->fetch($sql ,self::$db_conn);
        return !empty($results) ? $results : [];
    }else{
        return !empty($info) ? $info : [];
    }
 }

/**
* @note 重复调用请求，防止有空数据返回做特殊调用--小说章节详情页检测
*
* @param $content_arr array  请求的HTML数据
* @param $goods_list array 原始请求的校验数据
* @param $proxy_type 获取移动端的代理配置 4：列表的代理 2：统计移动端页面的代理 3：修补空数据的代理
* @return unnkower
*/
public static function callMultiListRquests($contents_arr=[],$goods_list=[],$proxy_type=1){
     if(!$contents_arr || !$goods_list){
        return [];
     }
     $goods_list = array_values($goods_list);
     $errData  =  $sucData  = [];
     $patterns = '/id="list"/'; //按照正文标签来匹配，如果没有确实是有问题
     foreach($contents_arr as $key => $val){
        if(!preg_match($patterns, $val)){
            $errData[] =$goods_list[$key] ?? [];
        }else{
            $sucData[] = $val;
        }
     }
     $repeat_data = $curl_contents1 =[];
     //数据为空的情况判断
     if(!empty($errData)){
        $successNum = 0;
        $old_num = count($errData);
        $urls = array_column($errData, 'story_link'); //进来先取出来
        while(true){
            $curl_contents1 = curl_pic_multi::Curl_http($urls,$proxy_type);
            $temp_url =[];//设置中间变量
            foreach($curl_contents1 as $tkey=> $tval){
                if(empty($tval)){//为空的情况
                    echo "获取数据为空，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                 }else if(!preg_match($patterns,$tval) ){//断章处理，包含有502的未响应都会
                    echo "不全的HTML，会重新抓取======================{$urls[$tkey]}\r\n";
                    $temp_url[] =$urls[$tkey];
                  }else{
                      $repeat_data[] = $tval;
                      unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                      unset($curl_contents1[$tkey]);
                      $successNum++;
                  }
            }
            $urls = $temp_url; //起到指针的作用，每次只存失败的连接
            $urls = array_values($urls);//重置键值，方便查找
            $curl_contents1 =array_values($curl_contents1);//获取最新的数组
            if($old_num == $successNum){
                echo "数据清洗完毕等待入库\r\n";
                break;
            }
        }
     }
    $retuernList = array_merge($sucData , $repeat_data);
    return $retuernList;
}

/**
* @note 利用信号机制结束当前进程
*
* @return unknown
*/
public static function killMasterProcess(){
    echo "=====================================\r\n";
    posix_setsid ();
    echo "安装信号处理程序...\n";
    pcntl_signal(SIGTERM,  function($signo) {
        echo "信号处理程序被调用\n";
    });
    //获取当前主进程ID
    $masterPid = posix_getpid();
    if(!$masterPid){
        echo "no this process\r\n";

    }
    //杀掉当前已经存在的主进程
    posix_kill($masterPid, SIGTERM);
    echo "Worker Exit,killed by pid, PID = {$masterPid}\n";
    echo "分发处理信号程序...\n";
    echo "time =".date('Y-m-d H:i:s')."\r\n";
    pcntl_signal_dispatch();
}

/**
* @note 获取成功和失败的数据信息
*
* @param  $goods_list array 章节信息
* @param  $data array 关联章节数组
* @param $type staring 类型 curl :curl判断 ghttp:通过ghttp来判断
* @return array
*/
public static function getErrSucData($content,$data,$type='ghttp'){
    if(!$content)
        return [];
    //该网站被大量用户举报，网站含有未经证实的信息，可能造成您的损失，建议谨慎访问
    //strstr($tval, '请求失败')
    $sucData = $errData= [];
    foreach($content as $key => $val){
         if($type == 'ghttp'){
              //如果存在这个就需要去判断
            //如果存在503或者抓取有大量被举报的返回值就需要去判断
              if(empty($val)){//如果为空或者503错误就存储对应的记录信息或者是403的页面也需要重新抓取
                  $errData[] =$data[$key] ?? [];
              }else if(!preg_match('/id="content"/',$val) ){//断章处理
                  $errData[] =$data[$key] ?? [];
              }else{
                  $sucData[] = $val;
              }
          }else if($type =='curl'){ //采用curl来进行验证
          // if(empty($tval) || strstr($tval,'503 Service') || strstr($tval, '403 Forbidde')){
              if(empty($val)){//如果为空或者503错误就存储对应的记录信息或者是403的页面也需要重新抓取
                  $errData[] =$data[$key] ?? [];
              }else{
                  $sucData[] = $val;
              }
          }
    }
    $info['sucData'] = $sucData;
    $info['errData'] = $errData;
    return $info;
}

/**
* @note 获取url里的ID信息
*
* @param  $url array url信息
* @return array
*/
public static function getUrlById($url =""){
  if(!$url)
    return false;
  $urlArr = parse_url($url);
  $arr  = explode('/', $urlArr['path']);
  $arr = array_filter($arr);
  $ccx = end($arr);
  if(!$ccx) return false;
  return $ccx;
}


/**
* @note 获取缓存的基础信息
*
* @param  $url array url信息
* @return array
*/
public static function cacheStoryDetail($url, $expire_time = 87600){
    if(!$url) return false;
    $novel_id = NovelModel::getUrlById($url);
    $redis_cache_key ='xsw_detail_'.$novel_id;
    global $redis_data;
    $info = $redis_data->get_redis($redis_cache_key);
    if(!$info){
        //没有缓存从缓存中获取一次
        $info = webRequest($url,'GET');//获取页面缓存信息
        $redis_data->set_redis($redis_cache_key,$info,$expire_time);
        $data = $info;
    }else{
      //直接取出来信息
       $data  = $info;
    }
    return $data;
}


}
?>