<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :dataList.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:guzzlehttp封装的业务类
// ///////////////////////////////////////////////////
use QL\QueryList;
class ClientModel{
    //验证类型 支持curl和ghttp验证
    private static $validate_type =['curl','ghttp'];

    private static $first_proxy_name = 'story'; //第一次代理的名称

    private static $second_proxy_name = 'empty'; //第二次代理的名称

    //代理配置的四条业务IP
    private static $proxy_list =  [
            'story',
            'count',
            'empty',
            'image',
        ];

    //配置curl获取的代理IP
    private static $proxy_curl_list =[2,3,4,5]; //获取获取代理的配置IP
    /*
    * @note 获取远端的内容
    * @param string $str 获取小说的章节数据处理-章节列表
    * @param  integer $store_id 小说ID
    * @param txt_path string 存储路径
    * @return array
    */
    public static function getClientContents($item=[],$store_id=0,$txt_path=''){
        if(!$item){
            return [];
        }
        $data_arr  = NovelModel::exchange_urls($item,$store_id,'count');
        $chapetList = [];
        foreach($data_arr as $m_key=> $gr){
             $mobileArr =parse_url($gr['mobile_url']);
             $new_data[$mobileArr['path']] = $gr;
             $mobilePath = substr($mobileArr['path'],0,-2);
             //存对饮的URL信息
             $chapetList[$mobilePath] = [
                    //拼装移动端的地址
                    'path'  =>  $txt_path.DS.md5($gr['link_name']).'.'.NovelModel::$file_type,
                    'chapter_name'  =>  $gr['chapter_name'],
                    'chapter_link'  =>  $gr['chapter_link'],
                    'chapter_mobile_link'   =>  substr($gr['mobile_url'] , 0 , -2),
             ];
        }
        $valid_ghttp ='ghttp';//ghttp验证
        $urls = array_column($new_data,'mobile_url');
        $list = guzzleHttp::multi_req($urls,self::$first_proxy_name);
        echo '<pre>';
        print_R($list);
        echo '</pre>';
        exit;
        if(!$list || empty($list)){//说明代理已经到期
            echo "代理已经到期了，请等待下一轮\r\n";
            NovelModel::killMasterProcess();//退出主程序
            exit(1);
        }

        $rand_str = self::getRandProxy();//随机获取代理
        $list  = self::callRequests1($list , $new_data,$valid_ghttp,$rand_str);
        echo '<pre>';
        print_R($list);
        echo '</pre>';
        exit;
        global $urlRules;//获取指定的抓取规则
        $rules =$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
        $allNovel = [];
        if($list){
            global $urlRules;
            foreach($list as $content){
                if(!$content) continue;
                $data = QueryList::html($content)->rules($rules)->query()->getData();
                $html = $data->all();
                $store_content = $html['content'] ?? '';
                $meta_data = $html['meta_data']??''; //meta表亲爱
                $first_line = $html['first_line'] ?? '';//第一行的内容
                //获取当前章节的页码数
                $html_path = NovelModel::getChapterPages($meta_data,$first_line);
                if(!$html_path) $html_path = array();
                $allNovel = array_merge($allNovel,$html_path);
            }
        }
        //重组获取新的列表请求链接
        $new_list = self::createMobileList($new_data ,$allNovel);
        sleep(1);

        //最终需要请求的列表
        $finalList = guzzleHttp::multi_req(array_column($new_list,'mobile_url'),self::$second_proxy_name);
        $rand_str_new = self::getRandProxy(); //再次生成随机的IP
        $finalList = self::callRequests1($finalList , $new_list,$valid_ghttp,$rand_str_new);
        //获取已经从客户端拿回来的内容
        $html_contents = self::getMobileContents($rules ,$finalList);

        //最终返回的数据信息
        $store_data= $returnArr =[];
        foreach($html_contents as $ggk =>$ggv){
              $index  = substr($ggk, 0, -2);
              $store_data[$index][]=$ggv;
        }
        //处理校验的抓取连接和请求
        if(!empty($store_data)){
            foreach($store_data as $gtkey=>$gtval){
                if(!$store_data) continue;
                $string = implode('',$gtval);//切割字符串,不能用,因为文章里含有，会错乱原因在这里
                $returnArr[$gtkey]['chapter_name'] = $chapetList[$gtkey]['chapter_name'] ?? '';//章节名称
                $returnArr[$gtkey]['chapter_link'] = $chapetList[$gtkey]['chapter_link'] ?? ''; //章节链接
                $returnArr[$gtkey]['chapter_mobile_link'] = $chapetList[$gtkey]['chapter_mobile_link'] ?? '';//移动端的连接地址
                $returnArr[$gtkey]['save_path'] = $chapetList[$gtkey]['path']??''; //保存的文件路径
                $returnArr[$gtkey]['content'] = $string; //获取内容信息
            }
        }
        return $returnArr ?? [];

    }

