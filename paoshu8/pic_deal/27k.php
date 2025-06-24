<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :批量移动文件夹处理.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(dirname(__DIR__)).'/library/init.inc.php';
use QL\QueryList;

$db_conn_novel = 'db_novel_pro';
$table_novel_table = 'mc_book';
$db_conn_novel = 'db_novel_pro';
$table_novel_table = "mc_book";
$exec_start_time = microtime(true);
$exec_start_time = microtime(true);
$where = ' instr(`source_url`,237)>0 and addtime>0';
$where =" instr(`source_url`,'27k')>0 and addtime>0";
$sql = "select count(1) as  num from {$table_novel_table} where {$where}";
$limit =100;
$ts_count = $mysql_obj->fetch($sql,$db_conn_novel);
$count = $ts_count['num'] ?? 0;
$pages = ceil($count/$limit);
for($i = 0;$i<$pages;$i++){
    $start = $i* $limit;
    echo "********************************current  page = ".($pages-1)."/{$i}\r\n";
    $sql = "select id,source_url,book_name,author,pic,addtime from mc_book where {$where} order by id asc limit {$start} ,{$limit}";
    $list = $mysql_obj->fetchAll($sql , $db_conn_novel);
    sync_pic_info($list);
}


//同步数据处理
function sync_pic_info($list){
    $urls =[];
    $lists= [];
    if(!$list){
        return false;
    }
    global $urlRules,$table_novel_table,$db_conn_novel,$mysql_obj;
    foreach($list as $key =>$val){
        $pic = $val['pic'] ?? '';
        if(!file_exists($pic) || !@getimagesize($pic)){
            // echo "图片缺失===== \"
            $source_url  = $val['source_url'];
            $parseData = parse_url($source_url);
            $val['date'] = date('Ym',$val['addtime']);
            $lists[$parseData['path']]=$val;;
        }
    }
    if(!$lists){
        echo  "暂无需要处理的数据\r\n";
        return false;
    }
    echo "需要处理的图片数据有 【".count($lists)."】条 去进行拉取\r\n";
    $t_url = array_column($lists, 'source_url');
    $result = StoreModel::swooleRquest($t_url);

    $imgList = [];
    $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['27k'];
    if($result){
        foreach($result as $key =>$val){
            if(isset($lists[$key])){
                $info_data = QueryList::html($val)
                        ->rules($rules)
                        ->query()
                        ->getData();
                $info_data = $info_data->all();
                $cover_logo = '';

                $pic_status = check_url($info_data['cover_logo']);
                if($pic_status== false){
                     $cover_logo = Env::get('APICONFIG.DEFAULT_PIC');
                }else{
                     if(isset($info_data['cover_logo']) && $info_data['cover_logo']){
                        $cover_logo = $info_data['cover_logo'] ?? '';
                    }

                }
                if($cover_logo){
                    // $new_conver_log = $cover_logo.'?id='.$info['id'];
                    $imgRet = parse_url($cover_logo);
                    $info  = $lists[$key] ?? [];
                    $info['cover_logo']  = $cover_logo;
                    $info['pic_path'] = $imgRet['path'];
                    $imgList[$info['pic_path']] = $info;
                    $ids[$info['pic_path']][]=$info['id'];
                    $lists[$key]['cover_logo'] = $cover_logo;
                }

            }
        }
    }
    echo "兼容后的图片信息有：".count($imgList)."个，可能出现有默认图重复合并啦\r\n";

    if(count($imgList)>0){
        $index = 0;
        echo "total = ".count($lists)."\r\n";
        $pic_url = array_column($imgList, 'cover_logo');
        $picResults = StoreModel::swooleRquest($pic_url);
        foreach ($picResults as $key =>$img_con){
            $index++;
            if(isset($imgList[$key])){
                $return = $imgList[$key] ?? [];
                if(!$img_con){
                    $img_con = webRequest('https://img.ipaoshuba.net/47577/139535.jpg','GET');
                }
                $up_ids = $imgList[$key];
                $save_path = '/data/pic/'.$return['date'] . DS . basename($return['pic']);
                $filename = $return['pic']  ?? '';
                $sql  ="update {$table_novel_table} set pic ='{$save_path}' where id in (".implode(',',$up_ids).")";
                echo "update_sql : ".$sql.PHP_EOL;
                $mysql_obj->query($sql,$db_conn_novel);
                //写入文件
                $t = writeFileCombine($save_path,$img_con);
                echo "num = {$index} \t d = {$return['id']} \title={$return['title']}\r\n";
            }
        }
        foreach($picResults as $key =>$img_con){
            $index++;
            if(isset($imgList[$key])){
                 $return = $imgList[$key] ?? [];
                 if(!$img_con){
                    $img_con =  webRequest('https://img.ipaoshuba.net/47577/139535.jpg',"GET");
                 }

                 $up_ids = isset($ids[$key]) ? $ids[$key] : 0;
                 //拼接新的路径去完善
                 $save_path = '/data/pic/'.$return['date'].'/'.basename($return['pic']); //更新后的字段信息
                 $filename = $return['pic']??'';
                 $sql  ="update {$table_novel_table} set pic ='{$save_path}' where id in (".implode(',',$up_ids).")";
                 echo "update_sql = {$sql} \r\n";
                 $mysql_obj->query($sql,$db_conn_novel);
                 //写入文件
                 @$t = writeFileCombine($save_path, $img_con);
                echo "num = {$index}\t id ={$return['id']}\ttitle ={$return['book_name']}\tauthor={$return['author']}\tpic = {$save_path}\tsuccess\r\n";
            }else{
                echo "num = {$index}\t no match data\tpic = {$save_path}\tsuccess\r\n";
            }
        }
    }
}
echo "over\r\n";
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";


?>