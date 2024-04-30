<?php
ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';

$limit =200;
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');
$where_condition= "1
and is_async = 1
and source ='paoshu8'
and (author!='' or author!='未知')
and  (title!='' or title is not null)
and store_id<174775
and is_resource = 0";
$order_by  = 'store_id desc';
$count = $mysql_obj->get_data_by_condition($where_condition , $novel_table_name,'count(store_id) as num');
$ts_count  = $count[0]['num'] ??0;
if(!$ts_count){
    exit("no data to run \r\n");
}
$pages = ceil($ts_count/$limit);
echo "all-nums：{$ts_count} ,all-pages：{$pages}\r\n";

for ($i=0; $i <$pages ; $i++) {
    $page = $i+1;
    echo "current page = {$page} \r\n";
    $sql = "select store_id,story_link,title,author from {$novel_table_name} where {$where_condition}";
    $sql .=' order by '.$order_by;
    // $sql .=" limit 1";
    $sql .=" limit ".($i*$limit).','.$limit;

    $list = $mysql_obj->fetchAll($sql , 'db_slave');
    if(!empty($list)){
        $dataList = [];
        foreach($list as $value){
            if(!$value || !$value['story_link']) continue;
            $title  = trimBlankSpace($value['title']); //小说作者
            $author = trimBlankSpace($value['author']); //小说标题
            $md5_str = NovelModel::getAuthorFoleder($title , $author);
            if(!$md5_str) continue;
            $josn_file_path =Env::get('SAVE_JSON_PATH') .DS .$md5_str.'.' .NovelModel::$json_file_type;//json路径信息
            $json_data = readFileData($josn_file_path);
            $chapter_item = json_decode($json_data,true);
            if(!$chapter_item){
                echo "this data is no json dat \r\n";
            }else{
                $itemList = parse_url($value['story_link']);
                $value['title'] = $title; //去除空格后的标题
                $value['author'] = $author;//去除空格后的作者
                $value['json_count'] = count($chapter_item);
                $value['path'] = $josn_file_path;
                $dataList[$value['story_link']] = $value;
            }
        }
        //通过URL去同步相关的列表
        $t= asyncUrlList($dataList,$pages,$page);
        if($t){
              $ids= array_column($t, 'store_id');
            //待更新的数据信息
            $up_sql = "update {$novel_table_name} set is_resource = 1 where store_id in (".implode(',', $ids).")";
            echo "sql = {$up_sql} \r\n";
            $res   =        $mysql_obj->query($up_sql,'db_master');
            echo '<pre>';
            var_dump($res);
            echo '</pre>';
        }else{
            echo "no ids to update \r\n";
        }
    }
}
echo  "finish\r\n";


/**
* @note 获取url的列表信息
*
* @param  $item array 列表信息
* @param $count_page int 总分页数
* @param $page int 页码
* @return  array
*/
function asyncUrlList($item=[],$count_page =0,$page=0){
    if(!$item){
        return $item;
    }
    $newList = $returnList = [];
    foreach($item as $key =>$val){
        $link_arr = parse_url($key);
        $newList[$link_arr['path']] = $val;
    }
    $urls = array_column($newList, 'story_link');//请求的URL
    $list= StoreModel::swooleRquest($urls);//请求数据
    $list = StoreModel::swooleCallRequest($list, $newList ,'story_link',2);
    $x = $y = 0;
    foreach($list as $key =>$val){

        $baseArr = $newList[$key] ??[];
        if(!$baseArr) continue;
        $title = $newList[$key]['title'] ?? '';
        $json_count  = $newList[$key]['json_count'] ?? 0; //json文件里的总数
        if(isset($newList[$key])){
            //获取匹配的章节信息
            $html  = html_entity_decode($val);
            $chapterList = NovelModel::getCharaList($html , $title);
            //统计当前的总数
            $curl_num = count($chapterList);
        }else{
            $curl_num  = 0;
        }
        if($curl_num>0 && $curl_num != $json_count){
            $x++;
            echo "store_id ={$baseArr['store_id']} \turl = {$baseArr['story_link']}  \t path = {$baseArr['path']} \t 本地json文件解析的总数：{$baseArr['json_count']} \t 远程章节总数：{$curl_num} \r\n";
            $baseArr['url_count'] = $curl_num;
            $returnList[]=$baseArr;
        }else{
            //local_num = {$json_count} curl_num={$curl_num}
            $y++;
            echo "store_id ={$baseArr['store_id']} \turl = {$baseArr['story_link']}  \t path = {$baseArr['path']} \t 章节数量一致，不需要重复采集  \r\n";
        }
    }
    echo "\r\n|||||||||||||||| this current page =  (".($page)."/{$count_page})\t  \tcomplate \r\n\r\n";
    echo "*********************************************\r\n";
    echo "需要重新拉去目录的小说有：{$x} 本，正常不需要更新的有：{$y}本 \r\n";
    return $returnList;
}

/**
* @note  更新需要跑的数据状态
* @param string $story_id 小说ID
* @return string
*/
 function updateResourceStatus($store_id){
    if(!$store_id){
        return false;
    }
    global $mysql_obj,$novel_table_name;
    $where_condition = "store_id = '".$store_id."'";
    $updateData['is_resource'] = 1;
    //对比新旧数据返回最新的更新
    $mysql_obj->update_data($updateData,$where_condition,$novel_table_name);
}
?>