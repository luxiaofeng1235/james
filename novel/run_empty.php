<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :process_url.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:通过代理IP伦旭获取当前未同步的书籍章节到本地
// ///////////////////////////////////////////////////
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
$exec_start_time = microtime(true);
//校验代理IP是否过期
if(!NovelModel::checkProxyExpire()){
    exit("代理IP已过期，请重新拉取最新的ip\r\n");
}


$id = isset($argv[1])  ? intval($argv[1]) : 0;
if(!$id){
    exit("please input your id \r\n");
}

$sql = "select title,author,pro_book_id,store_id from ims_novel_info where store_id = ".$id;
$info =$mysql_obj->fetch($sql , 'db_slave');
if(!empty($info)){
    extract($info);
    $md5_str= NovelModel::getAuthorFoleder($title,$author);
    echo "book_md5：".$md5_str."\r\n";
    $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;
    $list = readFileData($json_path);
    if(!$list){
        echo "当前读取章节目录为空\r\n";
        exit;
    }
    //解析章节列表
    $chapter_list = json_decode($list,true);
    //当前的存储的小说的目录信息
    $txt_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $dataList= [];
    if(!$chapter_list){
        exit("暂无关联章节json \r\n");
    }

    $i = 0;
    //构造函数处理广告
    $removeAdInfo = function($arr){
        foreach($arr as &$val){
            $val['link_name'] = $val['chapter_name'];
        }
        //移除广告章节
        $list = NovelModel::removeAdInfo($arr);
        return $list;
    };
    //处理广告
    $chapter_list = $removeAdInfo($chapter_list);
    foreach($chapter_list as $val){
        //当前的章节路径的名称
        $filename =$txt_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
        $content = readFileData($filename);
        if(!$content ||$content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  || !file_exists($filename)){
            //替换调首字母首字母信息
            $link  =str_replace(Env::get('APICONFIG.PAOSHU_HOST'),'',$val['chapter_link']);
            $chapterInfo['file_path'] = $filename;
            $chapterInfo['link_url'] = $val['chapter_link'];
            $chapterInfo['link_str'] = $link;
            $chapterInfo['link_name'] = $val['chapter_name'];
            $dataList[] =   $chapterInfo;
        }else{
            $i++;
        }
    }
    if(!$dataList){
          echo "book_name：{$info['title']}  pro_book_id：{$info['pro_book_id']}  不需要轮询抓取了，章节已经全部抓取下来了\r\n";
          exit();
    }
    //统计下当前的跑出来的数据情况
    echo "default-num：".count($chapter_list)."\tis_have_num：".$i."\tall-empty-num：".count($dataList).PHP_EOL;
    $items = array_slice($dataList , 0, 10); //测试后期删掉
    echo "共需要处理的空章节总数--步长按照10来算的:".count($items).PHP_EOL;
    echo "-----------------------------\r\n";
    $limit_num = Env::get('LIMIT_EMPTY_SIZE');
    $items = array_chunk($items, $limit_num);
    $goodsList = [];
    foreach($items as $k =>$v){
        //抓取远端地址并进行处理
        $curl_contents= getHtmlData($v);
        //保存本地文件变更
        saveLocalFile($curl_contents);
        sleep(1);
    }
    echo "over\r\n";
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";


/**
* @note 保存本地文件信息
*
* @param $data str 待保存的数据
* @return 返回抓取后的curl请求连接
*/
function saveLocalFile($data){
    if(!$data)
        return false;
    foreach($data as $key =>$val){
         if(!$val)
            continue;
        if($val['content']){
            echo "file to loal path：{$val['file_path']} |name={$val['link_name']}\r\n";
            file_put_contents($val['file_path'] ,$val['content']);
        }
    }
}

/**
* @note curl抓取远程章节类目并组装数据
*
* @param $data str array处理的数据
* @return 返回抓取后的curl请求连接
*/

function getHtmlData($data){
    global $urlRules;
    if(!$data)
        return false;
    foreach($data as $key =>$val){
        $item[$val['link_str']] = $val;
        $link_name = $val['link_name'];
        $item[$val['link_str']]['link_name'] = $link_name;
        $urls[$val['link_str']]=  $val['link_url'];
        $t_url[]= $val['link_url'];
    }
    //获取对应的curl的信息
    $shell_cmd = NovelModel::getCommandInfo($item);
    //执行对应的shell命令获取对应的curl请求
    $string = shell_exec($shell_cmd);
    //防止返回空数据的情况，做特殊判断
    if(!$string){
        return [];
    }
    $s_content=preg_split('/<\/html>/', $string);
    $s_content =array_map('trim', $s_content);
    //处理过滤账号信息
    $list = array_filter($s_content);
    //获取采集的标识
    $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
    foreach($list as $key =>$val){
        $data = QueryList::html($val)->rules($rules)->query()->getData();
        $html = $data->all();
        $store_content = $html['content'] ?? '';
        $meta_data = $html['meta_data']??'';
        $href = $html['href'];
        $html_path = getHtmlUrl($meta_data,$href);
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
    foreach($item as $k =>$v){
        $item[$k]['content'] = isset($store_c[$k])  ? $store_c[$k] : '';
    }
    $arr_list= array_values($item);
    return $arr_list;
}