    /**
    * @note 获取移动端的内容
    *
    * @param $finalList array 抓取的列表数据
    * @return array
    */
    public static function getMobileContents($rules=[],$finalList=[]){
        if(!$finalList) return [];
        $html_contents = [];
        if($finalList){
            foreach($finalList as $ck =>$cv){
                $data1 = QueryList::html($cv)->rules($rules)->query()->getData();
                $html = $data1->all();
                $store_content = $html['content'] ?? '';
                $meta_data = $html['meta_data']??''; //meta表亲爱
                $first_line = $html['first_line'] ?? '';//获取第一行的数据信息
                //获取每页的页码位置信息

                // echo $page_link_url."\r\n";
                //处理为空的情况
                //处理剔除第一行的标题显示和替换掉“本章未完，请点击下一页继续阅读”这种字样
                $store_content= NovelModel::removeLineData($store_content);
                //替换内容里的广告
                $store_content = NovelModel::replaceContent($store_content);

                //如果确实没有返回数据信息，先给一个默认值
                if(!$store_content || empty($store_content)){
                    $store_content ='未完待续...';
                }

                $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
                //替换文本中的P标签
                $store_content = str_replace("<p>",'',$store_content);
                $store_content = str_replace("</p>","\n\n",$store_content);
                //替换try{.....}cache的一段话JS这个不需要了
                $store_content = preg_replace('/{([\s\S]*?)}/','',$store_content);
                $store_content = preg_replace('/try\scatch\(ex\)/','',$store_content);
                //组装html内容 ,必须用页面中的返回的进行组装，否则会出现页面和文章错乱
                $currentPage = NovelModel::getCurrentPage($first_line);
                //存储每一页的内容
                $page_link_url = substr($meta_data,0, -1).'-'.$currentPage;
                //只有不为空才进行保存
                if(!empty($currentPage)){
                    $html_contents[$page_link_url] = $store_content;
                }
                $html_contents[$page_link_url] = $store_content;
                // if(isset($new_list[$ck])){
                //  $ttk = parse_url($new_list[$ck]['mobile_url']);
                //  $html_contents[$ttk['path']] = $store_content;
                // }
            }
        }
        return $html_contents;
    }


    /**
    * @note 生成移动端的数据列表
    *
    * @param  $new_data array 章节列表数据
    * @param $allowNovel 匹配的章节信息
    * @return
    */
    public static function createMobileList($new_data,$allNovel){
        //
        $new_list = [];
        foreach($new_data as $gk =>$gv){
             $t =explode('/',$gv['mobile_url']);
             $anurl =explode('-',$t[3]??[]);
             array_pop($anurl);
             //拼接新的url
             $curr_url = Env::get('APICONFIG.PAOSHU_MOBILE_HOSt'). '/'.implode('-',$anurl);
             $num = isset($allNovel[$gk]) ? intval($allNovel[$gk]) : 1;
             for ($i=0; $i <$num ; $i++) {
                 $new_list[]=[
                    'mobile_url'    =>  $curr_url.'-'.($i+1),
                 ];
             }
        }
        return $new_list;
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
public static function callRequests1($contents_arr=[],$goods_list=[],$type='',$proxy_type=''){
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
            //重新请求对应的信息 ,利用guzzlehttp来请求
            $list = guzzleHttp::multi_req($urls,$proxy_type);
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
                        && (!strstr($tval,'请求失败'))
                     ){
                            $repeat_data[] = $tval;
                            unset($urls[$tkey]); //已经存在就踢出去，下次就不用重复计算了
                            $i++;
                      }else{//为空保存urls去遍历循环
                        $temp_url[]=$urls[$tkey]; //说明请求里有空的html,把空的连接保存下来
                      }
                  }else if($type =='curl'){//采用curl来验证
                      //curl验证如果不是503的报错或者为空没有获取到200或者403就会返回一个空字符串来判断
                      if(empty($tval) || strstr($tval,'503 Service') || strstr($tval, '403 Forbidde') || strstr($val,'502 Bad Gateway') ){
                          $temp_url[] =$urls[$tkey];
                      }else{
                          $repeat_data[] = $tval;
                          unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                          $i++;
                      }
                  }
              }
              $urls = $temp_url; //起到指针的作用，每次只存失败的连接
              $urls = array_values($urls);//重置键值，方便查找
            }
            // $curl_contents1 =array_values($curl_contents1);//获取最新的数组
            //如果已经满足所有都取出来就跳出来
            if($old_num == $i){
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
    * @note 获取随机取出来一个代理配置信息
    *
    * @return string
    */

    public static function getRandProxy(){
        $proxy_arr =self::$proxy_list;
        $rand_str =$proxy_arr[mt_rand(0,count($proxy_arr)-1)];
        return $rand_str;
    }

    /**
    * @note 获取代理的配置IP-CURL专用
    *
    * @return string
    */
    public static function getCurlRandProxy(){
       $proxy_arr  = self::$proxy_curl_list;
        $rand_str =$proxy_arr[mt_rand(0,count($proxy_arr)-1)];
        return $rand_str;
    }
}
?>