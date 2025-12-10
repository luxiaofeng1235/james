
<?php
//处理线上笔趣阁

/*
 * 处理线上笔趣阁的采集流程 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */

class BiqugeModel{


	private static $db_collect_conn = 'db_master'; //默认采集库
	private static $db_master ='db_master';//线上数据节点同步
	private static $collect_table_name = 'ims_biquge_info';//笔趣阁线上的接口数据
	private static $online_table_name = 'mc_book';//线上表
	private static $method = 'get';
	public static  $source_ref= 'biqugeAPI';
	private static $db_bqg_source = 'db_bqg_source'; //笔趣阁总库
	private static $db_bqg_collect = 'db_bqg_collect';//笔趣阁采集库
	protected static $bqg_table_name = 'book';//笔趣阁的通用表名

	public static  $timeout = 900; //过期时间设置15分钟



	/**
	* @note 获取并拆分计算笔趣阁的关联动态数据
	*
	* @return unnkower
	*/
	public static function getRankList(){
		$rankConfig = BiqugeService::getRankConfig();
		$rankItem = [];
		if(isset($rankConfig['code']) && $rankConfig['code'] == 1){
			$data = $rankConfig['data'] ?? [];
			foreach($data as $stype =>$val){
				foreach($val as $gkey =>$gval){
					if(!empty($gval)){
						foreach ($gval as $ckey => $cvalue) {
							if(!$cvalue['page_id']) continue;
						   	$rankItem[]=[
						   		'name'	=>	trimBlankSpace($cvalue['name']),//板块名称
						   		'page_id'	=> trimBlankSpace($cvalue['page_id']),
						   	];  
						}
					}  
				}
			}
		} 
		return $rankItem;
	}

	/**
	* @note 重新抓取采集的detail的关联信息,防止接口出错
	* @param $bookDetail array 书籍信息 
	* @param $biquge_book_id int 书籍ID
	* @return array
	*/
	public static function callRequestDetail($bookDetail=[],$biquge_book_id=0){
		//这里判断不能为空ID，不然没法采集
		if(!$biquge_book_id){
			return [];
		}
		//数组特殊做一下过滤判断
		if(!$bookDetail || !is_array($bookDetail)){
			$bookDetail =[];
		}
		if(empty($bookDetail)){
			$num = 0;
			//轮训判断5次去拉取，这样子起码保证数据都有值
			while(true){
				$num++;
				echo "重试获取当前book_id= {$biquge_book_id}\t的关联书籍详情！！！\r\n";
				//三次之后直接退出
				if($num >5){
					break;
				}else{
					//获取详情信息
					$bookDetail = BiqugeService::getDetailInfo($biquge_book_id);
					if(!empty($bookDetail)){
						echo "匹配到相关的书籍详情数据信息\r\n";
						break;
					}					    					
				}
			}
		}else{
			echo "当前书籍ID= {$biquge_book_id}\t有详情数据，暂不需要重新获取\r\n";
		}
		return $bookDetail;
	}

	/**
	* @note 处理输入字符
	* @param  $str string 输入字符
	* @return array
	*/
	    
	public static function stringHandleInput($str){
		if(!$str){
			return false;
		}
		$string = trimAllSpace($str); //去除标题空格
		$string = myaddslashes($string); //处理mysql的转义字符
		return $string;
	}


	/**
    * @note 通过数据库查章节名称
    *
    * @param  $site_id string 书名
    * @param $crawl_book_id int  书名
    * @param $site_path int 源路径
    * @param $store_id int 本地采集ID
    * @return  arary
    */
	public static function getChapterListSearchDb($site_id='',$crawl_book_id=0,$site_path='',$store_id = 0){
		$site_id = trim($site_id);
		    
		$crawl_book_id = intval($crawl_book_id);
		$site_path = trim($site_path);
		if(!$site_id || !$crawl_book_id || !$site_path){
			return false;
		}
		$pathArr = explode('/',$site_path);
		$num = 0;
		if(isset($pathArr[1]) && !empty($pathArr[1])){
			$num = intval($pathArr[1]);
		} 
		$mysql_obj = self::getMyqlObj();
		$chapterList = [];
		if($site_id=='merge'){
			$field = 'id,chapter_name,\'\' as url,chapter_path,updated_at,chapter_collect_state';
		}else{
			$field = 'id,chapter_name,url,chapter_path,updated_at,chapter_collect_state';
		}
		    
		$sql ="select {$field} from {$site_id}_chapter.chapter_{$num} WHERE book_id = ".intval($crawl_book_id);
		echo "chapter_sql = {$sql}\r\n";
		$lists = $mysql_obj->fetchAll($sql,self::$db_bqg_collect);
		if($lists){
			if($site_id == 'merge'){
				//merge站用默认的的https://chapter.jhkhmgj.com/ 不然其他请求不通
				// $chapter_url = Env::get('BQG.CHAPTER_URL2');
				$chapter_url = Env::get('BQG.CHAPTER_URL');
			}else{
				$chapter_url = Env::get('BQG.CHAPTER_URL3');
			}			  
			    
			$failureCount  = 0;  
			foreach($lists as $key =>$val){
				if(!$val) continue;
				$url  = trim($val['url']);
			 	$chapter_path = $val['chapter_path'];
			 	$chapter_collect_state = intval($val['chapter_collect_state']);//章节采集状态:0.等待采集，1.已采集，2.采集失败
			 	//这个里面需要对特殊的标签需要再处理一层
			 	if(
			 		strstr($chapter_path,'newbqk') 
			 		|| strstr($chapter_path,'newidj') 
			 		|| strstr($chapter_path,'newvodtw') 
			 		||  strstr($chapter_path,'wsg') 
			 		|| strstr($chapter_path,'xsjtxt')
			 		|| strstr($chapter_path,'whzh')

			 	){
				 	$chapter_url = Env::get('BQG.CHAPTER_URL2'); //包含这个里面用 https://chapter.lmmwlkj.com/这个域名
				}else if($site_id == 'z'){
					//这个站比较特殊需要走代理
					$chapter_url = Env::get('BQG.CHAPTER_URL1'); //z站用https://chapter.ycukhv.com/这个域名
				}
				    
				if($chapter_collect_state == 1){ //采集成功
				 	if(!empty($url)){//url不为空
				 		//先判断是否包含http或者https在判断其他的
					 	if(strstr($url,'http') || strstr($url,'https')){
					 		//如果不是以这个域名的，就用path替换还用https://chapter.chuangke.tv/进行轮训查询
						 	if(!strstr($url, 'chuangke.tv')){
						 		$returnPath =  sprintf("%s%s",$chapter_url,$chapter_path);
						 	}else{
						 		$returnPath = $url;
						 	}
						 }else{
						 	//如果没有http的请求，就用默认的https://chapter.chuangke.tv/ 域名+后面的短锥拼接
						 	 $returnPath = sprintf("%s%s",$chapter_url,$url);
						 }
				 	}else{
				 		//如果为空，用默认的域名https://chapter.chuangke.tv/+chapter_path进行拼接
				 		$returnPath =  sprintf("%s%s",$chapter_url,$chapter_path);
				 	}
				 	 //检查当前的url是否为可用url
				 	 // $returnPath = self::checkUrlIsAvailable($returnPath);
				 }else{ //采集失败的
				 	$failureCount++; //解析失败就+1
				 	//采集失败给一个的url信息，防止报错
				 	$returnPath =sprintf("%s%s.html",$chapter_url,generateRandomString(10));
				}
				$info['link_name'] = trimBlankSpace($val['chapter_name']);//章节名去除首尾空格
				$info['link_url'] = $returnPath;
				$info['createtime'] = time();
				$chapterList[] = $info;

			}  
		}
		    
		    
		    
		    
		//错章率统计，如果错账率在70%就直接退出不采集这种就比较高了 没啥意义的书
		$allChapterCount = count($chapterList); //总的章节总数
		if($failureCount>0){
			$failurePercentage = sprintf("%.2f",$failureCount/$allChapterCount*100);
			//如果某个失败占比太高就不用起采集了
			if ($failurePercentage>70){
				echo "site_id={$site_id}\t失败率太高啦，不用采集啦 ，\t失败的百分比: " . $failurePercentage . "%\n";
				BiqugeModel::updateSynStatusData($store_id , 0 ,"章节占比错误率太高了 failNum = ({$failureCount}/{$allChapterCount})，failurePercent ={$failurePercentage} %"); //更新章状态
				NovelModel::killMasterProcess();//退出主程序
				exit();
			}
		}
		return $chapterList;

	}

