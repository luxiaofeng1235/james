<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2013,真一网络
// 日 期：2024-03-06
// 作　者：卢晓峰
// E-mail :luxiaofeng@linktone.com
// 文件名：file_factory.php
// 创建时间:下午2:01:55
// 编 码：UTF-8
// 摘 要: 文件工厂类，自动实现远端章节同步到本地
// ///////////////////////////////////////////////////

class FileFactory{

    private $mysql_conf = null;
    private $redis_conf = null;
    private $num =null;//配置抓取的内容
    private $where_data ='1 and syn_chapter_status =  ';//默认搜索的条件
    private $table_novel_name = null;
    private $syn_success_status = 1 ;//更新章节成功的状态
    private $syn_wait_chapter_status = 0;//待处理的章节同步状态
    private $syn_wait_status = 0; //待处理的同步状态

    public function __construct($mysql_obj  , $redis_obj){
        $this->num = Env::get('LIMIT_SIZE');//获取对应的长度
        $this->mysql_conf = $mysql_obj; //MySQL链接句柄
        $this->redis_conf = $redis_obj;//redis连接对象
        $this->table_novel_name = Env::get('APICONFIG.TABLE_NOVEL');//待处理的文件名
        $this->where_data .= $this->syn_wait_status;//搜索的前置条件
    }


    /**
     * @note  更新mc_book的状态
     * @param string $story_id 小说ID
     * @return string
     */
    public function updateDownStatus($pro_book_id= 0){
        if(!$pro_book_id){
            return false;
        }
        global $mysql_obj;
        $where_condition = "id = '".$pro_book_id."'";
        $no_chapter_data['is_less'] = 2;
        //对比新旧数据返回最新的更新
        $mysql_obj->update_data($no_chapter_data,$where_condition,'mc_book',false,0,'db_master');
    }




    /**
     * @note  更新最后的采集章节信息
     * @param string $title 小说名
     * @param string $author 作者
     * @return string
     */
    public function updateLastUpdateTime($title,$author){
        if(!$title || !$author){
            return false;
        }
        global $mysql_obj;
        $where_condition = "book_name = '".$title."' and author = '{$author}'";
        $update_data['update_chapter_time']  = time();
        $mysql_obj->update_data($update_data,$where_condition,'mc_book',false,0,'db_master');
            
    }

      /**
     * @note  更新首页状态
     * @param string $story_id 小说ID
     * @return string
     */
    public function updateIndexStatus($store_id){
        if(!$store_id){
            return false;
        }
        global $mysql_obj;
        $where_condition = "store_id = '".$store_id."'";
        $no_chapter_data['is_run'] = 1;
        //对比新旧数据返回最新的更新
        $mysql_obj->update_data($no_chapter_data,$where_condition,'ims_index_id');
    }

     /**
     * @note  更新状态小说同步状态信息
     * @param string $story_id 小说ID
     * @param string $note 备注说明
     * @return string
     */
    public function updateStatusInfo($store_id= 0,$note=""){
        if(!$store_id){
            return false;
        }
        global $mysql_obj;
        $where_condition = "store_id = '".$store_id."'";
        $no_chapter_data['syn_chapter_status'] = 1;
        $no_chapter_data['is_async'] = 1;
        $no_chapter_data['empty_status'] = 0;//如果有数据更新就更新为0
        if($note){
            $no_chapter_data['note'] = $note;
        }
        //对比新旧数据返回最新的更新
        $mysql_obj->update_data($no_chapter_data,$where_condition,$this->table_novel_name);
        return true;
    }

    /**
    * @note  更新当前状态为待同步
    * @param string $story_id 小说ID
    * @param string $note 备注信息
    * @return string
    */
    public function updateUnRunInfo($store_id= 0,$note=""){
        if(!$store_id){
            return false;
        }
        global $mysql_obj;
        $sql = "update {$this->table_novel_name} set syn_chapter_status = {$this->syn_wait_chapter_status} ,is_async ={$this->syn_wait_status},note=
        '{$note}' where store_id = '{$store_id}' limit 1";
        $result = $mysql_obj->query($sql , 'db_master');
    }


