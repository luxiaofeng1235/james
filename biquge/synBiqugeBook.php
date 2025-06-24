<?php
/*
 * 同步笔趣阁的小说数据到线上业务数据里 
 * 主要同步的数据有以下几个流程：
 * 1、同步 --已实现
 * 2、下载图片到本地的指定目录 --已实现
 * 3、同步章节数据暂时放到ims_chapter表，后期采用json存储 -待完善
 * 4、同步线上mc_book数据比对--已实现
 * Copyright (c) 2017 - zhenyi
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */

ini_set("memory_limit", "8000M");
set_time_limit(0);
require_once dirname(__DIR__).'/library/init.inc.php';
 
echo "\r\n";
echo "---------------------------------------------------------------------------------\r\n"; 


$novel_table_name = Env::get('BQG.TABLE_NAME');
$exec_start_time = microtime(true);
$startMemory = memory_get_peak_usage();
$store_id = $argv[1] ?? 0;
if(empty($store_id) || !$store_id){
	echo '请选择要抓取的内容id';
    exit();
}
$store_id = intval($store_id);
$info = BiqugeModel::getDataById($store_id);
$pro_book_id = $info['pro_book_id'] ?? 0;
$db_site_id = $info['site_id'] ??''; //获取对应的更新的源的ID

    
if($info){
	 $story_link = trim($info['story_link']);//小说的采集源
	 echo "story_link = {$story_link} \r\n";
	 //判断是否已经采集完毕
	 if($info['is_async'] == 1){
        echo "url：---".$story_link."---当前数据已同步，请勿重复同步 --pro_book_id = {$pro_book_id}\r\n";
        NovelModel::killMasterProcess();//退出主程序
        exit();
    }
	$source_ref = BiqugeModel::$source_ref; //直接用系统内置的
	$biquge_book_id = BiqugeModel::getBiqugeBookId($story_link);



	//请求书源接口--接口有获取不到的情况，重试三次去获取
	$sourceList = BiqugeService::getBookSource($biquge_book_id,false);
	    
	if(empty($sourceList['data'])){
		$num = 0;
		while(true){
			$num++;
			echo "重试获取当前的url = {$story_link} 的源\r\n";
			//三次之后直接退出
			if($num >3){
				break;
			}else{
				$sourceList = BiqugeService::getBookSource($biquge_book_id,false);
				//判断如果不为空，就直接退出
				if(!empty($sourceList['data'])){
					break;
				}
			}
		}
		//如果伦旭完成后，还是有空数据，就直接退出吧
		if(!$sourceList['data'] || empty($sourceList['data'])){
			$msg =  "未获取到小说源 id ={$biquge_book_id} 的列表数据 \r\n";
			echo $msg."\r\n";
			NovelModel::killMasterProcess();//退出主程序
			exit();
		}
	}

	    
	$sourceReferer = []; //当前的切换源
	$site_path = '';//站点的源
	//选择一个默认源，根据系统自动推荐  ===force:强制使用库里的，selectd:随机选择
	$sourceReferer = BiqugeModel::getUseHotSource($sourceList,$db_site_id,'force');
	    
	$site_path = $sourceReferer['site_path'] ?? ''; //获取默认的小说源
	$site_id = $sourceReferer['site_id'] ?? '';
	    
	if(!$sourceReferer || !$site_path){
		$msg =  "选择默认源无效，请稍后重试！ ";
		echo $msg."\r\n";
		BiqugeModel::updateSynStatusData($store_id , 0 ,$msg);//更新状态
		NovelModel::killMasterProcess();//退出主程序
		exit();
	}

	// //获取书源
	
	// if(!$site_path){
	// 	$msg =  "暂无可用书源，请稍后重试 ！";
	// 	echo $msg."\r\n";
	// 	BiqugeModel::updateSynStatusData($store_id , 0 ,$msg);//更新状态
	// 	NovelModel::killMasterProcess();//退出主程序
	// 	exit();
	// }
	    

	echo "当前书源ID= {$biquge_book_id} \t 对应的源为：{$site_id}（{$sourceReferer['site_name']}）\t选择率： {$sourceReferer['choose']}\t章节总数：{$sourceReferer['chapter_count']}\t源路径= {$site_path}\r\n";
	//拉取书源的基本详情信息
	$bookDetail = BiqugeService::getDetailInfo($biquge_book_id);
	    
	//重试详情接口，防止获取为空
	$bookDetail = BiqugeModel::callRequestDetail($bookDetail,$biquge_book_id);
	    
	    
	if(!$bookDetail){
		$warningInfo =  "暂无当前书源的详情，数据错误1 ！";
		echo $warningInfo."\r\n";
		BiqugeModel::updateSynStatusData($store_id , 0 ,$warningInfo);//更新状态
		NovelModel::killMasterProcess();//退出主程序
		exit();
	}
	//清洗笔趣阁的关联数据信息
	$store_data = BiqugeModel::initBqgStoreInfo($site_path,$story_link,$bookDetail,$site_id);
	//章节的小说+作者检测
 	if(empty($store_data['author']) || empty($store_data['title'])){
        //更新小说的当前状态

        $notice_msg= "当前小说没有作者或标题，此小说{$store_data['title']} 不需要去同步了\tpro_book_id ={$pro_book_id}\r\n";
        echo $notice_msg;
        BiqugeModel::updateSynStatusData($store_id , 0 ,$notice_msg);
        NovelModel::killMasterProcess();//退出主程序
        exit();
    }

    //只有新的小说才去更新图片下载，老的不更新
    $t = BiqugeModel::saveBiqugeBookImage($store_data['cover_logo'],$store_data['title'],$store_data['author']);
 	    
 	//$data = curlContetnsByProxy('https://res.jhkhmgj.com/bqk/436/ac/cc/25606.jpg',BiqugeRequestModel::getUsingProxy());
 	 
        

	//处理图片存储
	// $t= NovelModel::saveImgToLocal($store_data['cover_logo'],$store_data['title'],$store_data['author']);
	// dd($sourceReferer);
	//获取章节目录，查数据库
	$chapterList = BiqugeModel::getChapterListSearchDb($sourceReferer['site_id'],$sourceReferer['crawl_book_id'],$site_path,$store_id);
	    
	//获取章节目录，默认存15分钟
	// $chapterList = BiqugeModel::getBiqugeChapterList($biquge_book_id,$site_path);
	if(!empty($chapterList)){
		//获取本地的章节目录
		$localChapter = BiqugeModel::getLocalChpaterList($store_data['title'],$store_data['author']);
		//如果本地章节 >远程采集目录就停止采集
		$old_num = count($localChapter);
		$new_num = count($chapterList);
		if($new_num < $old_num){
			$warningInfo = 'title = '.$store_data['title'].' --- author = '.$store_data['author'].' ---pro_book_id ='.$pro_book_id.' 本地章节目录数据【num = '.$old_num.'】大于远端目录数据【num ='.$new_num.'】，不需要采集';
			BiqugeModel::updateSynStatusData($store_id , 0 ,$warningInfo);//更新状态
			NovelModel::exchange_book_handle($store_data ,$mysql_obj);//更新最后的采集时间
			echo "{$warningInfo}\r\n";
			NovelModel::killMasterProcess();//退出主程序
			exit();
		}
		$referer_url = Env::get('BQG.CHAPTER_URL');//章节的refer地址
		$lastStr = substr($referer_url, -1);
		if($lastStr == '/'){
			$referer_url = substr($referer_url, 0 , -1);
		}
		//同步章节目录
		BiqugeModel::createDataJson($store_data,$chapterList);
		//解下来开始同步主流程
		BiqugeModel::synCHapterInfo($store_id,$site_path,$store_data,$info);
		$novel_list_path = Env::get('SAVE_NOVEL_PATH'). DS . NovelModel::getAuthorFoleder($store_data['title'],$store_data['author']);
		echo "now_time：".date('Y-m-d H:i:s')."\tlast_update_time：".date('Y-m-d H:i:s',$store_data['third_update_time'])."\tself_store_id：".$store_id."\tnovel_path：".$novel_list_path."\t当前小说：".$store_data['title']." ---url：".$story_link."\t拉取成功，共匹配到JOSN文件的章节数量：".count($chapterList)."个\r\n";

		echo "--------------------------------------------------------------------------------------------\r\n";
		echo "*********************** pic_url = {$store_data['cover_logo']}  \r\n";
        NovelModel::killMasterProcess();//退出主程序
	}else{
		//如果没章节目录直接变更状态
		BiqugeModel::updateSynStatusData($store_id , 0 ,'未获取到张章节列表');
        printlog('未匹配到相关章节数据');
        echo "no chapter list\r\n";
        NovelModel::killMasterProcess();//退出主程序
	}
}else{
	NovelModel::killMasterProcess();//退出主程序
    echo "no data \r\n";
}
$exec_end_time = microtime(true);
$endMemory = memory_get_peak_usage();
$memoryUsage = $endMemory - $startMemory;//内存占用情况
$executionTime = $exec_end_time - $exec_start_time; //执行时间
echo "run execution time: ".round(($executionTime/60),2)." minutes \r\n";
echo "peak memory usage:" . $memoryUsage ." bytes \r\n";
echo "---------------------------------------------------------------------------------\r\n";
?>