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
require_once dirname(__DIR__).'/library/file_factory.php';
use QL\QueryList;
$exec_start_time = microtime(true);

echo "\r\n";
echo "*********************************************************************\r\n";

///一次申请三个一起判断，火力全开来进行判断，需要用三个IP来一起抓取提高效率
$proxy_detail = NovelModel::checkProxyExpire();//获取列表的PROXY
$proxy_count =  NovelModel::checkMobileKey();//获取统计的PROXY
$proxy_empty =  NovelModel::checkMobileEmptyKey();//获取修复空数据的PROXY
$proxy_img = NovelModel::checkImgKey();

//exit("代理IP已过期，key =".Env::get('ZHIMA_REDIS_MOBILE_KEY')." 请重新拉取最新的ip\r\n");
//校验代理IP是否过期
if(!$proxy_detail || !$proxy_count || !$proxy_empty || !$proxy_img){
    NovelModel::killMasterProcess();//退出主程序
   exit("入口--代理IP已过期，key =".Env::get('ZHIMA_REDIS_KEY').",".Env::get('ZHIMA_REDIS_MOBILE_KEY').",".Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA').",".Env::get('ZHIMA_REDIS_IMG')." 请重新拉取最新的ip\r\n");
}

//同步的书籍ID信息
$store_id = isset($argv[1])  ? intval($argv[1]) : 0;
if(!$store_id){
    exit("please input your store_id.... \r\n");
}
$table_novel_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
$where_data = 'is_async = 1'; //只对已经同步得来进行计算
//查询小说的基本信息
$sql = "select title,cover_logo,author,pro_book_id,store_id,empty_status from ".$table_novel_name." where {$where_data} and  store_id = ".$store_id;
echo "sql={$sql} \r\n";
$info =$mysql_obj->fetch($sql , 'db_slave');
if(!empty($info)){
    extract($info);
    //判断是否有关联的书本ID
    if(!$pro_book_id){
        NovelModel::killMasterProcess();//退出主程序
        echo  '并无关联的小说ID'.PHP_EOL;
        exit;
    }

    $run_status = intval($info['empty_status']);
    if($run_status){
        NovelModel::killMasterProcess();//退出主程序
        echo  'store_id = '.$store_id .' - empty_status = 1 已经跑完处理完毕，无须重复更新'.PHP_EOL;
        exit;
    }

    //同步一下图片，直接取
    $md5_str= NovelModel::getAuthorFoleder($title,$author);
    echo "book_md5：".$md5_str."\r\n";
    $json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;
    $list = readFileData($json_path);
    if(!$list){
        NovelModel::killMasterProcess();//退出主程序
        echo "当前读取章节目录为空\r\n";
        exit;
    }
    //解析章节列表
    $chapter_list = json_decode($list,true);
    //当前的存储的小说的目录信息
    $txt_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $dataList= [];
    if(!$chapter_list){
        NovelModel::killMasterProcess();//退出主程序
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



    //处理广告并移除关联章节
    $chapter_list = $removeAdInfo($chapter_list);
    if(!$removeAdInfo){
        echo "去除广告后未 发现有需要同步的章节\r\n";
        NovelModel::killMasterProcess();//退出主程序(
        exit(1);
    }

    $i = 0;
    foreach($chapter_list as $val){
        //当前的章节路径的名称
        $filename =$txt_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
        $content = readFileData($filename);
        if(!$content ||$content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  || !file_exists($filename)){
            //替换调首字母首字母信息
            $val['link_url'] = $val['chapter_link'];
            $dataList[] =   $val;
        }else{
            $i++;
        }
    }
    //这里没有说明已经全部抓取下来了
    if(!$dataList){
        echo "book_name：{$info['title']}  pro_book_id：{$info['pro_book_id']}  不需要轮询抓取了，章节已经全部抓取下来了\r\n";
        updateEmptyStatus($store_id); //更新状态
        NovelModel::killMasterProcess();//退出主程序
        exit(1);
    }

    echo "\r\n\r\n";
    echo "共需要补的章节总数量： num = ".count($dataList)."\r\n";

    $factory = new FileFactory($mysql_obj , $redis_data);

    //转换数据字典用业务里的字段，不和字典里的冲突
    $dataList = NovelModel::changeChapterInfo($dataList);

    $items = array_chunk($dataList,100); //默认每一页100个请求，到详情页最多150*3=900个URL 这个是因为移动端的原因造成
    $i_num =0;
    foreach($items as $k =>&$v){
        //获取内容信息
        $html_data = ClientModel::getClientContents($v,$store_id,$md5_str);
        if($html_data){
            $a_num = 0;
            foreach($html_data as $gvalue){
                  $a_num++;
                if(!empty($gvalue['content'])){
                    //方便调试,遇到有的章节空的path或者name为空，需要排查下
                    if(empty($gvalue['save_path']) || empty($gvalue['chapter_name'])){
                        echo '<pre>';
                        var_dump($gvalue);
                        echo '</pre>';
                        echo "*************************************\n";
                        echo "\r\n";
                    }
                    echo "num：{$a_num} \t  chapter_name: {$gvalue['chapter_name']}\t url：{$gvalue['chapter_mobile_link']}\t path：{$gvalue['save_path']} \r\n";
                    $i_num++;
                }else{
                     echo "num：{$a_num} \t chapter_name: {$gvalue['chapter_name']} \t 小说源内容为空 url：{$gvalue['chapter_mobile_link']}\r\n";
                }
            }
            //保存本地存储数据
            $factory->synLocalFile($txt_path,$html_data);
            sleep(1);//休息三秒不要立马去请求，防止空数据的发生
        }else{
            echo "num：{$a_num} 未获取到数据，有可能是代理过期\r\n";
        }
     }
    //强制清除内存垃圾
    gc_collect_cycles();
    unset($items);
    unset($chapter_list);
    echo "novel_path: {$txt_path} store_id = ".$store_id." | store_id= ".$store_id." pro_book_id = ".$info['pro_book_id'].PHP_EOL;
    echo "\r\n\r\n";
    echo "章节处理完毕\r\n";
    NovelModel::killMasterProcess();//退出主程序
}else{
    echo "no data\r\n";
    NovelModel::killMasterProcess();//退出主程序
}
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "*********************************************************************\r\n";
echo "\r\n";
echo "\r\n";
/**
* @note 更新为空的状态同步统计
*
* @param  $store_id int 小说ID
* @return array
*/
function updateEmptyStatus($store_id){
    global $mysql_obj , $table_novel_name;
    $store_id = intval($store_id);
    if(!$store_id)
        return false;
    $where_condition = "store_id = '". $store_id."'";
    $chapter_data['empty_status'] = 1;
    //对比新旧数据返回最新的更新
    $mysql_obj->update_data($chapter_data,$where_condition,$table_novel_name);
}


//获取成功抓取的总次数
function getSuccessTimes($data){
    if(!$data)
        return false;
    $num = 0;
    foreach($data as $value){
        if(!empty($value['content'])){
            $num++;
        }
    }
    return $num;
}

