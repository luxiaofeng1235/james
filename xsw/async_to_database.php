<?php
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :deal_page.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:自动把JSON文件里的数据追加到数据库中去
// ///////////////////////////////////////////////////

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
use QL\QueryList;
use Yurun\Util\HttpRequest;

$limit = Env::get('TWCONFIG.RUN_LIST_PAGE');
if(!$limit){
    exit("请输入完本的起止页码数");
}

//分类的取值范围1-7这样子去取 方便进行计算。
$cateId = isset($argv[1]) ? $argv[1] : '';
if(!$cateId){
  exit("请输入要处理的分类ID\r\n")
  ;
}

$size  = explode(',',$limit);
if($cateId == 6){
  $size[0] = 401;
}else if($cateId == 7){
  $size[0] = 292;
}
$pages = range($size[0] , $size[1]);
//cateId =2  同步中
//cateId = 3 同步中
//cateId = 4 同步中
//cateId = 534
//cateId = 6343
//cateId = 7 33


//批量写入数据信息
foreach($pages as $page){
  echo "current page is page = {$page} \r\n";
  echo "*******************************************************\r\n";
  echo "page = {$page} cateId = {$cateId} \r\n";
  $t = saveNovelData($cateId , $page); //同步数据
}


/**
* @note 保存小说数据到库里
*
* @param  $cateId int 分类id
* @param $page int 分页
* @return array
*/

function saveNovelData($cateId , $page){
    if(!$cateId || !$page){
      return "参数无效\r\n";
    }
    global $mysql_obj;
    $exists = [];

    $novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
    $db_conn = 'db_master';
    $json_file = Env::get('SAVE_CACHE_INFO_PATH') .DS .StoreModel::$detail_page . $cateId . '_'. $page.'.json';
    echo "json_file = $json_file\r\n";
    if(!file_exists($json_file)){
      return '当前文件不存在，请稍后重试'.PHP_EOL;
    }
    $json_data = readFileData($json_file);
    if(!$json_data) {
        echo "获取小说分页信息失败\r\n";
        return false;
    }
    //测试哈哈
    $storyList= json_decode($json_data,true);
    if(!$storyList){
      echo "暂无小说数据，请稍后重试\r\n";
      return false;
    }
    //更新数据
    //检测当前文件是否存在此本书
    $insertData =[];
    $num = $i_num = 0;
    foreach($storyList as $value){
        if(!$value) continue;
        $num++;
        //清洗对应的数据信息，去空，转义等等，方便进行计算
       $info = StoreModel::combineNovelHandle($value);
       //查询是否存在已经同步的小说信息
       $results  = NovelModel::getNovelByName($info['title'] , $info['author']);
       if($results){
            $exists[] = $results['title'] .'--'.$results['story_link'];
           $i_num++;
           echo "num = {$num} \t title={$results['title']}\t author = {$results['author']} \t url = {$results['story_link']}  \texists store_id = {$results['store_id']}  no to run\r\n";
       }else{
          $insertData[]=$info;
          echo "num = {$num} \t title={$value['title']}\t author = {$value['author']}\turl = {$value['story_link']} \tis to insert this data\r\n";
       }
    }
  echo "\r\n===========共计小说".count($storyList)."本 ，待插入同步的有 (".count($insertData) . ")本，已存在的有 ( {$i_num} )本 -- ".var_export($exists,true)."\r\n";
  $repeat_rate = sprintf('%.2f',$i_num / 100 * 100) .'%';
  echo "每30本的小说的重复率为：" . $repeat_rate .PHP_EOL;
  echo "\r\n";
  echo "*******************************************************\r\n";
  echo "\r\n";
  if($insertData){
      //同步数据
      $ret= $mysql_obj->add_data($insertData,$novel_table_name,$db_conn);
      if(!$ret){
          echo "数据库数据同步失败\r\n";
      }
      echo "同步小说列表成功 \r\n";
  }else{
      echo "暂无小说需要插入的数据同步\r\n";
  }

}
echo "finish\r\n";
?>