	/**
	* @note 检查当前的url是否为可用的url
	*
	* @param  $url string 当前域名
	* @return  $url
	*/

	// private static function  chcekBiqugeState($url =''){
	// 	if(!$url){
	// 		return false;
	// 	}
	// 	$header = get_headers($url, 1);
	// 	$codeContent = $header[0] ?? '';
	// 	if (strpos($codeContent, '200')) {
	// 		return true;
	// 	}else{
	// 		return false;
	// 	}  
		    
	// }

	/**
	* @note 检查当前的url是否为可用的url
	*
	* @param  $url string 当前域名
	* @return  $url
	*/
	public static function checkUrlIsAvailable($url =''){
		if(!$url){
			return false;
		}
		
		$urlData = parse_url($url);
		$path = $urlData['path'] ?? '';
		//检查原路径是否失效
		if(!self::chcekBiqugeState($url)){
			 $url1 = Env::get('BQG.CHAPTER_URL1').$path;
			 //用https://chapter.ycukhv.com//这个域名来检查 
			 if(!self::chcekBiqugeState($url1)){
			 	//用https://chapter.lmmwlkj.com/来检查域名
			 	$url2 = Env::get('BQG.CHAPTER_URL2').$path;
			 	//如果url2在这个地方成功了，就返回用心的替换了
			 	if(!self::chcekBiqugeState($url2)){
			 	    $url = $url2;	
			 	}else{
			 		$url = $url2;
			 	}
			}else{
				//如果当前域名存在就用他进行赋值
				$url = $url1; //用url1进行返回
			}
		}
		    
		return $url;
	}


	/**
    * @note 获取本地的章节目录的列表数据，做比较
    *
    * @param  $book_name string 书名
    * @param $author stirng 作者
    * @return  $url
    */
	public static function getLocalChpaterList($book_name,$author){
		$book_name= trimBlankSpace($book_name);
		$author = trimBlankSpace($author);
		if(!$book_name || !$author){
			return [];
		}
		    
		$filepath = NovelModel::getBookFilePath($book_name,$author);
		$json_data = readFileData($filepath);
		$chapter_item = [];
		if(!empty($json_data)){
			$chapter_item = json_decode($json_data,true);
		}
		return $chapter_item;
		    
	}

	/**
    * @note 通过泡书吧的搜索页面获取封面
    *
    * @param  $book_name string 书名
    * @param $author stirng 作者
    * @return  $url
    */
	public  static function getCoverLogoByPaoshu8($book_name,$author){
		$book_name= trimBlankSpace($book_name);
		$author = trimBlankSpace($author);
		if(!$book_name || !$author){
			return false;
		}
		global $urlRules;
		//利用书名进行搜素
		$keywords = urlencode($book_name);
		$cover_logo ='';
		$url = "http://www.paoshu8.info/modules/article/search.php?searchkey={$keywords}";
		echo "search-keyword-urlk ={$url}\r\n";
		$html = webRequest($url,'GET');
		//利用正则匹配搜索结果
		preg_match('/<table class=\"grid\".*?>.*<\/table>/ism', $html, $list);
		if($list){
			$contents = $list[0] ??'';
			//匹配出来对应的url信息
			$range = '#nr';
			$data = \QL\QueryList::html($contents)
							->rules([
								'book_name'=>['td:eq(0) a','text'],//书名
								'link_url'=>['td:eq(0) a','href'],//请求的url
								'author'=>['td:eq(2)','text'],//作者名
							])
							->range($range)
			    			->query()
			    			->getData();
			$info_data = $data->all();
			if(!empty($info_data)){
				foreach($info_data as $val){
					$input_name = trimBlankSpace($val['book_name']);
					$input_author = trimBlankSpace($val['author']);
					if(!$input_name || !$input_author) continue;
					//判断如果传入的和实际的作者+书名相等，就把这个url取出来
					if($book_name == $input_name && $author == $input_author){
						$link_url = $val['link_url'];
						if (!$link_url){
							continue;
						}
						echo "匹配到book_name = {$book_name}\tauthor={$author}\turl = {$link_url}\r\n";
						$html = webRequest($link_url,'GET');//请求对应的详情页里的内容
						$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['default_info'];
						//匹配查出来抓取到的logo信息
						$detailData = \QL\QueryList::html($html)
							->rules($rules)
			    			->query()
			    			->getData();
			    		$detailData = $detailData->all();
			    		$cover_logo = $detailData['cover_logo']  ?? '';
			    		break;
			    		      
					}    
				}
			}
		}
		return $cover_logo;
	}


