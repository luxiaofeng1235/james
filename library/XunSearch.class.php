<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2013,真一网络
// 日 期：2024-03-06
// 作　者：卢晓峰
// E-mail :513072539@qq.com
// 文件名：XunSearch.class.php
// 创建时间:下午2:01:55
// 编 码：UTF-8
// 摘 要: 迅搜搜索引擎 ，包含新增、更新、删除、搜索等
// ///////////////////////////////////////////////////

// use XS;
// use XSDocument;


class XunSearch{

	// private static $dbname = 'demo'; //数据库名称
	private static $dbname ='novel'; //具体的路径信息

	private static $addReq = 'addIndex';//添加索引类型
	private static $delReq = 'delIndex';//删除索引类型
	private static $method_post = 'POST';//设置post方式

	private static $specialWores = ['都重','重谁'];//配置特殊的查不到的词进行对应



	/**
	* @note 获取搜索的接口地址
	* @return string
	*/
	    
	public static function getHostUrl(){
		return Env::get('SEARCH.API_URL');
	}

	/**
	* @note 通过Cli的方式来添加索引
	* @param $id int 小说ID
	* @param  $subject string 书名
	* @param  $message string 消息
	* @return array |bool
	*/
	public static function addDocumentCli($id,$subject ,$message){
		if(!$id || !$subject || !$message){
			return false;
		}
		$data = [
			'req'	=>	self::$addReq,//添加类型
		    'book_id' => $id, // 此字段为主键，必须指定
		    'book_name' => $subject,
		    'author' => $message,
		];
		$json_data = json_encode($data,JSON_UNESCAPED_UNICODE);
		$url = self::getHostUrl(); //获取请求地址
		$header= [
			 'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($json_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
		];
		echo "url = {$url} \tindex-data = {$json_data}\r\n";
		//请求解码接口
		$res = webRequest($url,self::$method_post,$json_data,$header);
		return $res;
	}

	/**
	* @note 添加索引-普通同步
	* @param $id int 小说ID
	* @param  $subject string 书名
	* @param  $message string 消息
	* @return array
	*/
	public static function addDocument($id,$subject ,$message){
		if(!$id || !$subject || !$message){
			return false;
		}
		    
		$xs    = new XS(self::$dbname);
		$index = $xs->index;
		// 整理迅搜索引所需文章信息
		$data = [
		    'pid' => $id, // 此字段为主键，必须指定
		    'book_name' => $subject,
		    'author' => $message,
		    'chrono' => time()
		];
		// 创建文档对象，默认字符集utf-8
		$doc = new XSDocument;
		$doc->setFields($data);
		// 添加到索引数据库中并刷新缓存
		$ret =$index->add($doc)->flushIndex();
		return true;
	}

	/**
	* @note 保存索引
	* @param $id int 小说ID
	* @param  $subject string 书名
	* @param  $message  string 消息内容
	* @return array
	*/
	public static function saveDocument($id,$subject ,$message){
		if(!$id || !$subject || !$message){
			return false;
		}
		$xs    = new XS(self::$dbname);
		$index = $xs->index;
		// 整理迅搜索引所需文章信息
		$data = [
			'pid' => $id, // 此字段为主键，必须指定
			'book_name' => $subject,
			'author' => $message,
			'chrono' => time()
		];
		// 创建文档对象，默认字符集utf-8
		$doc = new XSDocument;
		$doc->setFields($data);
		// 更新到索引数据库中
		$index->update($doc)->flushIndex();
		return true;
	}

	/**
	* @note 删除索引
	* @param $id int 小说ID
	* @return array
	*/
	public static function delDocument($id){
		if(!$id){
			return false;
		}
		$xs     = new XS(self::$dbname);
		$index  = $xs->index;
		//删除索引操作，$id为项目主键id
		$ret = $index->del($id)->flushIndex();
		return true;
	}

	/**
	* @note 通过接口删除索引-删除后会立即更新
	* @param $id int 小说ID
	* @return array
	*/
	public static function delDocumentCli($id){
		if(!$id){
			return false;
		}
		$data = [
			'req'	=>	self::$delReq,//添加类型
			'id'=> $id,
		];
		$json_data = json_encode($data,JSON_UNESCAPED_UNICODE);
		$url = self::getHostUrl(); //获取请求地址
		$header= [
			'Content-Type: application/json; charset=utf-8',
			'Content-Length:' . strlen($json_data),
			'Cache-Control: no-cache',
			'Pragma: no-cache'
		];
		echo "url = {$url} \tdel-data = {$json_data}\r\n";
		//请求解码接口
		$res = webRequest($url,self::$method_post,$json_data,$header);
		return $res;
	}


	/**
	* @note 校验是否为纯数字
	* @param $str string 输入字符
	* @return array
	*/
	private static function isPureNumber($str) {
	    return ctype_digit($str);
	}

	/**
	* @note 纠错功能字体修正
	* @param $keyword string 搜索关键字
	* @param $limit int 限制搜索条数
	* @param $str_set int 设定纯数字的切割长度信息
	* @return array |bool
	*/
	public static function handleErrorText($xs,$text=''){
		if(!$text){
			return false;
		}
		$search = $xs->search; 
		$search->setQuery($text);
		$docs = $search->search();  
		$errorList = [];
		// 由于拼写错误，这种情况返回的数据量可能极少甚至没有，因此调用下面方法试图进行修正
		$corrected = $search->getCorrectedQuery();
		if (count($corrected) !== 0)
		{
		   // 有纠错建议，列出来看看；此情况就会得到 "测试" 这一建议
		   // echo "您是不是要找：\n";
		   foreach ($corrected as $word)
		   {
		      $errorList[]=[
		      	'word'	=> $word,
		      ];
		   }
		}
		return $errorList;
	}


