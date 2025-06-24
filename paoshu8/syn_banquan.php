<?php
require_once dirname(__DIR__) . '/library/init.inc.php';
require_once dirname(__DIR__) . '/library/file_factory.php';

use Overtrue\Pinyin\Pinyin;

echo "start_time：".date('Y-m-d H:i:s')."\r\n";
$imgType='jpg';
$db_master = 'db_master';
$sql  ="select * from ims_import_book";
$list = $mysql_obj->fetchAll($sql  , $db_master);


//获取图片的拼装路径
function getImageName($title,$author){
    if(!$title || !$author){
        return false;
    }
    global $imgType;
    $pinyin = new Pinyin();
    $trimBlank = function ($arr) use (&$pinyin) {
        //保留数字的转换方式
        $ext_data = $pinyin->name($arr, PINYIN_KEEP_NUMBER); //利用多音字来进行转换标题
        $str = '';
        //利用空数据来进行转换
        if (!empty($ext_data)) {
            foreach ($ext_data as $val) {
                //如果匹配到了数字就直接用数字返回，不需要做处理
                if (preg_match('/[0-9]/', $val)) {
                    $str .= $val;
                } else {
                    $str .= $val[0];
                }
            }
        }
        return $str;
    };
    $title_string = $trimBlank($title);
    $author_string = $trimBlank($author);

    $cover_logo =  $title_string . '-' . $author_string . '.' . $imgType;
    $monthDay = date('Ym'); #获取每周的日期的星期一，以这样子的格式存储
    $save_filename = Env::get('SAVE_IMG_PATH') . DS . $monthDay.DS. $cover_logo;
    $save_path = dirname($save_filename);
    //判断是否存在该目录
    if(!is_dir($save_path)){
        createFolders($save_path); //创建文件夹
    }
    return $save_filename;

}