	/**
    * @note 获取封面信息，通过book_id，只有获取封面失败才从这个地方获取
    *
    * @param  $book_id int 书的ID
    * @param $site_id sting 小说ID
    * @param $referer string 小说的前缀
    * @return  $url
    */
    public static function getCoverInfoByDataBase($book_id=0,$site_id = '',$referer=''){
        $book_id = intval($book_id);
        if(!$book_id){
            return false;
        }
        //http://www.paoshu8.info/modules/article/search.php?searchkey=%E6%88%91%E5%9C%A8%E7%BA%A2%E6%A5%BC%E4%BF%AE%E4%BB%99 直接用跑书吧的对应的搜索页面直接返回算了，匹配打开一个url获取里面的章节
        $mysql_obj = self::getMyqlObj();
        $sql  = "select id,last_site_id,last_crawler_book_id,name,author,image,is_contains_ai from ".self::$bqg_table_name." where id = ".$book_id;
        $info = $mysql_obj->fetch($sql, self::$db_bqg_source);
        $bookInfo = [];
        if($info && !empty($info['last_site_id']) && !empty($info['last_crawler_book_id'])){
        	echo "matches records name ={$info['name']}\tauthor = {$info['author']}\r\n";
        	//拿出来判断AI源
        	$last_site_id = trim($info['last_site_id']);
        	
        	if ($last_site_id == 'merge'){
        		$cover_filed ='cover as source_cover';
        	}else{
        		$cover_filed ='source_cover';
        	}
        	    
        	$is_contains_ai = intval($info['is_contains_ai']);//是否包含AI源
        	$last_crawler_book_id = intval($info['last_crawler_book_id']);//最后更新的书源ID
        	$image = trim($info['image']);//图片
        	$condition = " app_source_book_id = ".$book_id;

        	//拼装查询的采集库信息
        	$sql  = "select *  from ".trimBlankSpace($info['last_site_id']).'_collect.'.self::$bqg_table_name." where ".$condition;
        	echo "sql = {$sql} \r\n";
        	$book_cover = '';
        	$bookInfo = $mysql_obj->fetch($sql,self::$db_bqg_collect);	   
        	      
        	if(isset($bookInfo['source_cover']) && !empty($bookInfo['source_cover'])){//优先先取source_cover里的
        		$book_cover = trim($bookInfo['source_cover']);
        	}else if(isset($bookInfo['cover']) && !empty($bookInfo['cover'])){
        		$book_cover = trim($bookInfo['cover']);//如果cover_logo为空，取cover里的
        	} 
        	if(!$book_cover){
        		//如果查询没有用默认的图片返回
        		echo "matches records name ={$info['name']}\tauthor = {$info['author']}\t no cover url  default_url = {$image}\r\n";
        		$defaultimg =  sprintf("%s%s",$referer,$image);
        		return $defaultimg;
        	}
        	//如果是http请求就返回http的请求链接
        	if(strstr($book_cover, 'http') || strstr($book_cover, 'https')){
        		$bookUrl = $book_cover;
        	}else{
        		//使用默认的半路径进行拼接
        		$bookUrl = sprintf("%s%s",$referer,$book_cover);
        	}
        	return $bookUrl;
        }else{
        	echo "last_site_id or last_crawler_book_id is empty \r\n";
        }
        return $bookInfo;
    }

     /**
     * @note 获取小说详情记录
     *
     * @param $story_id string  第三方网站的id
     * @param $source string 来源 paoshu8：泡书吧 xsw：台湾小说
     * @return unnkower
     */
    public static function getBqgNovelInfoById($story_id = '', $source = '', $field = 'store_id,title,author,note')
    {
        if (!$story_id || !$source) {
            return false;
        }
        $sql = "select {$field} from " . self::$collect_table_name . " where story_id='{$story_id}' and source='{$source}'";
        global $mysql_obj;
        $info = $mysql_obj->fetch($sql, 'db_master');
        return !empty($info) ? $info : [];
    }


    /**
	* @note 关联搜索笔趣阁的相关书籍信息
	* @param  $book_name string 小说ID
	* @param $author string 作者
	* @param $field string 指定字段 
	* @return array
	*/
    public static function getBiqugeBookInfo($book_name = '', $author = '', $field = 'store_id,title,author,note')
    {
        if (!$book_name || !$author) {
            return false;
        }
        $sql = "select {$field} from " . self::$collect_table_name . " where title='{$book_name}' and author='{$author}'";
        global $mysql_obj;
        $info = $mysql_obj->fetch($sql, 'db_master');
        return !empty($info) ? $info : [];
    }

	/**
	* @note 获取线上笔趣阁的采集的基础信息
	* @param  $store_id int 
	* @return array
	*/
	public static function getDataById($store_id = 0){
		if(!$store_id){
			return false;
		}
		global $mysql_obj;
		$sql = "select * from ".self::$collect_table_name." where store_id = {$store_id}";
		$info = $mysql_obj->fetch($sql,self::$db_collect_conn);
		return $info ?? [];
	}

	/**
	* @note 获取笔趣阁的线上采集小说ID
	* @param  $source_url int 
	* @return array
	*/
	public static function getBiqugeBookId($source_url =''){
		if(!$source_url){
			return false;
		}
		$urlData = parse_url($source_url);
		$bookId = 0;
		if(isset($urlData['path'])){
			$urlArr = explode('/',$urlData['path']);
			$endString = end($urlArr);
			$bookId = str_replace('.html','',$endString);
			$bookId = intval($bookId);
		}
		return $bookId;		
	}

