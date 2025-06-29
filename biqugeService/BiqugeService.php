<?php
/*
 * 服务层主要处理一些通用的配置设置信息，获取配置路由转发等 
 *
 * Copyright (c) 2017 - 真一网络
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    
class BiqugeService{


	public static $baseUrl = null; //基础的搜索信息
	public static $searchUrl = null; //搜索的具体方法
	public static $chapterUrl = null; //章节的默认url

	private static $method_get = 'GET'; //默认为get方式请求
	private static $method_post = 'POST';//设置post方式

	private static $base_key = 'base'; //基础接口的key
	private static $search_key = 'search'; //搜索接口的key
	private static $chapter_key = 'chapter'; //章节内容接口的key
	private static $catalog_key = 'catalog';//章节目录的接口key
	private static $package = 'com.bqkkxb2023.read.tt'; //搜索默认的package_name
	private static $form = 1;//1 小说 2.、听书

	/**
	* @note 获取每日的新书推荐
	* @param $page int 页码
	* @return array
	*/
	public static function getHostNewBookeList($page = 1){
		$baseUrl = 'https://book.ycukhv.com/';
		$indexItem= [];
		$url =  sprintf("https://book.ycukhv.com/book_city/v7_more/index/2/6/3/%s.html",$page);
		echo "index-today-update-url = {$url}\r\n";
		$res = webRequest($url,self::$method_get);

		$indexList = json_decode($res,true);
		if($indexList['code'] == 1){
			$content = $indexList['data']['content'] ?? '';
			if(!$content){
				return []; //返回空
			}
			$param['content'] = $content;
			    
			$bookList = self::decryptContent($param); 
			if($bookList){
				$lists = $bookList['data']['detail'] ?? '';
				$indexItem = $lists ? json_decode($lists,true) : [];
			}
		}
		return $indexItem;
	}

	/**
	* @note 获取榜单配置
	* @return array
	*/
	public  static function getRankConfig(){
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%srank_lists/index.html",$baseUrl);
		echo "rank-config-url :{$url}\r\n";
		$res = webRequest($url,self::$method_get);
		return $res ? json_decode($res,true) : [];
	}

	/**
	* @note 通过分类获取榜单下的分类数据信息
	* @param $page_id string 榜单ID
	* @return array
	*/
	public static function getRankListByPageId($page_id =''){
		if(!$page_id){
			return false;
		}
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%srank_lists/details/%s.html",$baseUrl,$page_id);
		echo "book-rank-list url = {$url}\r\n";
		$result = webRequest($url,self::$method_get);
		$rankList = [];
		if($result){
			$data = json_decode($result,true);
			if(isset($data['data']) && !empty($data['data'])){
			 	$rankList  = $data['data'] ?? [];
			}
		}
		return $rankList;
	}


	/**
	* @note 默认拉取最近更新的书源
	* @param $sex int 性别
	* @param $page int 页码
	* @return array
	*/
	    
	public static function todayUpdateBooks($sex = 1,$page = 1){
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%sbook_city/v7_more/index/%s/2/0/%s.html",$baseUrl,$sex,$page);
		echo "today-update-url :{$url}\r\n";
		$res = webRequest($url,self::$method_get);
		$cateList = json_decode($res , true);
		$content = $cateList['data']['content'] ?? '';
		$bookList  = [];
		if($content){
			 $param['content'] = $content; //传入的解码对象信息
			 $bookList = self::decryptContent($param); 
		}
		return $bookList;
	}


	/**
	* @note 选择配置的的url信息
	* @param  $book_id int 书籍id 
	* @return array
	*/
	private static function selectedHost($key ){
		if(!$key){
			return false;
		}
		switch ($key) {
			case 'base':
				return Env::get('BQG.BASE_URL'); //书籍的相关接口
				break;
			case 'search':
				return Env::get('BQG.SEARCH_URL'); //搜索相关
				break;
			case 'chapter':
				return Env::get('BQG.CHAPTER_URL'); //处理章节内容
				break;
			case 'catalog':
				return Env::get('BQG.CATALOG_URL'); //处理章节目录
				break;
		}
	}

	/**
	* @note 获取默认分类
	* @param $sex 1 男 2：女
	* @param $cate_id int分类
	* @param $page 1 页码
	* @return array
	*/
	public static function getBookyByCategory($sex= 1,$cate_id  =0,$page = 1){
		if(!$sex){
			return false;
		}
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%sclassify/all/%s/%s/0/-1/%s.html",$baseUrl , $sex , $cate_id , $page);
		$bookList = webRequest($url , self::$method_get);
		$data  = json_decode($bookList, true);
		$categoryBook = [];
		if(isset($data['data']) & $data['code'] == 1){
		    $categoryBook = $data['data'] ?? [];
		// if(isset($bookList['data'])$bookList[''])
		}
		return $categoryBook['lists'] ?? [];
	}

	/**
	* @note 获取类型首页
	* @return array
	*/
	public static function getCategoryIndex(){
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%sclassify/v4/index.html",$baseUrl);
		$cateInfo = file_get_contents($url);
		$category  = [];
		$cateList = json_decode($cateInfo , true);
		if($cateList){
			$content  = $cateList['data']['content'] ?? '';
			if($content){
			    $param['content'] = $content; //传入的解码对象信息
			    $res = self::decryptContent($param); 
			        
			    if($res['code'] == 1){
			        $ret = $res['data']['detail'] ??'';
			        $category = $ret ?  json_decode($ret,true) : [];
				}
			}
		}
		return $category;
		    
	}


	/**
	* @note 根据关键字搜索书籍
	* @param  $book_id int 书籍id 
	* @return array
	*/
	public static function getListByKeywords($keyword){
		$keyword = trimBlankSpace($keyword); //去除首尾空格
		if(!$keyword || $keyword == ""){
			return false;
		}
		//base64进行请求数据
		$name = urlencode($keyword);
		//https://s.prod-book.iolty.xyz/v4/2/lists.api?form=1&keyword=%E9%BE%99%E7%8E%8B%E4%BC%A0%E8%AF%B4&package=com.bqkkxb2023.read.tt
		$baseUrl = self::selectedHost(self::$search_key);
		//组装搜索的url
		$url = sprintf("%sv4/2/lists.api?form=%s&keyword=%s&package=%s",$baseUrl,self::$form,$name,self::$package);
		$searchList = file_get_contents($url);
		// $searchList = webRequest($url , self::$method_get);
		if(is_json($searchList)){
			$result = json_decode($searchList , true);
			$ver = $result['data']['ver'] ?? 0;
			$content = $result['data']['content'] ?? '';
			if($content){
				$param['content'] = $content; //传入的解码对象信息
				//请求解码的接口数据信息
				$res = self::decryptContent($param);
				$dataItem = [];
				if($res['code'] == 1){
					 $detail = $res['data']['detail'] ?? ''; //获取解码的数据信息
					 $dataItem = $detail ? json_decode($detail ,true) : [];
				}
				return $dataItem;
			}
		}
		    
	}



	/**
	* @note 按照相关业务数据进行解码
	* @param  $data array 传入的解码数据信息 
	* @return array
	*/
	private static function decryptContent($data,$type=1){
		if(!$data){
			return false;
		}
		$modulePath = '';
		if ($type == 1){
			$modulePath= 'biquge/generalDecrypt';
			//通用解码，包含书籍、搜索、请求详情等
		}else if($type == 2){
			//章节列表解码，直接返回列表信息
			$modulePath = 'biquge/chapterDecrypt';
		}else if($type == 3){
			//内容解码
			$modulePath = 'biquge/contentDecrypt';
		}
		    
		//换砖成JSON对象接收
		$json_data = json_encode($data,JSON_UNESCAPED_UNICODE);
		$header= [
			 'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($json_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
		];
		$decodeUrl = sprintf("%s%s",Env::get('BQG.FOREIGN_URL') , $modulePath);
		//请求解码接口
		$res = webRequest($decodeUrl,self::$method_post,$json_data,$header);
		if($res){
			return json_decode($res,true);
		}
		
		return $res ?? '';
	}

	/**
	* @note 选择书源
	* @param  $book_id int 书籍id 
	* @param $isV4 int启用V4版本查询
	* @return array
	*/
	public static function getBookSource($book_id = 0,$isV4 =false){
		$book_id = intval($book_id);
		if(!$book_id){
			return false;
		}
		$key = getFirstThreeDigits($book_id);
		if(!$key){
			$key = 0;
		}
		//https://book.prod-book.iolty.xyz/source/972/972265.html 
		$baseUrl = self::selectedHost(self::$base_key);
		if($isV4){
			$url  = sprintf("%sbook/source/v4/%s/%s.html" , $baseUrl , $key , $book_id);
		}else{
			$url  = sprintf("%ssource/%s/%s.html" , $baseUrl , $key , $book_id);
		}
		echo "source-api-url = {$url}\r\n";
		$sourceList = webRequest($url,self::$method_get);
		if(is_json($sourceList)){
			$res = json_decode($sourceList ,true);
			if($isV4){//判断是否为V4接口的调用
				$content = $res['data']['content'] ?? '';
				$param['content'] = $content;
				//V4需要提前解密下
				$sourceRet = self::decryptContent($param,1);
				if($sourceRet['code'] == 1){
					$data = $sourceRet['data']['detail'] ?? [];
					$newSource= json_decode($data,true);
					$return['code'] = $res['code'];
					$return['data'] = $newSource;
					return $return;    
				}
				    
			}else{
				return $res;
			}	    
		}
	}

	/**
	* @note 获取基础的书籍信息
	* @param  $book_id int 书籍id 
	* @return array
	*/
	public static function getBaseInfo($book_id){
		$book_id = intval($book_id);
		if(!$book_id){
			return false;
		}
		$key = getFirstThreeDigits($book_id);
		if(!$key){
			$key = 0;
		}
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%sbook/base/v4/%s/%s.html", $baseUrl , $key , $book_id);
		$baseData = webRequest($url,self::$method_get);
		if(is_json($baseData)){
			$ret = json_decode($baseData,true);
			$detailArr= [];
			if($ret['code'] == 1){
				$content = $ret['data']['content'] ?? '';
				$param['content'] = $content; 
				//请求转码接口
				$res = self::decryptContent($param,1);
				if($res['code'] == 1){
				    $detailArr = $res['data']['detail'] ?? '';
				    $return = json_decode($detailArr,true);
				    return $return ?? [];
				}
			}
		}
	}

	/**
	* @note 获取详情的书籍信息
	* @param  $book_id int 书籍id 
	* @return array
	*/
	public static function getDetailInfo($book_id){
		$book_id = intval($book_id);
		if(!$book_id){
			return false;
		}
		//https://book.prod-book.iolty.xyz/book/details/v4/289/289427.html
		$key = getFirstThreeDigits($book_id);
		if(!$key){
			$key = 0;
		}
		$baseUrl = self::selectedHost(self::$base_key);
		$url = sprintf("%sbook/details/v4/%s/%s.html", $baseUrl , $key , $book_id);

		echo "detail-api-url = {$url}\r\n";
		$detailData = webRequest($url,self::$method_get);
		if(is_json($detailData)){
			 $ret = json_decode($detailData,true);
			 $detailArr= [];
			 if($ret['code'] == 1){
			 	//调用解码接口
			 	$content = $ret['data']['content'] ?? '';
				$param['content'] = $content; 
				//请求转码接口
				$res = self::decryptContent($param,1);
				if($res['code'] == 1){
				    $detailArr = $res['data']['detail'] ?? '';
				    $return = json_decode($detailArr,true);
				    return $return ?? [];
				}
			}
		}
	}

	/**
	* @note 获取拉取的章节内容
	* @param  $chapter_path string 章节路径 
	* @return string
	*/
	public static function pullChapterContent($chapter_path=''){
		$chapter_path = trimBlankSpace($chapter_path); //去除首尾空格
		if(!$chapter_path){
			return false;
		}
		//http://chapter.lmmwlkj.com/rz/290/2f/55/493/27974.html
		$baseUrl = self::selectedHost(self::$chapter_key);
		$url = sprintf("%s%s", $baseUrl , $chapter_path);
		$chapterContent = webRequest($url ,self::$method_get);
		$store_content = '';
		if(is_json($chapterContent)){
			$apiData =  json_decode($chapterContent,true);
			if($apiData['code'] == 1){
				$content = $apiData['data']['content'] ?? '';
				$param['content'] = $content; //组装数据
				$res = self::decryptContent($param,3);
				if($res['code'] == 1){
				      $store_content = $res['data']['content'] ?? '';
				}	
			}
		}
		return $store_content;
	}


	/**
	* @note 获取笔趣阁的具体某本书的章节目录
	* @param $path string 章节目录
	* @param $chapter_url string 章节的url
	* @return string
	*/
	public static function getBqgChapterList($path='',$chapter_url=''){
		if(!$path || !$chapter_url){
			return false;
		}

		$param['path'] = trim($path);
		$param['domain_url'] =trim($chapter_url) ;//追加的path的具体路径
		$res = self::decryptContent($param,2);//解锁章节目录
		$chapterList = [];
		if($res['code'] == 1){
			$chapterList = $res['data']['list'] ?? [];
		}
		return $chapterList;
	}


	/**
	* @note 获取书的章节目录
	* @param  $site_id string  采集源标识
	* @param $collect_book_id int 采集的书籍ID
	* @return string
	*/
	// public static function getBookChapterList($site_id = '',$collect_book_id =0){
	// 	if($site_id == '' || empty($collect_book_id)){
	// 		return false;
	// 	}
		
	// 	$baseUrl = self::selectedHost(self::$base_key);
	// 	//请求章节目录的接口
	// 	$apiurl = sprintf("%scollect/chapter/lists/reload/update.api",$baseUrl);
		
	// 	//构造post请求数据
	// 	$param['site_id'] = $site_id ;
	// 	$param['collect_book_id'] = $collect_book_id;

	// 	$json_data = json_encode($param);//使用JSON数据发送
	// 	//设置header
	// 	$header= [
	// 		'Content-Type: application/json; charset=utf-8',
	// 		'Content-Length:' . strlen($json_data),
	// 		'Cache-Control: no-cache',
	// 		'Pragma: no-cache'
	// 	];
	// 	//发送JSON的请求数据
	// 	$data = webRequest($apiurl , self::$method_post , $json_data , $header);
	// 	echo "<pre>";
	// 	var_dump($data);
	// 	echo "</pre>";
	// 	exit();
		          
		    
	// }
}
?>