    /**
     * @note  同步章节信息
     * @param string $story_id 小说ID
     * @param array $info_data 小说基础信息
     * @return string
     */
    public function synChapterInfo($story_id = '',$info_data= []){
        if(!$story_id){
            return false;
        }

        // //判断数据是否为空
        $info = $info_data;
        if(!empty($info)){
            $pro_book_id = intval($info['pro_book_id']); //线上的对应的小说id
            $story_id = trim($info['story_id']); //小说网站id
            $store_id = intval($info['store_id']); //库里的id
            //获取对应的加密串作为文件名：书名+作者
            $md5_str= NovelModel::getAuthorFoleder($info['title'] ,$info['author']);
            $download_path =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;//下载路径;
            if(!$pro_book_id){
                // $this->updateStatusInfo($store_id);
                // #$this->#$this->updateIndexStatus($store_id);($store_id);
                // $this->updateDownStatus($pro_book_id); //更新对应的状态信息
                echo '暂未同步线上pro_bok_id';
                NovelModel::killMasterProcess();//退出主程序
                return false;
            }
            if(!is_dir($download_path)){
                createFolders($download_path);
            }
            //记录文件的格式

            $file_name =NovelModel::getBookFilePath($info['title'],$info['author']);
            echo "json_path = {$file_name}\r\n";

            $json_data = readFileData($file_name);
            if(empty($json_data)) {
                $this->updateStatusInfo($store_id);
                echo "当前小说未生成json文件\r\n";
                printlog('当前ID:'.$pro_book_id.'暂未生成json文件');
                NovelModel::killMasterProcess();//退出主程序
                return false;
            }
            $chapter_item = json_decode($json_data,true);
            if(!$chapter_item){
                $this->updateStatusInfo($store_id);
                #$this->#$this->updateIndexStatus($store_id);($store_id);
                // $this->updateDownStatus($pro_book_id); //更新对应的状态信息
                // echo  "去除广告暂无发现需同步的章节了 \r\n";
                NovelModel::killMasterProcess();//退出主程序
                exit(1);
            }
                
            //去掉收尾空格处理--误删
            foreach($chapter_item as &$v){
                $v['chapter_name'] = trim($v['chapter_name']);
            }
            echo "JSON文件里的总章节总数：".count($chapter_item).PHP_EOL;
            $dataList = [];
            // $sucNum = 0;
            $sucNum = 0;
            foreach($chapter_item as &$val){
                $filename =$download_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
                $val['file_path'] = $filename;
                $content = readFileData($filename);
                if(!$content 
                    || strlen($content)<300 //不够200下次从这个地方进行更新，有数据更新
                    ||$content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  
                    || !file_exists($filename)
                    || strstr($content,'暂无相关章节信息')
                    || strstr($content, '未完待续...')
                ){
                    $chapter_link = $val['chapter_link'] ?? '';
                    //调过这一段内的指定点的链接
                    $val['link_url'] = $chapter_link;
                    $dataList[] =   $val;
                }else{
                    $sucNum++;
                }
            }
                
            if(!$dataList){
                $this->updateStatusInfo($store_id); //更新状态信息
                NovelModel::killMasterProcess();//退出主程序
                exit("*********************************title = {$info['title']} \t author = {$info['author']} \tstore_id = {$store_id}\t pro_book_id ={$info['pro_book_id']} \t已经爬取完毕 ，不需要重复操作了\r\n");
            }
            echo "\r\n\r\n";
            echo "共需要补的章节总数量： num = ".count($dataList)."\r\n";
            //转换数据字典用业务里的字段，不和字典里的冲突
            $dataList = NovelModel::changeChapterInfo($dataList);
            //按照长度进行切割轮询处理数据
            $limit_size = 200;

            $items = array_chunk($dataList,$limit_size); //默认每一页300个请求，到详情页最多300*3=900个URL 这个是因为移动端的原因造成
            $i_num = 0;
            $count_page= count($items); //总分页数
            echo "总分页总数：".$count_page." \t 每页步长数：$limit_size\n";


            foreach($items as $k =>&$v){
                $html_data= NovelModel::getDataListItem($info['title'],$v,$download_path);
                if($html_data){
                    $a_num =0;
                    foreach ($html_data as  $gvalue) {
                        $a_num++;
                        if(!empty($gvalue['content'])){
                            //方便调试,遇到有的章节空的path或者name为空，需要排查下
                            if(empty($gvalue['save_path']) || empty($gvalue['chapter_name'])){
                                echo "----".$gvalue['chapter_mobile_link']."\r\n";
                            }
                            echo "num：{$a_num} \t length=".mb_strlen($gvalue['content'],'utf-8') ."\t chapter_name: {$gvalue['chapter_name']}\t url：{$gvalue['chapter_mobile_link']}\t path：{$gvalue['save_path']} \r\n";
                            $i_num++;
                        }else{
                            echo "num：{$a_num} \t chapter_name: {$gvalue['chapter_name']} \t 小说源内容为空 url：{$gvalue['chapter_mobile_link']}\r\n";
                        }
                    }
                    //保存本地存储数据
                    $this->synLocalFile($download_path,$html_data);
                    echo "\r\n|||||||||||||||| this current page =  (".($k+1)."/{$count_page})\t store_id = {$store_id} \tcomplate \r\n\r\n";
                    // echo "休息一秒后再继续运行----------------\r\n";
                    sleep(1);//休息三秒不要立马去请求，防止空数据的发生
                }else{
                    echo "num：{$a_num} 未获取到数据，有可能是代理过期\r\n";
                }
            }
            echo "共抓取下来的章节总数：".$i_num.PHP_EOL;
            unset($items);
            //强制清除内存垃圾
            gc_collect_cycles();
            unset($items);
            unset($chapter_item);
            //更细对应的状态信息
            //更新对应的is_async和syn_success_status状态
            $this->updateStatusInfo($store_id);//更新对应的状态，暂时不用，根据是否爬取完毕判断
            printlog('小说（'.$info['title'].'）|pro_book_id='.$pro_book_id.'|story_id='.$story_id.'同步章节完成');
            return true;
        }else{
            NovelModel::killMasterProcess();//退出主程序
            printlog('story_id = '.$story_id.'未匹配数据');
        }
    }

    /**
     * @note  转换添加的小说信息
     * @param string $datas  小说数据
     * @return string
     */
    protected function exchangeNovelData($datas= []){
        if(!$datas)
            return false;
        return $datas;
    }

    /**
     * @note  保存本都得文件信息缓存起来
     * @param string $save_path 保存路径
     * @param array $data 读取出来的数据
     * @return string
     */
    public function synLocalFile($save_path,$data){
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
}
?>