	/**
	* @note 选择系统默认的一个源
	* @param  $data array 源列表 
	* @param $last_site_id string 最后的更新源
	* @param type force:强制使用last_site的站点信息 select：选择性更新
	* @return path，返回源对应的url信息
	*/
	public static function getUseHotSource($data=[],$last_site_id='',$type='force'){
		if(!$data){
			return false;
		}
		$last_site_id = trim($last_site_id);
		$maxValue = null;
		$maxItem = null;
		$choooeList= [];
		//获取当前的书源
		$sourceList = $data['data'] ?? [];
		if(!$sourceList){
			return [];
		}
		$count = count($sourceList);
	
		    
		//切换源选择
		foreach($sourceList as $val){
			if(!$val || !$val['site_id']) continue;
			//处理百分比方便下面自动筛选源
			$choooe= str_replace('%','',$val['choose']);
			$choooe = floatval($choooe);
			if(!$choooe){
				$choooe = 0;
			}
			$val['choooe1'] = $choooe;
			//强制使用库里指定的判断如果存在并且是强制使用的
			if($last_site_id && $type == 'force'){
				if($last_site_id && $val['site_id'] == $last_site_id){
					//默认用网站的更新源进行替换
					 $info= $val;
					 $choooeList[] = $info;
					 break;
				}
			}else{
				//默认使用随机推荐的
				//随机进行选择
				if($count>1 && in_array($val['site_id'],['wsg'])){
					continue;
				}
				$choooeList[] = $val;
			}
		}
		$maxChooseEntry = null;
		$maxChooseValue = 0;

		//比对最大值拿出来选择人数最多的源出来
		foreach ($choooeList as $item) {
			// //大于等于也算
		    // if ($maxValue === null || $item['choooe1'] = $maxValue) {
		    //     $maxValue = $item['choooe1'];
		    //     $maxItem = $item;
		       
		    // }
		    // $chooseValue = $item['chapter_count'];//按照章节总数判断
		    // $chooseValue = $item['choooe1']; //选择率
		    $chapter_count = $item['chapter_count'] ?? 0; //章节总数计算
		        
		    if ($chapter_count > $maxChooseValue) {
		        $maxChooseValue = $chapter_count;
		        $maxChooseEntry = $item;
		    }
		}
		if ($maxChooseEntry !== null) {
		    echo "最大的 chapter_count 值是: " . $maxChooseValue . "%";
		     // echo "最大的 choooe1 的章节目录总数值是: " . $maxChooseValue ;
		    echo "\n对应的数据: ";
		    print_r($maxChooseEntry);
		    
		}
		return $maxChooseEntry;
	}

	/**
	* @note 清洗过滤一些不关紧要的笔趣阁的数据信息
	* @param  $info_data array 基础字段信息
	* @param $siter_path string 最后一次采集源的地址
	* @param $story_link string 采集地址
	* @param $site_id string 小说源标识
	* @return array
	*/
	    
	public static function initBqgStoreInfo($site_path,$story_link,$info_data=[],$site_id){
		if(!$info_data){
			return [];
		}
		
		    
		$store_data['site_path'] = $site_path; //源的地址
		$store_data['title'] = trimBlankSpace($info_data['name']) ?? '';//书名
		$store_data['author'] = trimBlankSpace($info_data['author']) ?? '';//作者
		$store_data['story_link'] = $story_link;
		$store_data['cate_name'] = $info_data['ltype'] ?? '';//书籍分类，对应笔趣阁大类
		
		$last_chapter_name =  BiqugeModel::stringHandleInput($info_data['last_chapter_name']); //处理转义字符
		$store_data['nearby_chapter'] = $last_chapter_name; //最近更新的章节
		$store_data['tag'] = $info_data['ltype'];
		$store_data['story_id'] = $info_data['book_id'] ?? 0; //第三方的ID
		$intro = addslashes($info_data['remark']);//转义 特殊字符
        // $intro = cut_str($intro,200); //切割字符串
        $intro = trimBlankSpace($intro);
		$serialize = $info_data['status'] ?? 0 ;//连载状态
		    
		$cover_logo  = $info_data['image'] ??'';//小说封面
		$updateAt = $info_data['updated_at'] ?? '';//第三方的更新时间
		$updateTime = 0;
		//获取笔趣阁的ID 反查对应的封面
		$bookId = intval($info_data['book_id']);//获取对应的书籍ID
		if(strtotime($updateAt)>0){
			$updateTime = strtotime($updateAt);
		}
		
		$defaultimg = Env::get('APICONFIG.DEFAULT_PIC');
		$picReferer = Env::get('BQG.PIC_URL');
		//拼接图片路径		
		$image =$picReferer.$cover_logo;
		    
		//检查图片的状态是否为404
		if(!@check_url($image)){
			echo "cover_logo = {$image}\t图片资源不存在重新请求\r\n";
			//从数据库再发反查一遍
			$image = self::getCoverInfoByDataBase($bookId,$site_id,$picReferer);
			//如果从库里查询的也不存在
			if(!@check_url($image)){
				//从跑书吧在同步一次同步一次图片
				$image = self::getCoverLogoByPaoshu8($info_data['name'],$info_data['author']);
				if(!$image){//如果三个地方都找不到图片资源，就给一个默认的把
					$image= $defaultimg; //如果最终还是为空就给默认
				}
			}
		}
		        

		// BookIsfinished = 1 //完本
		// BookUnfinished = 2 完本
		//这个有点坑爹，他这个里面1 和2 都是完结
		if($serialize == 1){
			$serializeText = '已经完本';
		}else {
			$serializeText = '连载中';
		}
		$store_data['cover_logo'] = $image; //封面处理
		$store_data['status'] = $serializeText;//连载状态
		$store_data['third_update_time'] = $updateTime;
		$store_data['intro'] = $intro;//简介   
		return $store_data;
	}