	/**
	* @note 关键字搜索结果-【根据keyword搜索】
	* @param $keyword string 搜索关键字
	* @param $limit int 限制搜索条数
	* @return array
	*/
	public static function getSearchByKeywords($keyword,$limit=20){
		if(!$keyword){
			return [];
		}
		$xs     = new XS(self::$dbname);
		//判断是否为中文
		if(self::isAllChinese($keyword)){
			//纠错发现错误用第一个的。
			$rightText= self::handleErrorText($xs,$keyword);
			if(!empty($rightText) && isset($rightText[0]['word'])){
				$keyword = $rightText[0]['word']??'';
			}
		}    
		    
		    
		$search = $xs->search; // 获取 搜索对象
		$docs = $search
				->setCollapse('pid') // 表示搜索结果按 tid 字段的值归并，至多返回 1 条最匹配的数据
				->addWeight('book_name', $keyword) //增加附加条件：提升标题中包含 'xunsearch' 的记录的权重
				->setLimit($limit) //设置返回前20条
				->search($keyword);
		return $docs;  
	}



	/**
	* @note 判断是否为中文
	* @param $input string 输入字符
	* @return array
	*/
	public static function isAllChinese($input) {
	    // 正则表达式：全匹配汉字
	    return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $input);
	}




	/**
	* @note 获取搜索结果【联想搜索】
	* @param $keyword string 搜索关键字
	* @param $limit int 限制搜索条数
	* @param $str_set int 设定纯数字的切割长度信息
	* @return array
	*/
	public static function getSearchImagineList($keyword="",$limit = 20,$str_set = 4){
		if(!$keyword){
			return [];
		}

		$xs     = new XS(self::$dbname);

		$words = [];
		//判断如果是设置如果是数字
		if(self::isPureNumber($keyword)){
			//如果是纯数字，而且超过5就切割
			$strData = str_split($keyword,$str_set);
			foreach($strData as $val){
				$words[] =[
					'word'	=>$val,
				];
			}
			//如果还是顺出资的话
		}else{
			//调用分词器
			$tokenizer = new \XSTokenizerScws;     // 直接创建分词实例   
			//纠整错别字的调整,判断如果是纯汉字进行纠错
			$rightText = [];
			//全是中文,如果不判断为全中文，纠错会改变其他的字符，所以要特殊处理下。
			if(self::isAllChinese($keyword)){ 
			  	$rightText= self::handleErrorText($xs,$keyword);
			}
			if(!empty($rightText)){
				$words = $rightText;//如果不为空说明纠错有错别字
			}else{
				$words = $tokenizer->getResult($keyword);
			}
			$infos = [];
			if($words){
				//特殊的词语处理配置，暂时现有这个
				foreach($words as $key =>$v){
					$word = trim($v['word']);
					if(!$word){
						continue;
					}
					//处理特殊词汇
					if (in_array($word,self::$specialWores)){
					  	$infos[]=[
					  		'word' =>'重生',
					  	];	
					  	break;
					}
					//判断是否为中文
					if(self::isAllChinese($word)){
						//处理错别字
						$correctData = self::handleErrorText($xs,$word);
						if(!empty($correctData) && isset($correctData[0]['word'])){
							$words[$key]['word'] =$correctData[0]['word'] ?? '';
						}
					}
					
				}
			}
			$words = array_merge($words,$infos);
		}    
		//设置分词的模糊搜索
		$query = 'book_name:'.implode(' OR ',array_column($words,'word')); 
		$search = $xs->search; // 获取搜索对象
		$search->setQuery($query); // 设置搜索语句
		$search->setCollapse('pid');// 表示搜索结果按 tid 字段的值归并，至多返回 1 条最匹配的数据
		// $search->setIgnore(true); //忽略标点符号
		$search->setLimit($limit); // 设置搜索条数
		$docs = $search->setFuzzy()->search();   //setFuzzy() 模糊搜索
		return $docs;		    
		    
		    

		// $words = $tokenizer->getResult($keyword);
		// echo "<pre>";
		// print_r($words);
		// exit;
		// //都重生了谁谈恋爱啊
		// $tops = $tokenizer->getTops($keyword, 5, 'n,v,vn');
		// echo "<pre>";
		// print_r($tops);
		// exit;
		    
		// $suggestSearch = $xs->search;
		// $suggestSearch->setQuery($keyword);  // 使用模糊搜索
		// $suggestResult = $suggestSearch->search();
		// echo "<pre>";
		// var_dump($suggestResult);
		// echo "</pre>";
		// exit();
		    

		    
		// $search = $xs->search; // 获取 搜索对象
		// $docs = $search
		// 		// ->setCollapse('pid') // 表示搜索结果按 tid 字段的值归并，至多返回 1 条最匹配的数据
		// 		->setFuzzy(true) 	//设为 true 表示开启模糊搜索, 设为 false 关闭模糊搜索 ||注意setFuzzy 必须在setQuery之前调用
		// 		// ->setQuery($keyword)
		// 		->addWeight('book_name', $keyword) //增加附加条件：提升标题中包含 'xunsearch' 的记录的权重
		// 		->setLimit(20) //设置返回前20条
		// 		// ->setSort('chrono')
		// 		->search($keyword);
		// return $docs;
	}

}
?>
