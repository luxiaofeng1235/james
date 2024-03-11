<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :process_url.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:处理进程的有问题未拉取下来的章节去同步
// ///////////////////////////////////////////////////

class ProcessUrl{

    //自动计算有问题的url从远端进行抓取
    public static function selfRunUrls($info){
        if(!$info)
            return false;
        global $mysql_obj;

        $title = $info['title'];
        $author = $info['author'];

        $md5_str= NovelModel::getAuthorFoleder($title,$author);
        //echo 'md5：'.$md5_str.PHP_EOL;
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
                $arr =array_chunk($run_list,200);
                foreach($arr as $key =>$val){
                    //获取最新的数据信息
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
    }
}
?>