	/**
	* @note 获取笔趣阁的章节类目
	* @param  $site_path array 获取基础数据信息
	* @param $story_link string 采集地址
	* @param $bookId int 笔趣阁小说ID
	* @return array
	*/
	public static function getBiqugeChapterList($bookId= 0,$site_path=''){
		$site_path = trim($site_path);
		$bookId = intval($bookId);
		if(!$bookId || !$site_path){
			return false;
		}
		$chapter_url = Env::get('BQG.CHAPTER_URL');
		//设置redis的对应的key		
		$redisKey = sprintf("biquge_book_id_%s:%s",$bookId,md5($site_path));
		global $redis_data;
		$lists  =[];
		$redis_data->del_redis($redisKey);
		// echo 33;exit;
		//这里缓存章节目录15分钟，方便存取 ，以site_path的md5来进行加解密
		$cacheData = $redis_data->get_redis($redisKey);
		if(!$cacheData){
			echo "【我是新的缓存的章节数据*******】chapter_md5：{$redisKey}\t缓存时间：".self::$timeout."\r\n";
		    //获取章节目录并缓存
			$chapterList = BiqugeService::getBqgChapterList($site_path,$chapter_url);
			    
			$redis_data->set_redis($redisKey,json_encode($chapterList,JSON_UNESCAPED_UNICODE),self::$timeout);
			$lists = $chapterList;
			unset($chapterList);
		}else{
			echo "我是缓存 key ={$redisKey} 里的章节数据呀\r\n";
			$lists = json_decode($cacheData,true);
		}
		    
		

		    
		$items= [];
		//汇总章节目录数据
		foreach($lists as $val){
			//如果章节名称为空，则不统计
			if(empty($val['name'])){
			    continue;
			}
				    
			//这里用APi接口的is_content判断是否采集的有内容，false为无内容，提示后续更新
			if($val['is_content'] == false ){
				 if($linkPath == '/' || $linkPath ==''){
				 	 //错误的返回没有生产呢给一个内容
				 	$returnPath =sprintf("%s%s.html",$chapter_url,generateRandomString(10));
				 }
			}else{
				//如果是以： url =  https://chapter.chuangke.tv/ai/474/8e/ef/563/15710.html 这种类型的链接结束的，优先使用这个url的链接，说明是正确的，不然用path里的拼接好的返回同步
				$path= $val['path'] ?? ''; //拼装后的路径url
				$url = $val['url'] ?? ''; //原始路径的url信息
				if(strstr($url, 'http') || strstr($url, 'https')  ){
					//这里面需要再判断下是否包含html
					// if(!strstr($url,'.html')){
					// 	$returnPath = $path; //不存在html的时候用path
					// }else{
					// 	$returnPath = $url;//存在.html的时候的时候用path
					// }
					$returnPath = $path;
				}else{
					//非http返回用path进行组装返回
					$returnPath = $path; //用path路径里的
				}
			}   
			$info['link_name'] = $val['name'];
			$info['link_url'] = $returnPath;
			$info['createtime'] = time();
			$items[] = $info;
		}
		    
		    
		    
		return $items;
	}

