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
    private static $table_name = 'mc_book';

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
       return $pro_book_id ?? 0;
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
    * @note 从特定的url中获取对应的数据信息
    * @param $html string 文本内容
    * @return array
    *
    */
   public static function getCharaList($html,$title=''){
      if(!$html || !$title){
        return '';
      }
      $link_reg = '/<a.+?href=\"(.+?)\".*>/i'; //匹配A连接
      $text_reg ='/<a href=\"[^\"]*\"[^>]*>(.*?)<\/a>/i';//匹配链接里的文本
      //只取正文里的内容信息，其他的更新的简介不要
      //匹配正文章节内容
      preg_match('/《'.$title.'》正文.*<\/dl>/ism',$html,$list);
      if(isset($list[0]) && !empty($list)){

           $contents = $list[0] ?? [];
           //获取相关的数据信息
           // $contents = self::replaceListArr($list_item);
           if($contents){
              preg_match_all($link_reg,$contents,$link_href);//匹配链接
              preg_match_all($text_reg,$contents,$link_text);//匹配文本
              $len = count($link_href[1]);
              $chapter_list = [];
              for ($i=0; $i <$len ; $i++) {
                 $chapter_list[] =[
                    'link_name' => $link_text[1][$i] ?? '',
                    'link_url'  => $link_href[1][$i] ?? '',
                 ];
              }
              return $chapter_list;
           }
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
   * @note
    *  //处理抓取中按照章节名称返回
    //将章节中的全角符号转换成英文
    //过滤调一些特殊分符号
    *
   * @param  $data array 处理的章节
   * @return  array
   */

  public static function removeDataRepeatStr($data){
      if(!$data) return false;
      $t= [];
      foreach($data as $key=>$val){
          $link_name = replaceLRSpace($val['link_name']); //只替换首尾空格，
          if(!empty($link_name)){
              $t[] = [
                  'link_name' =>$link_name,
                  'link_url'  =>$val['link_url']
              ];
          }
      }
      $t= array_values($t);
      //移除广告章节
      $list = NovelModel::removeAdInfo($t);
      return $list;
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
  * 替换广告
  * @param string $str
  * @return $string
  */
   public static function  replaceContent($str){
    if(!$str)
      return false;
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
    * @param $cate_name str分类名称
    * @param $title string 小说标题
    * @param $author string 作者
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
      header("Content-type: application/octet-stream");
      header("Accept-Ranges: bytes");
      header("Accept-Length: 348");
      header("Content-Disposition: attachment; filename=".$imgFileName);
      $filename = $save_img_path . DS . $imgFileName;
      //判断文件是否存在，如果不存在就直接保存到本地
      $save_img_path =Env::get('SAVE_IMG_PATH');
      if(!is_dir($save_img_path)){
          createFolders($save_img_path);
      }
      //获取图片的最后修改时间
      // $modify_time = filemtime($filename);
      //基准对比时间
      if(!file_exists($filename)){
          //开启使用代理IP去请求,由于服务器在海外要用代理去请求
           $res = MultiHttp::curlGet([$url],null,true);
          $img_con = $res[0] ?? '';
          @$t=file_put_contents($filename, $img_con);
      }
      return $filename;
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
    * @note 通过curl下载图片
    *
    * @param $title string  小说名称
    * @param $author string 作者名称
    * @param $url string URL图片信息
    * @param $mysql_obj string 连接句柄
    * @return string
    */
    public static function saveImgByCurl($url,$title ='',$author=''){
        if(!$url){
            return false;
        }
      $save_img_path = Env::get('SAVE_IMG_PATH');
      if(!is_dir($save_img_path)){
          createFolders($save_img_path);
      }
      //转换标题和作者名称按照第一个英文首字母保存
      if($title && $author){
          $imgFileName = self::getFirstImgePath($title,$author ,$url);
      }else{
          //默认从规则里url取
          $t= explode('/',$url);
          $imgFileName = end($t);
      }
      //实际保存的图片地址
      $filename = $save_img_path . DS . $imgFileName;
      $proxy_server = self::getProxyItem();//获取代理配置、
      //  $res = MultiHttp::curlGet([$url],null,true);
      // $img_con = $res[0] ?? '';
      // header("Content-type: application/octet-stream");
      // header("Accept-Ranges: bytes");
      // header("Accept-Length: 348");
      // header("Content-Disposition: attachment; filename=".$imgFileName);
      // $shell_cmd ='curl  -H "Accept: image/jpeg"  -H Accept-Encoding:gzip,defalte -o image.jpg  --socks5 '.$proxy_server.' '.$url;
      // echo '<pre>';
      // print_R($shell_cmd);
      // echo '</pre>';
      // exit;
      // shell_exec($shell_cmd);
      // echo 1;die;
      return 1;
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
       if(!$title || !$author){
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
          $cover_logo =  $title_string.'-'.$author_string .'.'. $extension;
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
        if(!$title || !$author){
            return false;
        }
        $title =trim($title);
        $author = trim($author);
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

      $info['chapter_title'] = $info['last_chapter_title'];
      $info['cid'] = self::getNovelCateId($info['class_name']);
      $info['addtime']  = time();
      $info['author'] = $info['author'] ? trim($info['author']) : '未知';
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
      //根据书籍名称和坐着来进行匹配
      $where_data = 'book_name ="'.$info['book_name'].'" and author ="'.$info['author'].'"  and source_url   not like "%biquge34%" limit 1';
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
    * @return string
    */
  public static function createJsonFile($info=[],$data=[],$pro_book_id = 0){
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
      //获取对应的json目录信息
      if(!is_dir($save_path)){
          createFolders($save_path);
      }
      $filename = $save_path . DS . $md5_str.'.'.self::$json_file_type;
      //保存对应的数据到文件中方便后期读取
      $json_data = json_encode($json_list ,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
      file_put_contents($filename,$json_data);//把json信息存储为对应的目录中去
      return $json_list;
  }

  /*
     * @param $txt_path string 存储的路径
     * @param $data array 需要处理的
     * @return mixed
     */
  public  static  function getDataListItem($data,$txt_path){
        if(!$data)
          return false;
        // dd($txt_path);
          $chapter_list=[];
          foreach($data as $key =>$val){
             //章节的连接
            $mobilePath = $val['link_url'] ??'';
            $chapetList[$mobilePath] = [
                //拼装移动端的地址
                'path'  =>  $txt_path.DS.md5($val['link_name']).'.'.NovelModel::$file_type,
                'chapter_name'  =>  $val['chapter_name'],
                'chapter_link'  =>  $val['chapter_link'],
                'mobile_url'  => $val['chapter_link'], //兼容老数据
                'chapter_mobile_link' => $val['chapter_link'],
             ];
            $t_url[]=Env::get('APICONFIG.PAOSHU_HOST'). $val['link_url'];
          }
          global $urlRules;
          //获取采集的标识
          $valid_curl ='curl';
          $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
          //开启多线程请求,使用当前代理IP去请求，牵扯到部署需要再境外服务器
          $detail_proxy_type =4;//基础小说的代理IP
          $list = curl_pic_multi::Curl_http($t_url,$detail_proxy_type);
          //获取随机的代理IP
          $rand_str = ClientModel::getCurlRandProxy();
          //重试防止有错误的
          $list  = NovelModel::callRequests($list , $chapetList,$valid_curl,$rand_str);
          if(!$list)
            return [];
          foreach($list as $gkey =>$gval){

            $data = QueryList::html($gval)->rules($rules)->query()->getData();
            $html = $data->all();

            $store_content = $html['content'] ?? '';
            $meta_data = $html['meta_data']??'';
            $href = $html['href'];
            //组装html_path的信息
            $html_path = getHtmlUrl($meta_data,$href);
            if(empty($meta_data) || empty($href) || empty($gval)){
                // echo "meta信息为空了 {$data[$gkey]['chapter_link']} \r\n";
                // echo '<pre>';
                // print_R($gval);
                // echo '</pre>';
                // echo "000000000000000000000000000000\r\n";
          }

            //替换内容里的广告
            $store_content = NovelModel::replaceContent($store_content);
            //如果确实没有返回数据信息，先给一个默认值
            if(!$store_content || empty($store_content)){
                $store_content ='未完待续...';
            }
            if($store_content){
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
          $allNovelList =[];
          if(!empty($store_c)){
              foreach($store_c as $gtkey=>$gtval){
                $allNovelList[$gtkey]['chapter_name'] = $chapetList[$gtkey]['chapter_name'] ?? '';//章节名称
                $allNovelList[$gtkey]['chapter_mobile_link'] = $chapetList[$gtkey]['chapter_mobile_link'] ??'';
                $allNovelList[$gtkey]['chapter_link'] = $chapetList[$gtkey]['chapter_link'] ?? ''; //章节链接
                $allNovelList[$gtkey]['save_path'] = $chapetList[$gtkey]['path']??''; //保存的文件路径
                $allNovelList[$gtkey]['content'] = $gtval; //获取内容信息
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
    //取出来成功和失败的数据
    $sucData = $returnList['sucData'] ?? []; //成功的数据
    $errData = $returnList['errData'] ?? []; //失败的数据
    $repeat_data = $curl_contents1 =[];
    if(!empty($errData)){
        $i = 0;
        $old_num = count($errData);

        $urls = array_column($errData, 'mobile_url'); //进来先取出来
        while(true){//连接总数和请求成功数量不一致轮训
            //重新请求对应的信息
            $curl_contents1 = curl_pic_multi::Curl_http($urls,$proxy_type);
            // $curl_contents1 = array_filter($curl_contents1);
            $temp_url =[];//设置中间变量，如果是空的，就需要把对应的URL加到临时变量里
            if($curl_contents1){
                foreach($curl_contents1 as $tkey=> $tval){
                  //防止有空数据跳不出去,如果非请求失败确实是空，给一个默认值
                  // if(!strstr($tval, '请求失败')  && empty($tval)){
                  //     //$tval ='此章节作者很懒，什么也没写';
                  // }

                  if($type =='ghttp'){//ghttp验证方式
                    //判断返回页面里不为空的情况，还要判断是否存在异常 如果不是200会返回guzzle自定义错误根据这个来判断
                      if(!empty($tval)
                        && (!strstr($tval,'您当前访问的页面存在安全风险') || !strstr($tval,'请求失败'))
                     ){
                            $repeat_data[] = $tval;
                            unset($urls[$tkey]); //已经存在就踢出去，下次就不用重复计算了
                            $i++;
                      }else{//为空保存urls去遍历循环
                        $temp_url[]=$urls[$tkey]; //说明请求里有空的html,把空的连接保存下来
                      }
                  }else if($type =='curl'){//采用curl来验证
                    // dd($tval);
                    // echo 333;die;
                    //|| strstr($tval,'503 Service') || strstr($tval, '403 Forbidde') || strstr($tval,'502 Bad Gateway') ||
                      //curl验证如果不是503的报错或者为空没有获取到200或者403就会返回一个空字符串来判断
                      //502 bad gateway
                      //如果没有匹配到id="content"说明页面缺了，需要重新补
                    // echo 1111;
                     if(empty($tval)){//为空的情况
                        $temp_url[] =$urls[$tkey];
                        //断章处理
                     }else if(!preg_match('/id="content"/',$tval) ){
                      echo "有断章======================{$urls[$tkey]}\r\n";
                          // echo '<pre>';
                          // print_R($tval);
                          // echo '</pre>';
                          // exit;
                          $temp_url[] =$urls[$tkey];
                      }else{
                          $repeat_data[] = $tval;
                          unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                          $i++;
                      }
                  }
              }
              // sleep(1);
              $urls = $temp_url; //起到指针的作用，每次只存失败的连接
              $urls = array_values($urls);//重置键值，方便查找
            }
            // $curl_contents1 =array_values($curl_contents1);//获取最新的数组
            //如果已经满足所有都取出来就跳出来
            if($old_num == $i){
                break;
            }
        }
    }
    //合并最终的需要处理的数据
    $finalData = array_merge($sucData , $repeat_data);
    return $finalData;
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
* @return array
*/
public static  function getErrSucData($content,$data,$type='ghttp'){
    if(!$content)
        return [];
    //该网站被大量用户举报，网站含有未经证实的信息，可能造成您的损失，建议谨慎访问
    //strstr($tval, '请求失败')
    $sucData = $errData= [];
    foreach($content as $key => $val){
         if($type == 'ghttp'){
              //如果存在这个就需要去判断
            //如果存在503或者抓取有大量被举报的返回值就需要去判断
            if(strstr($val,'您当前访问的页面存在安全风险') || strstr($val,'请求失败')){
                $errData[] =$data[$key] ?? [];//记录失败的
            }else{
                $sucData[] = $val;//记录成功的
            }
          }else if($type =='curl'){ //采用curl来进行验证
          // if(empty($tval) || strstr($tval,'503 Service') || strstr($tval, '403 Forbidde')){
              if(empty($val) || strstr($val,'503 Service') || strstr($val,'403 Forbidde')){//如果为空或者503错误就存储对应的记录信息或者是403的页面也需要重新抓取
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


}
?>