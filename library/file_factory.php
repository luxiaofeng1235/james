<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2013,真一网络
// 日 期：2024-3-6
// 作　者：卢晓峰
// E-mail :luxiaofeng@linktone.com
// 文件名：file_factory.php
// 创建时间:下午2:01:55
// 编 码：UTF-8
// 摘 要: 文件工厂类，自动实现文件和对象的定义
// ///////////////////////////////////////////////////

class FileFactory{

    private $mysql_conf = null;
    private $redis_conf = null;
    private $num =500;//配置抓取的内容
    private $where_data ='1 and syn_chapter_status =  ';//默认搜索的条件
    private $table_novel_name = null;
    private $syn_success_status = 1 ;//更新章节成功的状态
    private $syn_wait_status = 0; //待处理的同步状态

    public function __construct($mysql_obj  , $redis_obj){
        $this->mysql_conf = $mysql_obj; //MySQL链接句柄
        $this->redis_conf = $redis_obj;//redis连接对象
        $this->table_novel_name = Env::get('APICONFIG.TABLE_NOVEL');//待处理的文件名
        $this->where_data .= $this->syn_wait_status;//搜索的前置条件
    }


     /**
     * @note  同步章节信息
     * @param string $story_id 小说ID
     * @return string
     */
    public function synChapterInfo($story_id = '',$info_data= []){
        if(!$story_id){
            return false;
        }
        if(!empty($info_data)){
            $info = $info_data;
        }else{
            $where = $this->where_data . ' and story_id =\''.$story_id.'\'';
            $sql = "select story_id,story_link,pro_book_id,title from ims_novel_info where $where";
            $info = $this->mysql_conf->fetch($sql,'db_slave');
        }
        if(!empty($info)){
            $pro_book_id = intval($info['pro_book_id']); //线上的对应的小说id
            $story_id = trim($info['story_id']);
            $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $pro_book_id;//下载路径;
            if(!$pro_book_id){
                printlog('暂未同步线上pro_bok_id');
                return false;
            }
            if(!is_dir($download_path)){
                createFolders($download_path);
            }
            //记录文件的格式
            $file_name =Env::get('SAVE_JSON_PATH') .DS .$pro_book_id.'.' .NovelModel::$json_file_type;
            $json_data = readFileData($file_name);
            if(!$json_data) {
                printlog('当前ID:'.$pro_book_id.'暂未生成json文件');
                return false;
            }
            $chapter_item = json_decode($json_data,true);
            if(!$chapter_item)
                return false;
            //转换数据字典
            $chapter_item = NovelModel::changeChapterInfo($chapter_item);
            $items = array_chunk($chapter_item,$this->num);
            foreach($items as $k =>&$v){
                //抓取内容信息
                $html_data= getContenetNew($v);
                //保存本地存储数据
                $this->synLocalFile($download_path,$html_data);
                sleep(1);
            }
            //更细对应的状态信息
            //更新对应的is_async状态
            $update_novel_data= [
                'syn_chapter_status'=> $this->syn_success_status,
            ];
            $where_up_data = "story_id='".$story_id."' limit 1";
            $this->mysql_conf->update_data($update_novel_data,$where_up_data,$this->table_novel_name);
            printlog('小说（'.$info['title'].'）|pro_book_id='.$pro_book_id.'|story_id='.$story_id.'同步章节完成');
            return true;
        }else{
            printlog('story_id = '.$story_id.'未匹配数据');
        }
    }

     /**
     * @note  保存本都得文件信息缓存起来
     * @param string $save_path 保存路径
     * @param array $data 读取出来的数据
     * @return string
     */
    protected function synLocalFile($save_path,$data){
        foreach($data as $key =>$val){
            if( !$val ) continue;
            $content = $val['content'] ?? '';//提交的内容
            $filename = $save_path .DS. md5($val['link_name']).'.'.NovelModel::$file_type;
            file_put_contents($filename,$content); //防止文件名出错
        }
    }
}
?>