		/**
	 * @note 创建生成本地的json文件
	 *
	 * @param $store_data array 小说的基础信息
	 * @param $chapterList array 章节列表
	 * @return array
	 */
	public static function createDataJson($store_data=[],$chapterList=[]){
		if (!$store_data || !$chapterList) {
			return false;
		}
		
		$book_name = trimBlankSpace($store_data['title']);//标题
		$author = trimBlankSpace($store_data['author']);//作者        

		    
		//获取标题+文字的md5串
		$md5_str = NovelModel::getAuthorFoleder($book_name, $author);
		    
		if (!$md5_str) {
			return false;
		}
		$json_list = [];
		foreach ($chapterList as $key => $val) {
			$link_url = $val['link_url'] ?? '';
			//配置指定的json地址信息
			$json_list[] = [
				'id'    => $key + 1, //ID
				'sort'  => $key + 1,   //排序
				'chapter_link'  => $link_url, //抓取的地址信息
				'chapter_name'  => $val['link_name'], //文件名称
				'vip'   =>  0, //是否为VIP
				'cion'  =>  0, //章节标题
				'is_first' =>   0, //是否为首页信息
				'is_last'   => 0, //是否为最后一个
				'text_num'  => rand(3000, 10000), //随机生成文本字数统计
				'addtime'   => (int) $val['createtime'], //添加时间
			];
		}

		//保存的	的具体路径信息    
		$save_path = Env::get('SAVE_JSON_PATH') . DS . substr($md5_str, 0, 2); //保存json的路径
		    
		//获取对应的json目录信息
		if (!is_dir($save_path)) {
			createFolders($save_path);
		}
	
		$filename = NovelModel::getBookFilePath($book_name, $author);
		echo $filename."\r\n";    
		// $filename = $save_path . DS . $md5_str . '.' . self::$json_file_type;
		//保存对应的数据到文件中方便后期读取
		$json_data = json_encode($json_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		writeFileCombine($filename, $json_data); //把json信息存储为对应的目录中去
		return $json_list;
	}


	 /**
	* @note  更新状态小说同步状态信息
	* @param string $story_id 小说ID
	* @param string $note 备注说明
	* @return string
	*/
    public static function  updateSynStatusData($store_id= 0,$pro_book_id=0,$note=""){
        if(!$store_id){
            return false;
        }
        global $mysql_obj;
        $where_condition = "store_id = '".$store_id."'";
        $update_data['is_async'] = 1; //设置默认已同步
        if($pro_book_id){
        	$update_data['pro_book_id'] = $pro_book_id;
        }
        if($note){
            $update_data['note'] = $note;
        }
        $update_data['updatetime'] = time();//记录更新的时间
        //对比新旧数据返回最新的更新
        $mysql_obj->update_data($update_data,$where_condition,self::$collect_table_name);
        return true;
    }


    /**
	* @note 跟新当前的同步本地数据信息
	* @param  $store_data int 老数据
	* @param $mysql_obj object 对象信息
	* @param $collect_data array 采集的数据
	* @param $updater_status bool 是否更新状态 true:更新 false:更新内容
	* @param $pro_book_id int 线上小说ID
	* @param $note stirng 更新日志
	* @return bool
	*/
    public static function saveImsBookInfo($mysql_obj,$store_data,$collect_data,$update_status = false,$store_id,$pro_book_id =0,$note=''){
    	if(!$store_data || !$collect_data || !$store_id){
    		return false;
    	}
    	//比对需要更新的数据
    	$diff_data = NovelModel::arrayDiffFiled($store_data,$collect_data);
    	if($update_status){
    		$diff_data['is_async'] = 1; //更新变更的数据信息
    	}
    	//关联线上的更新状态信息
    	if($pro_book_id){
    		$diff_data['pro_book_id'] = $pro_book_id;
    	}
    	if($note){
    		$diff_data['note'] = $note;
    	}
    	//更新对应的状态
        if(!empty($diff_data)){
            $diff_data['updatetime'] = time();
			$condition = "store_id = ".intval($store_id)."";  
            $mysql_obj->update_data($diff_data,$condition,self::$collect_table_name);
        }
        return true;
    }

	/**
	* @note 同步拉取主目录数据信息
	* @param  $site_path int 站点信息
	* @param $store_data array 小说基本信息
	* @param $store-id int 本地的关联业务ID
	* @return array
	*/
	public static function synCHapterInfo($store_id,$site_path,$store_data=[],$oldData=[]){
		if(!$site_path || !$store_data || !$oldData){
			return false;
		}
		//老的用户ID
		$old_pro_book_id = $oldData['pro_book_id'] ?? 0;
		$info = $store_data; //方便下面计算
		$mysql_obj = self::getMyqlObj();//初始化连接句柄
		$md5_str= NovelModel::getAuthorFoleder($info['title'] ,$info['author']);
		if(!$md5_str){
		 	return false;
		}
		$download_path =Env::get('SAVE_NOVEL_PATH') .DS . $md5_str;//下载路径;   
	  	if(!is_dir($download_path)){
            createFolders($download_path);
        }
        //记录文件的格式
        $file_name =NovelModel::getBookFilePath($info['title'],$info['author']);
        echo "json_path = {$file_name}\r\n";
        $json_data = readFileData($file_name);
        if(empty($json_data)) {
            echo "当前小说未生成json文件\r\n";
            printlog('当前ID:'.$pro_book_id.'暂未生成json文件');
            NovelModel::killMasterProcess();//退出主程序
            return false;
        }
        $chapter_item = json_decode($json_data,true);
        if(!$chapter_item){
        	echo "暂无关联章节目录\r\n";
            NovelModel::killMasterProcess();//退出主程序
            exit(1);
        }
            
         //去掉收尾空格处理--误删
        foreach($chapter_item as &$v){
            $v['chapter_name'] = trim($v['chapter_name']);
        }
            


        // echo "<pre>";
        // var_dump($chapter_item);
        // echo "</pre>";
        // exit();
            
       
           
        echo "JSON文件里的总章节总数：".count($chapter_item).PHP_EOL;
        $dataList = [];

        $sucNum = 0;
        foreach($chapter_item as &$val){
            $filename =$download_path .DS . md5($val['chapter_name']).'.'.NovelModel::$file_type;
            $val['file_path'] = $filename;
            $content = readFileData($filename);
            //判断是否需要重新抓取，的一个原则 ,判断文件为空或者是有一些特殊的写入等
            if(
            	!$content 
            	|| !file_exists($filename)
            	||$content =='从远端拉取内容失败，有可能是对方服务器响应超时，后续待更新'  
            	|| strstr($content,'暂无相关章节信息')
            	|| strstr($content, '未完待续...') 
            	 )
            {
                $chapter_link = $val['chapter_link'] ?? '';
                //调过这一段内的指定点的链接
                $val['link_url'] = $chapter_link;
                $dataList[] =   $val;
            }else{
                $sucNum++;
            }
        }		
           
       if(!$dataList){
       		//同步线上数据
       		$pro_book_id = NovelModel::exchange_book_handle($info ,$mysql_obj);
       		//同步更新线上的更新状态信息
       		$notice_msg = "已经爬取完毕 ，不需要重复操作了 -- pro_book_id = {$pro_book_id}";
       		self::updateSynStatusData($store_id,$pro_book_id,$notice_msg);
            NovelModel::killMasterProcess();//退出主程序
            exit("*********************************title = {$info['title']} \t author = {$info['author']} \tstore_id = {$store_id}\t pro_book_id ={$old_pro_book_id} \t{$notice_msg}\r\n");
       }
           
           
       	echo "\r\n\r\n";
        echo "共需要补的章节总数量： num = ".count($dataList)."\r\n";
		$dataList = NovelModel::changeChapterInfo($dataList);
		$chapterAes = new ChapterAes();//实例化对象加密对象

		    
		$limit_size =200; //默认为200
		    
		// echo "<pre>";
		// var_dump($dataList);
		// echo "</pre>";
		// exit();
		    
		$items = array_chunk($dataList,$limit_size); //默认每一页300个请求，到详情页最多300*3=900个URL 这个是因为移动端的原因造成
		$i_num = 0;
		$count_page= count($items); //总分页数
		echo "总分页总数：".$count_page." \t 每页步长数：$limit_size\n";
		foreach($items as $k =>&$v){
			//拉取笔趣阁的列表信息
			$html_data = self::pullBiqugeChapterList($v,$download_path,$info['title'] , $chapterAes);
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
                self::synLocalDataFile($download_path,$html_data);
                echo "\r\n|||||||||||||||| this current page =  (".($k+1)."/{$count_page})\t store_id = {$store_id} \tcomplate \r\n\r\n";
                // echo "休息一秒后再继续运行----------------\r\n";
                sleep(1);//休息三秒不要立马去请求，防止空数据的发生
            }else{
                echo "num：{$a_num} 未获取到数据，有可能是代理过期\r\n";
            }			    
		}
		$pro_book_id1 = NovelModel::exchange_book_handle($info ,$mysql_obj);
		echo "同步后的pro_book_id = {$pro_book_id1} \r\n";
		//同步接口数据
		// self::updateSynStatusData($store_id,$pro_book_id1);//更新对应的状态，暂时不用，根据是否爬取完毕判断
		//同步本地数据变更
		self::saveImsBookInfo($mysql_obj,$oldData,$info,true,$store_id,$pro_book_id1,"小说ID= {$store_id} 采集完毕"); //同步本地数据变更
		return true;
	}