function createJsonList($content,$title,$author,$len_size = 3000){
    if(!$content){
        return false;
    }
    //获取章节目录信息
    $chapter_list = mbStrSplitChapter($content,$len_size);
    if($chapter_list){
        $json_list = [];
        foreach($chapter_list as $key =>$val){

            $json_list[] = [
                'id'    => $key + 1, //ID
                'sort'  => $key + 1,   //排序
                'chapter_link'  => '', //抓取的地址信息
                'chapter_name'  => "第".convertChineseUppercase($key+1)."章", //文件名称
                'vip'   =>  0, //是否为VIP
                'cion'  =>  0, //章节标题
                'is_first' =>   0, //是否为首页信息
                'is_last'   => 0, //是否为最后一个
                'text_num'  => rand(3000, 10000), //随机生成文本字数统计
                'addtime'   => time(), //添加时间
            ];
        }

        $md5_str = NovelModel::getAuthorFoleder($title, $author);
        if(!$md5_str){
            return false;
        }
        $save_path = Env::get('SAVE_JSON_PATH').DS.substr($md5_str, 0,2); //保存json的路径
        //获取对应的json目录信息
        if (!is_dir($save_path)) {
            createFolders($save_path);
        }
        $filename = NovelModel::getBookFilePath($title, $author);
        echo "path = {$filename}\r\n";
        // $filename = $save_path . DS . $md5_str . '.' . self::$json_file_type;
        //保存对应的数据到文件中方便后期读取
        $json_data = json_encode($json_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        writeFileCombine($filename, $json_data); //把json信息存储为对应的目录中去
        return $json_list;
    }else{
        return [];
    }
    return $content;

}

$infos = [];
//处理章节
if($list){
    $num = 0;
    foreach($list as $key=>$val){
        $num++;
        $book_path = $val['book_path'] ??'';
        if(!$book_path){
            continue;
        }
        //读取文本内容
        $content = readFileData($book_path);
        $content = iconv('GBK', 'utf-8//ignore', $content);
        $filename = NovelModel::getBookFilePath($val['title'], $val['author']);
        if(!file_exists($filename)){
            if($content){
                 $json_list = createJsonList($content,$val['title'],$val['author']);
                 echo "num = {$num}\tid ={$val['id']}\ttitle={$val['title']}\tauthor={$val['author']}\t创建JSON成功\r\n";
            }else{
                echo "num = {$num}\tid  = {$val['id']}\ttitle={$val['title']}\tno data\r\n";
            }
       }else{
            $val['json_path'] = $filename;
            $infos[$val['id']] =$val;
            echo  "num = {$num}\tid  = {$val['id']}\ttitle={$val['title']}\tjson exists\r\n";
       }
    }
}

/**
 * @note  保存本都得文件信息缓存起来
 * @param string $save_path 保存路径
 * @param array $data 读取出来的数据
 * @return string
 */
function synLocalFileNew($save_path,$data){
    if(!$data){
        return [];
    }
    foreach($data as $key =>$val){
        $content = $val['content'] ?? '';//提交的内容
        $save_path = $val['save_path'] ?? '';
        if(!$save_path || !$content) continue;
        writeFileCombine($save_path, $content); //写入文件，以追加的方式，由于移动端带有分页，有可能是某个章节在第二页所以要处理下。
         //用md5加密的方式去更新
        // $filename = $save_path .DS. md5($val['link_name']).'.'.NovelModel::$file_type;
        // file_put_contents($filename,$content); //防止文件名出错
    }
}


//保存设置的同步书籍
function saveContents($content,$json_path,$title,$author,$len_size = 3000){
    if(!$content || !$json_path){
        return false;
    }

    $json_data = readFileData($json_path);
    $chapter_item = [];
    //读取对应的配置信息
    $md5_str= NovelModel::getAuthorFoleder($title ,$author);
    $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;//下载路径;
    if(!is_dir($download_path)){
        createFolders($download_path);
    }
    if($json_data){
         $chapter_list = mbStrSplitChapter($content,$len_size);
         if(!$chapter_list){
            $chapter_list = []; //特殊判断下
         }
         $chapter_item = json_decode($json_data,true);
         foreach($chapter_item as $key => &$val){
            $filename =$download_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
            $val['save_path'] = $filename;
            $val['content'] = isset($chapter_list[$key]) ? $chapter_list[$key]  : '';
         }

    }
    if($chapter_item){
       $html_data = $chapter_item ?? [];
        $a_num =0;
        foreach ($html_data as  $gvalue) {
            $a_num++;
            echo "num：{$a_num} \t length=".mb_strlen($gvalue['content'],'utf-8') ."\t chapter_name: {$gvalue['chapter_name']}\t path：{$gvalue['save_path']} \r\n";
        }
        synLocalFileNew($download_path,$html_data);
    }
    return $json_path;
}

//处理业务的合性导入流程，导入的时候批量写入文件信息
if(!empty($infos)){
    foreach($infos as $value){
        $content = readFileData($value['book_path']);
        $content = iconv('GBK', 'utf-8//ignore', $content);
        $json_data = readFileData($value['json_path']);
        $item = json_decode($json_data,true);
        if($item){

        }

        //组装同步接口的数据信息
        $value['tag'] = '网游';
        $value['story_link'] ='qijitushu';
        $value['status'] = '已经完本';
        $value['cate_name'] = '网游竞技';
        $value['tid'] = 0;
        $value['cover_logo'] = $value['mc_pic'];
        $value['third_update_time'] =  time();
        $pro_book_id = NovelModel::exchange_book_handle($value,$mysql_obj);
        if(!$pro_book_id) $pro_book_id = 0;

        $sql = "update ims_import_book set pro_book_id = {$pro_book_id} where id  ={$value['id']}";
        $mysql_obj->query($sql,$db_master);
        echo "sql = {$sql}\r\n";
        echo "id = {$value['id']}\ttitle={$value['title']}\tauthor={$value['author']}\r\n";
        //同步书籍ID
        saveContents($content,$value['json_path'],$value['title'],$value['author']);
    }
}

//处理图片信息
// foreach($list as $key =>$val){
//     if(!$val) continue;
//     $title=  trim($val['title']);
//     $author  =trim($val['author']);
//     $book_path = trim($val['book_path']);
//     $img_path  = trim($val['img_path']);
//     $mc_pic = trim($val['mc_pic']);
//     $id = intval($val['id']);
//     if(file_exists($mc_pic)){
//         // $pic_img = getImageName($title,$author);
//         // echo "key ={$key}\t pic = {$pic_img}\r\n";
//         // if(!file_exists($pic_img)){
//         //     if (copy($img_path, $pic_img)) {
//         //             $sql = "update ims_import_book set mc_pic='{$pic_img}' where id ={$val['id']} limit 1";
//         //             $mysql_obj->query($sql,$db_master);
//         //             echo "id ={$id}\ttitle ={$title}\author ={$author}\tfrom:{$img_path}\tto:{$pic_img}\t图片复制成功\r\n";
//         //         }else{
//         //             echo "id ={$id}\ttitle ={$title}\author ={$author}\t复制失败\r\n";
//         //     }
//         // }else{
//         //       $sql = "update ims_import_book set mc_pic='{$pic_img}',updatetime=".time()." where id ={$val['id']} limit 1";
//         //      echo "id ={$id}\ttitle ={$title}\author ={$author}\texists111\r\n";
//         //      $mysql_obj->query($sql,$db_master);
//         // }
//     }else{
//         echo 1111;
//         // echo "id ={$id}\rtitle ={$title}\author ={$author}\texists\r\n";
//     }
// }

echo "finish\r\n";
echo "finish_time：".date('Y-m-d H:i:s')."\r\n";
?>