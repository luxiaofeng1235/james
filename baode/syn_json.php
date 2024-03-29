<?php
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use QL\QueryList;

$json_file = './baode_url.json';//读取当前的文件的URL
$item = readFileData($json_file);
if($item){
    $list= json_decode($item ,true);
    $url = getBaodeUrlRand($list);
    if(!$url){
        exit("no url to deal\r\n");
    }
    $urlData = parse_url($url);
    $path = substr($urlData['path'], 0 , -1);
    $path = str_replace('/ob/' , '' , $path);
    $url_link = Env::get('BAODE.STORE_MOBILE_HOST') . $path. '_1/';
    $rules = [
        'pages' =>['.pagelink span','text']
    ];

    $story_id = '101781';

    $url_link = 'https://m.baode1.com/ob/'.$story_id.'_1/';
    $response_url = array($url_link);
    $html  =guzzleHttp::multi_req($response_url,'count');

    $content  = $html[0] ?? '';

    //抓取分页信息
    $data = QueryList::html($content)
                    ->rules($rules)->query()
                    ->getData();
    $s_html = $data->all();
    $page_data = explode('/',$s_html['pages']);
    if(!$page_data){
        echo "分页获取错误";
        die;
    }
    $all_pages = range($page_data[0] , $page_data[1]);
    $pages_url = [];
    foreach($all_pages as $page){
        $pages_url[]= Env::get('BAODE.STORE_MOBILE_HOST'). $story_id.'_'.$page.'/';
    }

    $num =30;
    $all_list = array_chunk($pages_url  , $num);
    $dataList= [];
    foreach($all_list as $val){
        $dt_ret = guzzleHttp::multi_req($val,'empty');
        //获取章节列表
        $chapter_list = getChapterList($dt_ret);
        //合并所有数据
        $dataList= array_merge($dataList , $chapter_list);
    }
    // sleep(1);


    $dataList = array_slice($dataList, 0 , 5);


    //处理获取相关的内容进行存储，需要对相关的数据进行分割存储
    $chapter_info  = array_chunk($dataList , 1);
    $returnList = [];
    foreach($chapter_info as $num => $multi_url){
        $urls = array_column($multi_url,'chapter_link');
        $contents = guzzleHttp::multi_req($urls,'story');
        $td = getContents($contents);
         foreach($multi_url as $gk =>$gv){
            if(isset($td[$gk])){
               $returnList[] = [
                    'name'  =>  $multi_url[$gk]['chapter_name'],
                    'content'   =>  $td[$gk],
               ];
            }
         }
    }
    echo '<pre>';
    print_R($returnList);
    echo '</pre>';
    exit;
}

//获取配置的相关信息
function getContents($goods_list= []){
    if(!$goods_list)
        return false;
    $rules = [
        'content'   => ['#nr','html']
    ];
    $items  =[];
    foreach($goods_list as $val){
         $contents = QueryList::html($val)
                    ->rules($rules)->query()
                    ->getData();
        $html = $contents->all();
        $store_content = $html['content'] ?? '';
        $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
        //替换文本中的P标签
        $store_content = str_replace("<br>","\n",$store_content);
        $items[] = $store_content;
    }
    return $items;
}

function getChapterList($data=[]){
    if(!$data)
        return false;
    $preg = '/<ul class="lb fk">(.*?)<\/ul>/is';
    $link_arr= [];
    foreach($data as $key =>$val){
        //加一个特殊判断，防止链接为空
        if(!strstr($val, 'href')){
            continue;
        }
        preg_match($preg,$val ,$matches);
        if(isset($matches[1])){
             $list = $matches[1];
             $chapter_list= preg_split("/\r\n/", $list);
             $chapter_list = array_map('trim',$chapter_list);
             $chapter_list = array_filter($chapter_list);
             array_pop($chapter_list);
             $item = getList($chapter_list);
            $link_arr = array_merge($link_arr , $item);
        }
    }
    return $link_arr;
}

//处理获取的连接地址
function getList($data=[]){
    if(!$data)
        return array();
    $link_reg = '/<a.+?href=\"(.+?)\".*>/i'; //匹配A连接
    $text_reg ='/<a href=\"[^\"]*\"[^>]*>(.*?)<\/a>/i';//匹配链接里的文本
    $list = [];
    foreach($data as $contents){
        preg_match($link_reg,$contents,$link_href);//匹配链接
        preg_match($text_reg,$contents,$link_text);//匹配文本
        $url ='';
        //处理前缀问题
        if(isset($link_href[1])){
            $url = 'https:' . $link_href[1];
        }
        $list []=[
            'chapter_name'  =>  $link_text[1]??'', //章节名称
            'chapter_link'  =>$url,//连接地址
        ];
    }
    return $list;
}


/**
* @note 随机一个URL去处理
*
* @param $list str array
* @return  string
*/

function getBaodeUrlRand($list){
    if(!$list)
        return false;
    $ran_count = count($list);
    //每次随机一个url
    $random_url =$list[mt_rand(0,$ran_count-1)];
    return $random_url;
}
?>