	/**
	* @note 获取相关的mysql链接句柄
	* @return object
	*/
	private static function getMyqlObj(){
		global $mysql_obj;
		$obj = $mysql_obj;
		return $obj;
	}

	    /**
     * @note  保存本都得文件信息缓存起来
     * @param string $save_path 保存路径
     * @param array $data 读取出来的数据
     * @return string
     */
    public  static function synLocalDataFile($save_path,$data){
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


    /**
	* @note 对于错误的数据轮训重新请求伦旭接口
	* @param  $urls arry 采集的列表
	* @param $chapterAes object 解密类对象
	* @return array
	*/
    public static function callRequestsAPI($contents_arr=[],$goods_list=[],$method='get',$field_key= 'mobile_url'){
    	if(!$contents_arr || !$goods_list){
    		return false;
    	}
    	    
    	$errData  =  $sucData  = [];
    	foreach($contents_arr as $key => $val){
    		//这里不考虑404的情况，404跳出去给一个默认的页面提示就可以了
	        if(empty($val) || $val == '' || strstr($val, '云安全平台检测') ||  strstr($val, 'AccessDenied')){//空数据返回
	            $errData[] =$goods_list[$key] ?? [];
	        }else{//正常的数据返回
	             $sucData[$key] = $val;
	        } 
        }
        $repeat_data = $curl_contents1 =[];
         //数据为空的情况判断
        if(!empty($errData)){
            echo "有返回为空或者异常数据的数据请求，会重新去进行请求返回111\r\n";
            $successNum = 0;

            $old_num = count($errData);
            $urls = array_column($errData, $field_key); //进来先取出来,根据上面的取出来
            echo "待需要重新抓取的处理的url数据:\r\n";
            echo '<pre>';
            var_dump($urls);
            echo '</pre>';
            while(true){
                //通过说swoole来完成并发请求，采用协程
                $curl_contents1 = StoreModel::swooleRquest($urls,$method);
                $temp_url =[];//设置中间变量
                if(!$curl_contents1){
                    $curl_contents1 = [];
                }
                $len = 50 ;//至少要保持页面有数据，防止数据为空
                foreach($curl_contents1 as $tkey=> $tval){
                    if(empty($tval) || $tval == '' || strstr($tval, '云安全平台检测')){//为空的情况
                        //当前需要抓取的url连接诶
                        $t_url = $goods_list[$tkey][$field_key] ?? '';
                        echo "获取数据为空，会重新抓取====================== {$t_url}\r\n";
                        $temp_url[] = $t_url; //取出来当前的连接
                    }else{//正常的返回
                        $repeat_data[$tkey] = $tval;
                        unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
                        unset($curl_contents1[$tkey]);
                        $successNum++;
                    }
                }
                $urls = $temp_url; //起到指针的作用，每次只存失败的连接
                $urls = array_values($urls);//重置键值，方便查找
                if($old_num == $successNum){
                    echo "数据清洗完毕等待入库\r\n";
                    break;
                }
            }
        }
        echo "success-num =".count($sucData).PHP_EOL;
        echo "repeat_data-num = ".count($repeat_data).PHP_EOL;
        $returnList = array_merge($sucData , $repeat_data);
        echo "all-return-num = ".count($returnList).PHP_EOL;
        if(count($sucData) +count($repeat_data) != count($returnList) ){
            echo "数组返回数量不对，请检车代码逻辑";
            exit();
        }
        return $returnList;
    }

	/**
	* @note 获取所有笔趣阁的抓取流程数据
	* @param  $urls arry 采集的列表
	* @param $txt_path 文本路径信息
	* @param $title string 标题信息
	* @param $chapterAes object 解密类对象
	* @return array
	*/
	    
	public static function pullBiqugeChapterList($urls = [],$txt_path,$title='',$chapterAes){
		 if(!$urls){
            return false;
        }
            
        $referer_url = "";
        foreach($urls as $val){
            $mobilePath = $val['link_url'] ?? '';
            $referer_url = NovelModel::urlHostData($val['chapter_link']);
            $pathRet = parse_url($val['chapter_link']);
            $path= $pathRet['path'];
            $chapterList[$mobilePath] = [
                //拼装移动端的地址
                'save_path'  =>  $txt_path . DS . md5($val['link_name']) . '.' . NovelModel::$file_type,
                'chapter_name'  =>  $val['chapter_name'],
                'chapter_link'  =>  $val['chapter_link'],
                'mobile_url'  => $val['chapter_link'], //兼容老数据
                'chapter_mobile_link' => $val['chapter_link'],
            ];
            $t_url[] = $val['chapter_link'];
        }

        // echo "<pre>";
        // var_dump($t_url);
        // echo "</pre>";
        // exit();
            
            
        //异步协程请求章节目录列表
        $returnList = BiqugeRequestModel::swooleRequest($t_url,self::$method);
            
        //重复调用防止采集为空，增加重试机制，防止数据漏章
       	$returnList = BiqugeModel::callRequestsAPI($returnList, $chapterList);
       	    
        //异常判断
        if(!$returnList){
            return [];
        }
        $response_content = [];
       
       //处理加解密
        $store_c = [];
        if(!empty($returnList)){
            foreach($returnList as $gk =>$gv){
            	$storeData = json_decode($gv,true);
            	if(isset($storeData['code']) && $storeData['code'] == 1){
            		$html = $storeData['data']['content'] ?? ''; //获取对应的抓取的内容
            		if($html){
            			//执行解密程序，会根据对应的信息进行自动转换
            			$content = $chapterAes->pswDecryptString($html);
            			 $store_c[$gk] = $content;
            		}else{
            			$store_c[$gk] = '暂无相关章节信息，请浏览其他书籍哦，稍后更新查看~';
            		}
            	}else{
            		$store_c[$gk] = '暂无相关章节信息，请浏览其他书籍哦，稍后更新查看~';
            	}
            }
        }
            
            
        foreach($chapterList as $key =>$val){
        	$store_content = "";
            if(isset($store_c[$key])){
                $store_content = $store_c[$key];
            }
            $chapterList[$key]['content'] = $store_content;
        }
        return $chapterList;
	}


	/**
	* @note 处理笔趣阁图片保存问题，需要挂一个代理
	* @param  $title string 小说名
	* @param $cover_logo string 封面
	* @param $author string 作者
	* @return array
	*/
	public static function saveBiqugeBookImage($cover_logo='', $title = '', $author = ''){
		if(!$cover_logo){
			return false;
		}
		    
		$imgFileName = NovelModel::getFirstImgePath($title, $author, $cover_logo);
		$save_img_path = Env::get('SAVE_IMG_PATH');
		$monthDay = date('Ym');
		$filename = $save_img_path . DS . $monthDay . DS . $imgFileName;
		//判断文件是否存在，如果不存在就直接保存到本地
		$save_img_path = Env::get('SAVE_IMG_PATH');
		if (!is_dir($save_img_path)) {
			createFolders($save_img_path);
		}
   
   	    
		//图片不存在写入
		if(!file_exists($filename) || !@getimagesize($filename)){
			//这个次方可以做一个三次轮训，如果三次都没有获取到结果那就直接退出
			// $res = curlContetnsByProxy($cover_logo,BiqugeRequestModel::getUsingProxy());
			$res = webRequest($cover_logo,'GET');
			if(!$res || $res == ""){
				//重试三次，如果还是有问题就退出
				$num = 0;
				while(true){
					$num++;
					echo "重试加载图片 url = {$cover_logo}\t请求次数num = {$num}\r\n";
					//三次之后直接退出
					if($num >3){
						break;
					}else{
						//如果获取到图片了就直接退出
						$res = webRequest($cover_logo,'GET');
						// $res = curlContetnsByProxy($cover_logo,BiqugeRequestModel::getUsingProxy());
						if($res){
							break;
						}
					}
				}
			}		
			$img_con = $res ?? '';
			writeFileCombine($filename, $img_con);	
		}
			    
		return $filename;
	}



	/**
	* @note 同步本地存储的记录关联数据
	* @param  $cateBook int 书籍ID
	* @return array
	*/
	    
	public static function synBookToLocalTable($cateBook=[]){
		if(!$cateBook){
			return false;
		}
		global $mysql_obj;
		    
		$novel_table_name =self::$collect_table_name; //采集的主要表
		$db_conn = self::$db_collect_conn; //默认的链接句柄
		if($cateBook){
			$novelList = [];
			$source = BiqugeModel::$source_ref;
			foreach($cateBook as $key =>$val){
				$book_id = intval($val['book_id']);
				if(!$book_id){
					continue;
				}

				$site_id = $val['site_id'] ?? 0;
						    
				//获取第一个对应的对应的key
				$string_key = getFirstThreeDigits($book_id);
				$source_url = sprintf("%ssource/%s/%s.html",Env::get('BQG.BASE_URL'),$string_key, $book_id);
				$info['title'] = trimBlankSpace($val['name']);//小说名称
				$info['author'] = trimBlankSpace($val['author']);//小说作者
				$info['is_async'] = 0;//是否同步状态
				$info['source'] = $source; //来源
				$info['cate_name'] =trimBlankSpace($val['ltype']);
				$info['createtime'] = time(); //时间
				$info['story_link'] = $source_url;
				$info['source'] = $source;
				$info['story_id'] = $book_id;
				if($site_id){//站点的更新时间
					$info['site_id'] = $site_id;
				}
				    
				//判断pro_book_id是否为空
				if(isset($val['pro_book_id'])){
					$info['pro_book_id'] = $val['pro_book_id'];
				}
				    
				$novelList[] =  $info;
			}

			$insertData = [];
			$num = $i_num = 0;    
			foreach ($novelList as $key => $val) {
			    $num++;
			    $source =$val['source'] ?? '';
			    $val['tag'] = $val['cate_name'] ?? '';
			    //查询是否存在次本小说
			    // $storyInfo = BiqugeModel::getBqgNovelInfoById($val['story_id'], $source);
			    //查笔趣阁的关联数据
			    $storyInfo = BiqugeModel::getBiqugeBookInfo($val['title'],$val['author']);
			    if (empty($storyInfo)) {

			        $val['title'] = trimBlankSpace($val['title']);//小说名称
			        $val['author'] = $val['author'] ? trimBlankSpace($val['author']) : '未知';//小说作何
			        $val['is_async'] = 0;//是否同步状态
			        $val['source'] = $source; //来源
			        $val['createtime'] = time(); //时间
			        $insertData[] = $val;
			        echo "num = {$num} \t book_id = {$val['story_id']}\tstore_id = {$storyInfo['store_id']}\ttitle={$val['title']}\t  author = {$val['author']} is to insert this data\r\n";
			    } else {
			    	    
			        $i_num++;
			        //更新对应的状态信息，需要改成is_async 0,方便进行同步
			        echo "num = {$num} exists \tbook_id={$val['story_id']}\tstore_id = {$storyInfo['store_id']} \t title={$storyInfo['title']}\t author = {$storyInfo['author']} ，status is update,next to run\r\n";
			       $note = $storyInfo['note'] ??'';
			       if(!empty($note)){
			            $note = '';
			       }
			       $sql= "update {$novel_table_name} set is_async = 0,note ='请求导入importBIqugeTodayAll脚本呢，重新执行' where store_id = {$storyInfo['store_id']} limit 1";
			       $mysql_obj->query($sql,$db_conn);
			    }
			}    
			echo "------------共拉取下来的的小说有". count($novelList)."本 \r\n";
			echo "===========实际待需要插入的小说有 " . count($insertData) . "本，会自动同步\r\n";
			echo "******************已存在的小说有 {$i_num}本，状态会自动处理为待同步\r\n";
			if ($insertData) {
			    //同步数据
			    $ret = $mysql_obj->add_data($insertData, $novel_table_name, $db_conn);
			    if (!$ret) {
				        echo "数据库数据同步失败\r\n";
				    }
					    echo "同步小说列表成功 \r\n";
				} else {
				    echo "暂无小说需要插入的数据同步\r\n";
				}
		}else{
			echo "no data\r\n";
		}
	}

}
?>