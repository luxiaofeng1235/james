<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
require_once($dirname.'/library/file_factory.php');

$title ='腹黑男神：迷糊小财女';
$author ='月溪沙';
$md5_str= NovelModel::getAuthorFoleder($title,$author);
echo 'md5：'.$md5_str.PHP_EOL;


$json_path = Env::get('SAVE_JSON_PATH').DS.$md5_str.'.'.NovelModel::$json_file_type;

$urls = $run_list=[];
$list = readFileData($json_path);
if($list){
    $log_path  = Env::get('SAVE_NOVEL_PATH').DS.$md5_str;
    $arr = json_decode($list,true);
    foreach($arr as $val){
        $filename =$log_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
        $content = readFileData($filename);
        if(!$content || $content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新' || !file_exists($filename)){
            $run_list[]=array(
                'file_path' =>$filename,
               'link_name'  =>$val['chapter_name'],
               'link_url'   =>  str_replace('http://www.paoshu8.info','',$val['chapter_link'])
            );
        }
    }
}
$success_num=0;
$insert_data = [];
$len = count($run_list); //需要跑的总数
$run_times = 0;
if($len>0){
     do{
        $run_times++;
        //整体轮询五次
        if($run_times>5){
            break;
        }
        echo "zong-num：".$run_times.PHP_EOL;
        $arr =array_chunk($run_list,5);
        foreach($arr as $key =>$val){
             $content = getContenetNew($val);
             if($content && is_array($content)){
                foreach($content as $k =>$v){
                    if(!empty($v['content'])){
                        $success_num++;
                        $insert_data[]=$v;
                    }
                }
             }
         }
         //只有为待采集和新的数据为空蔡旭哟啊进行处理
         if($len>0 && $len == $success_num){
              foreach($insert_data as $gval){
                  file_put_contents($gval['file_path'],$gval['content']);
                  echo $gval['file_path'].PHP_EOL;
              }
              break;
         }
    }while(true);
}
echo "over\r\n";


?>