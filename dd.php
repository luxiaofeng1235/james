<?php
//测试文档代码机构的集合。
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
use Overtrue\Pinyin\Pinyin;
use QL\QueryList;
$exec_start_time = microtime(true);
$list = $mysql_obj->fetchAll('select chapter_id,CONCAT(\''.Env::get('APICONFIG.PAOSHU_HOST').'\',link_url) as link_url from ims_chapter where story_id="92_92763" order by rand()   limit 100','db_slave');
$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
$urls = array_column($list,'link_url');
if(!NovelModel::checkProxyExpire()){
    exit("代理已过期，请重新拉取最新的ip\r\n");
}


$t  = NovelModel::saveImgByCurl('http://www.paoshu8.info/files/article/image/70/70047/70047s.jpg','我的弟子全是大帝之资','别让我通宵');
echo '<pre>';
print_R($t);
echo '</pre>';
exit;

$shell_cmd = NovelModel::getCommandInfo($list);
echo '<pre>';
print_R($shell_cmd);
echo '</pre>';
exit;
if(!$shell_cmd){
    echo "缓存可能失效了";
    die;
}
//执行shell命令函数

// $s_content=preg_split('/<\/html>/', $content);
// echo '<pre>';
// var_dump(count($s_content));
// echo '</pre>';
// exit;
// $chapter_data = [];
// $res= [];
// $num = 0;
// if(!empty($content)){
//     $s_content=preg_split('/<\/html>/', $content);
//     $s_content =array_map('trim', $s_content);
//     $s_content = array_filter($s_content);
//     foreach($s_content as $val){
//         if(!empty($val)){
//             $data = QueryList::html($val)->rules($rules)->query()->getData();
//             $chapter_data = $data->all();
//             $store_content = $chapter_data['content'] ?? '';
//             $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
//             //替换文本中的P标签
//             $store_content = str_replace("<p>",'',$store_content);
//             $store_content = str_replace("</p>","\n\n",$store_content);
//             //替换try{.....}cache的一段话JS这个不需要了
//             $store_content = preg_replace('/{([\s\S]*?)}/','',$store_content);
//             $store_content = preg_replace('/try\scatch\(ex\)/','',$store_content);
//             if(!empty($content)){
//                 $num++;
//                  $res[]=[
//                     'content'   =>$store_content,
//                 ];
//             }

//         }
//     }
// }
$exec_end_time = microtime(true);
$executionTime = $exec_end_time - $exec_start_time;
echo "共在数据库中找到的URL个数：{".count($urls)."}\t共爬取到的匹配到的内容总数： {".$num."}\r\n";
echo "Script execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "over\r\n";

?>

