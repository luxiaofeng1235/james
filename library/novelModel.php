<?php

use Overtrue\Pinyin\Pinyin;
use QL\QueryList;

//导入拼音转换类
/*
 * 处理小说的主要模型业务（暂时放在这里）
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */

class NovelModel
{


	public static $redis_expire_time = 7200; //默认2个小时
	public static $dict_exchange = [
		'title'          =>      'book_name', //小说书名
		'cover_logo'      =>      'pic', //小说封面
		'author'          =>      'author', //作者
		'tag'             =>      'tags', //标签
		// 'tag'             =>       'tag_name',//标签名称
		'intro'           =>      'desc', //简介
		'nearby_chapter'  =>      'last_chapter_title', //最新章节
		'story_link'      =>      'source_url', //采集来源
		'cate_name'       =>      'class_name', //小说分类名称
		'status'          =>      'serialize', //是否完本状态
		'third_update_time' =>    'last_chapter_time', //网站的第三方时间
		'text_num'			=>		'text_num' //总字数
	];
	private static $validate_type = ['curl', 'ghttp']; //验证类型 支持curl和ghttp验证
	private static $imageType = 'jpg'; //默认的头像
	public static $prefix_html = 'detail_'; //html的前缀

	protected static $run_status = 1; //已经运行完毕的

	protected static $text_num = 2000; //默认的的字数

	public static $is_no_async = 0; //未同步的

	private static $default_pic = '/data/pic/default_cover.jpg'; //默认的图片


	public static $file_type = 'txt'; //存储为txt的格式文件

	public static $json_file_type = 'json'; //存储为json文件格式

	//过滤不必要的广告章节
	public static $filterWords = null;
	public static $db_conn = 'db_novel_pro';
	private static $table_name = null;

	//过滤文本需要用的配置
	public static $filterContent = null; //过滤文本中的内容




	//检查当前是否有
	/**
	* @note 检查当前是否有下架的书籍，并进行删除
	* @param  $where string 查询条件
	* @param $table_name string 表名 
	* @param $status int 显示状态
	* @return array
	*/
	public static function checkSearchBookClosed($where,$table_name,$status=0){
		if(!$where || !$table_name){
			return false;
		}
		global $mysql_obj;
		    
		$sql = "select * from {$table_name} where {$where}";
		$list = $mysql_obj->fetchAll($sql,self::$db_conn);
		if($list && is_array($list)){
			$book_table_name  = Env::get('TABLE_MC_BOOK');
			$bookIds = array_column($list, 'bid');
			$sql  = "select id from ".$book_table_name. " where id in (".implode(',',$bookIds).") and status = {$status}";
			$bookRes = $mysql_obj->fetchAll($sql ,self::$db_conn);
			if($bookRes){
				$delIds=  array_column($bookRes ,'id');
				//如果存在就删掉
				$sql = "delete from {$table_name} where bid in (".implode(',',$delIds).")";
				echo "online-delete-sql = {$sql} \r\n";
				$mysql_obj->query($sql,self::$db_conn);			
			}else{
				echo "暂未发现待删除的{$table_name}表的的下架的书籍数据\r\n";
			}
		}
	}



	/**
	* @note 自动粉刺
	* @param word_frequency 分词检索
	* @return array
	*/
	public static function cutChineseWords($text=''){
		if(!$text){
			return [];
		}
		$dict = array("都", "重","谁");
		$word_frequency = array();
		$length = strlen($text);
		$start = 0;
		for ($i = 0; $i < $length; $i++) {
		    $word = substr($text, $start, $i - $start + 1);
		    if (in_array($word, $dict)) {
		        if (!isset($word_frequency[$word])) {
		            $word_frequency[$word] = 0;
		        }
		        $word_frequency[$word]++;
		        $start = $i + 1;
		    }
		}
		return $word_frequency;
	}

	/**
	* @note 交换元素第N个和第一个的位置
	* @param array array交换的数组
	* @param $position int 数组位置信息
	* @return condition
	*/
	public static function  swapFirstWithAny($array, $position) {
		//异常处理
		if($position<0){
			$position = 0;
		}
	    // 检查位置是否有效
	    if ($position < 0 || $position >= count($array)) {
		        echo "位置无效";
		        return;
		    }
	    // 交换元素
	    $temp = $array[0];
	    $array[0] = $array[$position];
	    $array[$position] = $temp;
	    return $array;
	}

	/**
	 * @note 获取断章采集的来源
	 * @return condition
	 */
	public static function getDuanUrlReferCondition(){
		$referList = Env::get('SOURCE_LIST');
		if($referList){
			$refererArr = explode(',',$referList);
			$refererArr = array_unique($refererArr);// 去重
			$condition = '';
			foreach($refererArr as $v){
				if(!$v) continue;
				$where_data[]= 'instr(source_url,\''.$v.'\')>0';
			}
			$condition = '( ' .implode(' OR ', $where_data).' )';
			return $condition;
		}
	}

	/**
	 * @note 获取跑书吧的首页监听数据信息
	 * @param $url string 页面地址信息
	 * @return array
	 */
	public static function getPaoshu8IndexData($url)
	{
		if (!$url) {
			return [];
		}
		global $urlRules;
		$urls[] = $url;
		$list = StoreModel::swooleRquest($urls);
		$range_update = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['range_update'];
		$update_rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['update_list'];
		$novelList = [];
		foreach ($list as $key => $html) {
			if (!$html)
				continue;
			$storyList = QueryList::html($html)
				->rules($update_rules)
				->range($range_update)
				->query()
				->getData();
			$storyList = $storyList->all();
			$source = NovelModel::getSourceUrl($url);
			if ($storyList) {
				foreach ($storyList as &$val) {
					//批量处理下
					if ($source == 'ipaoshuba' || $source == 'ipaoshubaxs') {
						$urlHost = parse_url($val['story_link']);
						$new_story_link = $url . substr($urlHost['path'], 1);
						//处理story_id
						$story_id = trim($val['story_id']);
						$story_id = str_replace('Partlist/', '', $story_id);
						$val['story_link'] = $new_story_link;
						$val['story_id'] = $story_id;
						//处理标题
						$val['title'] = str_replace('[目]', '', $val['title']);
					}
				}
			}
			$novelList = array_merge($novelList, $storyList);
		}
		return $novelList;
	}

	/**
	 * @note 检测redis代理IP是否可用
	 *
	 */
	public static function checkProxyExpire()
	{
		global $redis_data;
		$redis_key = Env::get('ZHIMA_REDIS_KEY');
		if (!$redis_data->get_redis($redis_key)) {
			return 0; //代理已过期
		} else {
			return 1; //代理可用
		}
		$content = $redis_data->get_redis($tag);
	}

	/**
	 * @note 生成url
	 *
	 */
	public static function getBanjiashiUrl($id, $index = 1)
	{
		if (!$id) {
			return false;
		}
		$url = StoreModel::replaceParam(Env::get('APICONFIG.BANJIASHI_NEXT_URL'), 'store_id', $id);
		$url = StoreModel::replaceParam($url, 'page', $index);
		return $url;
	}




	/**
	 * @note 初始化连载状态以及其他的字段信息
	 * @param $store_data array 抓取的数据信息
	 * @param array
	 *
	 */
	public static function initStoreInfo($store_data = [])
	{
		if (!$store_data) {
			return false;
		}

		$story_link = $store_data['story_link'];
		//为了应付过审，需要只跑这两个分类的
		if ($store_data['source'] == 'paoshu8') {
			$store_data['cate_name'] = '网游竞技';
			$store_data['tag'] = '网游';
		}
		///判断是否为xs74w网站的数据，因为这个网站的数据没有连载状态给他默认一个
		if (strpos($story_link, 'xs74w')) {
			$diff_time = date('Y-m-d 00:00:00');
			$unixtime = strtotime($diff_time);
			//判断当前的时间是否大于最后更新的时间，如果大于就是说明已经完本了
			if (strtotime($store_data['third_update_time']) &&   $unixtime > $store_data['third_update_time']) {
				$status = '已经完本';
			} else {
				$status = '连载中';
			}
			$store_data['status'] = $status;
		}
		//数据转换
		if ($store_data['cate_name'] == '游体') {
			$store_data['cate_name'] = '游戏';
			$store_data['tag'] = '游戏';
		}
		if ($store_data['cate_name'] == '玄奇') {
			$store_data['cate_name'] = '玄幻';
			$store_data['tag'] = '玄幻';
		}
		if ($store_data['cate_name'] == '女生') {
			$store_data['cate_name'] = '都市';
			$store_data['tag'] = '都市';
		}
		//完结 连载
		if ($store_data['status'] == '连载' || $store_data['status'] == '连载中' || $store_data['status'] == '新书上传') {
			$store_data['status'] = '连载中';
		} else if ($store_data['status'] == '完结' || $store_data['status'] == '已完结') {
			$store_data['status'] = '已经完本';
		} else {
			$store_data['status'] = '未知';
		}
		if ($store_data['source'] == 'ipaoshuba') {
			//处理默认图图片问题，之前的图片不太好看
			if (strstr($store_data['cover_logo'], 'nocover')) {
				$store_data['cover_logo'] = Env::get('APICONFIG.DEFAULT_PIC'); //使用默认图
			}
			$url = str_replace("Partlist", "Book", $store_data['story_link']);
			$bookRes = StoreModel::swooleRquest($url);
			global $urlRules;
			$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['ipaoshuba_append'];
			$html = array_values($bookRes)[0] ?? '';
			$info_data = QueryList::html($html)
				->rules($rules)
				->query()
				->getData();
			if (!strstr($store_data['cover_logo'], 'https://')) {
				$store_data['cover_logo'] = Env::get('APICONFIG.IPAOSHUBA_URL') . $store_data['cover_logo'];
			}
			$info_data = $info_data->all();
			//只有标题为空需要统一处理下
			if(empty($store_data['title'])){
				$title = trimBlankSpace($info_data['title']);
				$title = addslashes($title);
				//转义特殊字符 双引号等
				$store_data['title'] = $title;
			}
			$store_data['status'] = $info_data['status'];
			$store_data['text_num'] = $info_data['text_num'] ?? 0; //总字数
			$store_data['third_update_time'] = strtotime($info_data['third_update_time']);
		}else if($store_data['source'] =='xiaoshubao' && !$store_data['intro']){
			 $html = webRequest($store_data['story_link'],'GET');
			 global $urlRules;
			 $rules= $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['xiaoshubao_append'];
			 $info = QueryList::html($html)
			 				->rules($rules)
			 				->query()
			 				->getData();
			 $info = $info->all();
			 $store_data['intro'] = $info['intro'];
		}
		//去掉分页，不然保存会出问题
		if(isset($store_data['chapter_pages'])){
            unset($store_data['chapter_pages']);
        }
		return $store_data;
	}



	/**
	 * @note 转换编码格式
	 *
	 */
	public static function iconv_utf8($data)
	{
		if (!$data) {
			return false;
		}
		if (count($data) == count($data, 1)) {
			foreach ($data as &$val) {
				//转换数组对象
				$val = iconv('gbk', 'utf-8', $val);
			}
		} else {
			//处理二维数组的转换处理
			foreach ($data as $key => $val) {
				foreach ($val as &$v) {
					$v = $val = iconv('gbk', 'utf-8', $v);
				}
				$data[$key] = $val; //需要赋值一下，才能生效
			}
		}
		return $data;
	}


	/**
	 * @note 检测 缓存跑书吧的首页
	 * @param $url string 网站的url
	 * @param $expire_time stirng 过期时间,默认现在每五分钟就请求刷新一次到缓存里,这个地方只保留 五分钟，每次脚本刷新自动取更新信息
	 * @return array
	 *
	 */
	public static function cacheHomeList($url, $expire_time = 300)
	{
		if (!$url) {
			return false;
		}
		//获取网站来源
		$source = NovelModel::getSourceUrl($url);
		$redis_key = $source . '_home_nearby';
		global $redis_data;
		$data = $redis_data->get_redis($redis_key);
		if (!$data) {
			$list = webRequest($url, 'GET');
			$redis_data->set_redis($redis_key, $list, $expire_time);
			$data = $list;
			unset($list);
		}
		return $data;
	}

	/**
	 * @note 检测 图片的代理是否可用
	 *
	 */
	public static function checkImgKey()
	{
		global $redis_data;
		$redis_key = Env::get('ZHIMA_REDIS_IMG');
		if (!$redis_data->get_redis($redis_key)) {
			return 0; //代理已过期
		} else {
			return 1; //代理可用
		}
		$content = $redis_data->get_redis($tag);
		return $content;
	}

	/**
	 * @note 检测移动端的key是否可用
	 *
	 */
	public static function checkMobileKey()
	{
		global $redis_data;
		$redis_key = Env::get('ZHIMA_REDIS_MOBILE_KEY');
		if (!$redis_data->get_redis($redis_key)) {
			return 0; //代理已过期
		} else {
			return 1; //代理可用
		}
		$content = $redis_data->get_redis($tag);
		return $content;
	}

	/**
	 * @note 获取缓存里的mc_book表对应的id
	 * @param $store_id int 小说id
	 * @return interer
	 *
	 */
	public static function getRedisProId($store_id)
	{
		if (!$store_id) {
			return 0;
		}
		global $redis_data;
		$redis_key =  Env::get('REDIS_STORE_KEY') . $store_id;
		$pro_book_id = $redis_data->get_redis($redis_key);
		return $pro_book_id ? $pro_book_id :  0;
	}


	/**
	 * @note 获取redis中小说的基础详情信息
	 *
	 */
	public static function getRedisBookDetail($store_id)
	{
		if (!$store_id) {
			return 0;
		}
		global $redis_data;
		$redis_key =  Env::get('REDIS_STORE_DETAIL_KEY');
		$info = $redis_data->hget_redis($redis_key, $store_id);
		if (!$info) {
			$info = array();
		} else {
			$info = json_decode($info, true);
		}
		return $info ?? [];
	}

	/**
	 * @note 检测移动端补数据的脚本呢的key
	 *
	 */
	public static function checkMobileEmptyKey()
	{
		global $redis_data;
		$redis_key = Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA');
		if (!$redis_data->get_redis($redis_key)) {
			return 0; //代理已过期
		} else {
			return 1; //代理可用
		}
		$content = $redis_data->get_redis($tag);
	}


	/**
	 * @note 返回对应的cmd的具体的路径信息
	 * @param $data 需要转换的数据
	 * @return array
	 *
	 */
	public static function cmdRunPath()
	{
		$base_dir = ROOT . 'paoshu8' . DS;
		$base_dir = str_replace('\\', '/', $base_dir);
		return $base_dir;
	}

	/**
	 * @note 获取要执行的curl命令
	 * @return str
	 *
	 */
	public static function getCommandInfo($list = [])
	{
		if (!$list)
			return [];
		$proxy_auth = self::getProxyItem();
		if (!$proxy_auth)
			return false;
		$urlArr = [];
		// $list1 = array_values($list);
		$data = array_column($list, 'link_url');
		foreach ($data as $key => $val) {
			$pathInfo = parse_url($val);
			$link_string = substr($pathInfo['path'], 1);
			$link_string = str_replace('.html', '', $link_string);
			$urlArr[] = $link_string;
		}
		$curlHtml = implode(',', $urlArr);
		$str = '{' . $curlHtml . '}';
		$shell_cmd = 'curl  -H "Content-Type:text/pln;charset=UTF-8"  -H "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36" --socks5  ' . $proxy_auth . ' -s  ' . Env::get('APICONFIG.PAOSHU_HOST') . '/' . $str . '.html';
		return $shell_cmd;
	}

	/**
	 * @note 对比新旧数据进行返回处理
	 * @param $old array 旧数据
	 * @param $new array 新数据
	 * @return array
	 *
	 */
	public static function arrayDiffFiled($old, $new)
	{
		if (!$old || !$new) {
			return false;
		}
		if(isset($new['text_num'])){
			unset($new['text_num']);
		}
		$diff_filed = array_diff_assoc($new, $old);
		if (!$diff_filed) {
			$diff_filed = [];
		}
		return $diff_filed;
	}

	/**
	 * @note 转换数据信息
	 * @param $data 需要转换的数据
	 * @return array
	 *
	 */
	public static function changeChapterInfo($data)
	{
		if (!$data)
			return false;
		foreach ($data as $key => $val) {
			$link_url = $val['chapter_link'] ?? '';
			$pathData = parse_url($link_url);
			$data[$key]['link_url'] = $pathData['path'] ?? '';
			$data[$key]['link_str'] = $pathData['path'] ?? ''; //兼容其他脚本去跑数据
			$data[$key]['link_name'] = $val['chapter_name'] ?? '';
		}
		return $data;
	}


	/**
	 * @note 移除不必要的广告章节
	 * @param $data array 章节列表
	 * @return object
	 *
	 */
	public static function removeAdInfo($data)
	{
		global $advertisement;
		self::$filterWords = $advertisement['chapter']; //获取广告词
		if ($data) {
			foreach ($data as $key => $val) {
				foreach (self::$filterWords as $v) {
					if (strstr($val['link_name'], $v)) {
						unset($data[$key]);
						break;
					}
				}
			}
			$list = array_values($data);
			return $list;
		}
	}
	/**
	 * @note 自动加载分类配置文件
	 *
	 */
	public static function getCateConf()
	{
		if (is_file(dirname(__DIR__) . '/config/novel_class.php')) {
			$config = require  dirname(__DIR__) . '/config/novel_class.php';
			return $config;
		} else {
			return false;
		}
	}


	/**
	 * @note 替换里面的指定空行
	 * @param $data array 处理数据
	 * @return array
	 *
	 */
	protected static function replaceListArr($data = [])
	{
		if (!$data)
			return false;
		foreach ($data as &$val) {
			$val = str_replace("\r\n", '', $val);
		}
		return $data;
	}


	/**
	 * @note 处理指定的HTML中的字符换行问题
	 * @param $html string 文本内容
	 * @return array
	 *
	 */
	public static function dealHtmlBr($string)
	{
		if (!$string) {
			return false;
		}
		$aa    = preg_split('/\r\n/', $string);
		foreach ($aa as &$v) {
			// $v = removeTabEnter($v);
		}
		echo "<pre>";
		var_dump($aa);
		exit;
		$data = implode('', $aa);
		//按照</dd>切割换行符进行转换
		$str  = preg_replace('/<\/dd>/', "</dd>\r\n", $data);
		return $str;
	}

	/**
	 * @note 根据当前的章节连接返回具体IDE总数信息
	 * @param $link_href string 小说采集源连接
	 * @param $link_text string 小说采集源文本
	 * @return array
	 *
	 */
	protected static function getxugesRealList($link_href, $link_text)
	{
		if (!$link_href || !$link_text) {
			return false;
		}
		$removeStatus = 1; //设置一个标记，如果是就说明需要删除对应的章节信息
		foreach ($link_text as $value) {
			$chapter_name = trimBlankSpace($value);
			if ($chapter_name && in_array($chapter_name, ['章节目录', '目录'])) {
				$removeStatus = 1;
			}
		}
		if ($removeStatus) {
			//这里是说明满足条件，就把原来的直接去掉的配置信息
			array_shift($link_href);
			array_shift($link_text);
		}
		$hrefArr = array_values($link_href);
		$textArr = array_values($link_text);
		return ['href' => $hrefArr, 'text' => $textArr];
	}




	/**
	 * @note 处理正则转义字符
	 * @param $title string 小说标题
	 * @return array
	 *
	 */
	public static function exchangePregStr($title = "")
	{
		if (!$title) {
			return false;
		}
		$title = str_replace('(', '\(', $title);
		$title = str_replace(')', '\)', $title);
		$title = str_replace('.', '\.', $title);
		$title = str_replace(',', '\,', $title);
		$title = str_replace('[', '\[', $title);
		$title = str_replace(']', '\]', $title);
		$title = str_replace('$', '\$', $title);
		$title = str_replace('?', '\?', $title);
		$title = str_replace('{', '\{', $title);
		$title = str_replace('}', '\}', $title);
		$title = str_replace('*', '\*', $title);
		$title = str_replace('+', '\+', $title);
		$title = str_replace('$', '\$', $title);
		$title = str_replace('^', '\^', $title);
		$title = str_replace('|', '\|', $title);
		return $title;
	}


	/**
	 * @note 从特定的url中获取对应的数据信息
	 * @param $html string 小说的详情内容信息
	 * @param $title string 小说标题
	 * @param $is_fanti_ex bool 是否为繁简体转换 默认false不转换 true：转换
	 * @param $is_exchange bool 是否转换gbk到utf8 默认不进行转换, true:转换
	 * @param $source_ref string 网站来源
	 * @return array
	 *
	 */
	public static function getCharaList($html, $title = '', $is_fanti_ex = false, $is_exchange = false, $source_ref = '')
	{
		if (!$html || !$title) {
			return '';
		}
		if ($is_exchange) {
			$html = array_iconv($html); //转换编码格式
		}
		if($source_ref == 'bqwxg8'){
			$html = iconv('gbk', 'utf-8//ignore', $html);
		}
		    
		//处理繁简体的转换文字
		# $link_reg = '/<a.+?href=\"(.+?)\".*>/i'; //匹配A连接
		$link_reg = '/<a.*?href="(.*?)".*?>/';
		//这个地方因为A链接有可能是多个的形式展示，导致取不出来A标签，用一个万能的表达式来根据当前的配置取相关的连接信息
		$text_reg = '/<a.*?href=\"[^\"]*\".*?>(.*?)<\/a>/ims'; //匹配链接里的文本(zhge)
		//只取正文里的内容信息，其他的更新的简介不要
		//匹配正文章节内容

		    
		//标题处理正则转义字符
		$title = self::exchangePregStr($title); //小说转义字符
		$contents = '';

		//特殊的标记需要自动过滤掉，这个网站有点问题
		if($source_ref == 'ipaoshuba'){
			$html = str_replace('","copyright":"','',$html);//替换调一些特殊标记防止采集有问题
		}
		    

		//<dt>《毒誓一九四一》正文</dt>
		//兼容这种带正文的正则
		//《雾乡猎梦人》正文
		if (preg_match('/《' . $title . '》正文.*<\/dl>/ism', $html, $with_content)) { //带有正文的匹配
			$contents = $with_content[0] ?? '';
		}else if (preg_match('/章节列表.*<\/dl>/ism', $html, $with_content)) { //带有正文的匹配
			$contents = $with_content[0] ?? [];
		} else if (preg_match('/<div id=\"list\".*?>.*<\/dl>/ism', $html, $list)) { //带有id="list"的规则
			$contents = $list[0] ?? [];
		} else if (preg_match('/<div class=\"info-chapters flex flex-wrap\">.*?<\/div>/ism', $html, $list)) { //匹配台湾网站
			$contents = $list[0] ?? '';
		} else if (preg_match('/<div class=\"book-list clearfix\">.*?<\/div>/ism', $html, $matches)) { //兼容xuges网站的格式1
			$contents = $matches[0] ?? '';
		} else if (preg_match('/<td class=\"tdw3\".*?<\/table>/ism', $html, $list)) {//处理xudes的另外一种样式
			$contents = $list[0] ?? '';
		} else if (preg_match('/<table class=\"tbw3\".*?<\/table>/ism', $html, $list)) {
			//兼容xuges网站的格式2，这个网站有两个样式
			$contents = $list[0] ?? '';
		} else if(preg_match('/<ul class=\"chaw_c\".*?>.*<div class=\"setbox\">/ism', $html, $list)){
			//处理27k的导入配置
			$contents = $list[0] ?? '';
		}else if(preg_match('/《'.$title.'》正文.*?>.*<p class=\"articles\">/ism', $html, $list)){//兼容含有正文开头的
			$contents = $list[0] ?? '';
		}
		    
		if ($contents) {
			////替换style的样式标签，防止采集不到数据
			$contents = str_replace('href =', 'href=', $contents);
			//处理中间的换行字符,不然匹配会出问题
			preg_match_all($link_reg, $contents, $link_href); //匹配链接
			preg_match_all($text_reg, $contents, $link_text); //匹配文本;

			$len = count($link_href[1]);
			$chapter_list = [];
			//回调函数处理去除换行
			$link_text = array_map('trimBlankLine', $link_text);
			//回调函数去除收尾空格


			for ($i = 0; $i < $len; $i++) {

				$chapter_name = $link_text[1][$i] ?? '';
				if ($source_ref == 'xuges' && in_array($chapter_name, ['章节目录', '目录'])) {
					continue;
				} else if ($source_ref == 'ipaoshuba' && strstr($chapter_name, 'txt全文下载')) {
					continue;
				}
				// preg_match('/^\(\d{1,}\)+$/','(16)',$matches);
				// echo '<pre>';
				// var_dump($matches);
				// echo '</pre>';
				// exit;
				if ($source_ref == 'xuges') {
					if (preg_match('/^\(\d{1,}\)+$/', $chapter_name, $matches)) {
						$index = str_replace(array('(', ')'), '', $chapter_name);
						$numSet = (int) $index;
						$everyNum = $numSet - 1; //每次需要减1去计算
						$chapterName = ($link_text[1][$i - $everyNum] ?? '') . $chapter_name; //新的章节名称

					} else {
						$chapterName = $chapter_name;
					}
				} else {
					$chapterName = $chapter_name;
				}
				//汇总最终的数据组合
				$chapter_list[] = [
					'link_name' =>  trimBlankSpace($chapterName),
					'link_url'  => $link_href[1][$i] ?? '',
				];
			}
			// echo '<pre>';
			// print_R($chapter_list);
			// echo '</pre>';
			// exit;
			//处理繁简体转换
			if ($is_fanti_ex) {
				$chapter_list =  StoreModel::traverseEncoding($chapter_list);
			}
			return $chapter_list;
		} else {
			//如果上面的没有匹配出来直接从dd里获取对应的连接
			//直接暴力一点
			//直接从链接里开始遍历得了
			preg_match('/《' . $title . '》正文.*<\/dl>/ism', $html, $urls);
			$chapter_list = [];
			if (isset($urls[0])) {
				$item = preg_split('/<dd>/', $urls[0]);
				$item = array_filter($item);
				foreach ($item as $key => $val) {
					if (strpos($val, '.html')) {
						preg_match($link_reg, $val, $t1);
						preg_match($text_reg, $val, $t2);
						if (isset($t1[1]) && !empty($t1[1])) {
							$chapter_list[] = [
								//处理前后的尾部空格信息
								'link_name' =>  isset($t2[1])  ? trimBlankSpace($t2[1]) : '',
								'link_url' => $t1[1] ?? '',
							];
						}
					}
				}
			}
			return $chapter_list;
		}
	}

	/**
	 * @note 初始化标签列表数据
	 * @param $redis_key string tag缓存标记
	 * @param $tiemout int 过期时间
	 * @return array
	 */
	public static function initTagList($redis_key = 'tag_list',$timeout = 86400)
	{
		global $redis_data, $mysql_obj;
		//删除redis的主要信息配置修改
		 $redis_data->del_redis($redis_key);
		if (!$redis_data->get_redis($redis_key)) {
			$sql = "select id ,tag_name,column_type from mc_tag";
			$info = $mysql_obj->fetchAll($sql, 'db_novel_pro');
			$info && $redis_data->set_redis($redis_key, json_encode($info), $timeout);
			$data  = $info;
			unset($info);
		} else {
			$res = $redis_data->get_redis($redis_key);
			$t = json_decode($res, true);
			$data = $t ?? [];
		}
		return $data;
	}

	/**
	 * @note 获取M站的连接地址
	 * @param string $msg
	 * @return array
	 */
	public static function mobileLink($url)
	{
		if (!$url)
			return false;
		$urlArr = parse_url($url);
		$items = explode('/', $urlArr['path']);
		$novel_id = 0;
		if (isset($items[1])) {
			$data = explode('_', $items[1]);
			$novel_id = $data[1] ?? 0;
		}
		//拼接获取对应的URL地址
		$link = Env::get('APICONFIG.PAOSHU_MOBILE_CHAPTER_URL') . '-' . $novel_id . '-' . str_replace('.html', '', $items[2] ?? 0);
		$info['path'] = $urlArr['path'];
		$info['link'] = $link;
		return $info;
	}

	/**
	 * 根据配置加载文件信息
	 * @param integer $store_id 小说id
	 */
	public static function reloadChapterTotal($store_id)
	{
		if (!$store_id)
			return [];
		//加载配置文件信息
		$file_path = Env::get('SAVE_MOBILE_NUM_PATH') . DS . $store_id . '.' . self::$json_file_type;
		//读取配置文件
		$goods_arr = readFileData($file_path);
		$list = json_decode($goods_arr, true);
		if (!empty($list)) {
			//转换数组
			$items = [];
			foreach ($list as $val) {
				//按照 path=> pages对应去
				$items[$val['path']] = $val['pages'];
			}
			return $items;
		} else {
			return [];
		}
	}

	/**
	 * @note 获取当前页面URL里的页码
	 *
	 * @param $str string 页面头部信息
	 * @return interger
	 */
	public static function getCurrentPage($str)
	{
		if (!$str) {
			return 1;
		}
		$data = explode('/', $str); // (第1/3页) 替换
		if (!$data)
			return 1; //如果没有默认返回第一页
		if (isset($data[0])) {
			$novelStr = explode('(', $data[0]);
			//只取最后一个
			$numData = end($novelStr);
			preg_match('/\d/', $numData, $matches);
			if (isset($matches[0])) {
				$num = trim($matches[0]) ?? 0;
			} else {
				$num = 1;
			}
		} else {
			//没有获取的默认给1
			$num = 1;
		}
		return $num;
	}

	/**
	 * @note 获取当前页面的总数量z
	 *
	 * @param $meta_data array meta信息
	 * @param $html string 当前抓取到的url
	 * @param $num 当前页码，默认只计算第一页
	 * @return string
	 */
	public static function  getChapterPages($meta_data = '', $first_line = '', $num = 1)
	{
		if (!$meta_data || !$first_line)
			return false;

		$path = substr($meta_data, 0, -1);
		// $page_num = 0;
		// $con_str = preg_split('/<\/p>/',$html); //按照P标签切割函数
		// $pages =str_replace("\r\n",'', $con_str[0]); //替换里面的空行
		// $content = filterHtml($pages);//过滤特殊字符
		$showData = explode('/', $first_line); // (第1/3页) 替换
		if (!$showData)
			return 1; //如果没有默认返回第一页
		$end_string = end($showData); //只取最后一个，如果有多余的就有问题
		preg_match('/\d/', $end_string, $allPage);
		$everyPages  = $allPage[0] ?? 1;
		$all_num = intval($everyPages); //总的页码数量，需要判断是否有1页以上
		$path_info =  $path . '-' . $num;
		$info[$path_info] = $all_num; // 按个格式进行拼装
		return $info;
	}


	/**
	 * 转换URL连接诶地址
	 * @param array $data 章节类目
	 * @param $store_id int 小说ID
	 * @param $pro_type string count:同步章节用 empty：补空的章节用
	 * @return array
	 */
	public static function exchange_urls($data = [], $store_id = 3, $pro_type = 'empty')
	{
		if (!$data)
			return [];
		// if(!$offset || $offset<0)
		//   $offset =3;
		//加载配置信息
		$count_arr = [];
		if ($pro_type != 'count') {
			$count_arr = self::reloadChapterTotal($store_id);
		}

		$infos = [];
		foreach ($data as $val) {
			$url = trim($val['link_url']);
			if (!$url) continue;
			//转换获取M站的地址
			$mobile_link = NovelModel::mobileLink($url);
			$mobile_url = $mobile_link['link'] ?? ''; //连接地址
			$url_path = $mobile_link['path'] ?? '';
			if ($pro_type != 'count') {
				//判断是否存在当前加载的页码，如果存在就用配置里的
				if (isset($count_arr[$url_path])) {
					$pages = intval($count_arr[$url_path]);
				} else {
					$pages = 1;
				}
			} else { //只按照当前页计算
				$pages = 1; //如果是统计章节默认就1
			}
			// echo $pages.PHP_EOL;
			for ($i = 0; $i < $pages; $i++) {
				$s_url = $mobile_url . '-' . ($i + 1);
				$val['mobile_url'] = $s_url;
				$val['path'] = $url_path;
				$infos[] = $val;
			}
		}
		return $infos;
	}

	/**
	 * @note 处理https://www.xbiqiku2.com/2中的广告，主要太多了啊吗，还是分开写一下吧。
	 * @param string $content 小说内容
	 * @return $string
	 */
	public static function replaceXbiqiku2Advert($str = "")
	{
		if (!$str) {
			return false;
		}
		$replaceContent = '';
		$string = preg_replace("/☻此章节正在.*?稍后刷新访问/iUs", $replaceContent, $str);
		$string = preg_replace("/✿手机访问的帅哥美女，先注册个Ⓜ.xbiqiku.com会员好吗！！！/", $replaceContent, $string);
		$string = preg_replace("/☀注册本站会员.*?更方便阅读/iUs", $replaceContent, $string);
		$string = preg_replace("/如果此章是作者求票之类废话的，请跳过继续看下一章/iUs", $replaceContent, $string);
		$string = preg_replace("/☀请先收藏此页.*?不然等下找不到此章节/iUs", $replaceContent, $string);
		$string = preg_replace("/某某Нttps:\/\/wⓌw.xbiqiku.com\/59\/59282/", $replaceContent, $string);
		$string = preg_replace("/某某https:\/\/Ⓜ.xbiqiku.com\/59\/59282\//", $replaceContent, $string);
		$string = preg_replace("/盛望搬进了白马弄堂的祖屋院子.*?1v1\+he立意\:好好学习天天向上/iUs", $replaceContent, $string);
		$string = preg_replace("/告白Шww.xbiqiku.com\/89\/89819\//", $replaceContent, $string);
		$string = preg_replace("/告白Μ.xbiqiku.com\/89\/89819\//", $replaceContent, $string);
		$string = preg_replace("/【下本开《碳酸苏打》.*?立意:暗恋自由梦想/iUs", $replaceContent, $string);
		$string = preg_replace("/请握紧你手中扳手https:\/\/шШw.xbiqiku.com\/117\/117772\//", $replaceContent, $string);
		$string = preg_replace("/请握紧你手中扳手https:\/\/м.xbiqiku.com\/117\/117772\//", $replaceContent, $string);
		$string = preg_replace("/正版阅读在晋江文学城.*?经年累月形成一道凹坑。/iUs", $replaceContent, $string);
		$string = preg_replace("/正版阅读在晋江文学城.*?经年累月形成一道凹坑。/iUs", $replaceContent, $string);
		$string = preg_replace("/已完结同类型女强小说.*?立意:顽强不屈，向阳而生。/iUs", $replaceContent, $string);
		$string = preg_replace("/折月亮Нttps:\/\/wwШ.xbiqiku.com\/151\/151806\//", $replaceContent, $string);
		$string =  preg_replace("/折月亮https:\/\/Μ.xbiqiku.com\/151\/151806\//", $replaceContent, $string);

		$string =  preg_replace("/长宁将军https:\/\/wщw.xbiqiku.com\/35\/35302\//", $replaceContent, $string);
		$string =  preg_replace("/长宁将军нttps:\/\/.xbiqiku.com\/35\/35302\//", $replaceContent, $string);

		$string =  preg_replace("/再遇https:\/\/wшw.xbiqiku.com\/134\/134491\//", $replaceContent, $string);
		$string =  preg_replace("/再遇https:\/\/㎡.xbiqiku.com\/134\/134491\//", $replaceContent, $string);
		#尾部去除
		$string = preg_replace("/笔趣库为你提供最快的我真的只是人类更新.*?报送后维护人员会在两分钟内校正章节内容,请耐心等/iUs", $replaceContent, $string);
		return $string;
	}

	/**
	 * @note 替换广告和一些特殊字符
	 * @param string $str 小说内容
	 * @param $referer_url string 回调域名地址
	 * @param $html_path  具体的章节目录，方便处理过滤
	 * @return $string
	 */
	public static function replaceContent($str, $referer_url, $html_path, $title)
	{
		if (!$str) {
			$str = '';
		}
		global $advertisement;
		self::$filterContent = $advertisement['content'] ?? [];
		if (!self::$filterContent)
			return '';
		foreach (self::$filterContent as $keywords) {
			$matches = strstr($str, $keywords);
			preg_match('/' . $keywords . '/', $str, $matches);
			if (isset($matches[0])) {
				$str = str_replace($matches[0], '', $str);
			}
		}

		//过滤实体标签，类似>&nbsp;&nbsp;&nbsp;&nbsp;这样子的过滤一下
		$str = preg_replace('/&[#\da-z]+;/i', '', $str);
		//替换注释部分的信息
		$str = preg_replace('/<!--(.*?)-->/', '', $str);
		//////////////////////////广告和标签相关
		//过滤具体的html标签
		$str = preg_replace('/<script.*?>.*?<\/script>/ism', '', $str);
		$str = preg_replace('/<br><br><br>/', '', $str); //去除三个BR标签，没啥用
		//过滤网站中存在的广告
		//去除含有div的标签对信息

		$str = preg_replace('/一秒记住【笔趣阁 www.bqg24.net】，精彩小说无弹窗！/', '', $str);
		$str = preg_replace('/喜欢女配真的是来摆烂的请大家收藏：（）女配真的是来摆烂的更新度。/', '', $str);
		$str = str_replace('/一秒记住【笔趣阁 www.bqg24.net】，精彩小说无弹窗免费阅读！/', '', $str);
		$str = preg_replace('/<script.*?>.*?<\/script>/ism', '', $str);
		$str = preg_replace('/<div id="gc1".*?>.*?<\/div>/ism', '', $str);
		$hostUrl = parse_url($referer_url);
		$url = trim($hostUrl['host']); //只需要不带https或者http域名的
		$url = preg_replace('/\//', '\/', $url);
		$url = preg_replace('/\./', '\.', $url);
		$mobile_url = str_replace('www', 'm', $url); //移动端的地址
		$text_reg = "/请记住本书首发域名：{$url}.*?{$mobile_url}/iUs";
		# $text_reg1 ='/请记住本书首发域名.*?https:\/\/www\.xs74w\.com/iUs'
		// $str='请记住本书首发域名：www.mxgbqg.com。梦想文学网手机版阅读网址：m.mxgbqg.com';
		$str = preg_replace($text_reg, '', $str);



		// $a = preg_match("//")
		//针对个别网站需要特殊处理下
		if (preg_match('/xuges/', $referer_url)) {
			$str = preg_replace('/<\/td><\/tr>/ism', '', $str);
			$str = preg_replace('/<tr><td>/ism', '', $str);
			$str = preg_replace('/<hr \/>/ism', '', $str);
			$str = preg_replace('/<tr><td class=\"zhx\">/ism', '', $str);
			$str = preg_replace('/<span class=\"zs\">/ism', '', $str);
			$str = preg_replace('/<\/span>/ism', '', $str);
			$str = preg_replace('/<td class=\"jz\">/ism', '', $str);
			// $str = preg_replace('/\r\n.*?<br \/>\r\n/ism','', $str);
		} else if (preg_match('/xbiqiku2/', $referer_url)) {
			// $str = self::replaceXbiqiku2Advert($str);
		}
		$chapter_link = $referer_url . $html_path; //拼接对应的文章连
		//去除广告中的网页链接 -- (https://www.mxgbqg.com/book/91090932/22106863.html) ,每个章节里都有这样的一段话
		$str = str_replace("({$chapter_link})", '', $str);
		if ($title) {
			$str = preg_replace('/喜欢' . $title . '请大家收藏：（）' . $title . '更新度。/', '', $str);
		}
		//思路客的替换
		$str = preg_replace('/思路客小说网 www.siluke520.net，最快更新最新章节！/', '', $str);
		return $str;
	}

	/**
	 * 简单的日志信息输出
	 * @param string $msg
	 */
	public function log($message = "")
	{
		echo "[" . date("Y-m-d H:i:s", time()) . "]--" . $message . "\n";
	}


	/**
	 * @note 根据小说内容获取对应的分类id
	 *
	 * @param $cate_name str分类名称
	 * @return string
	 */

	public static function getNovelCateId($cate_name = '')
	{
		$cate_list = self::getCateConf();
		if (!$cate_list)
			return false;
		$cate_id = 0;
		    
		foreach ($cate_list as $key => $category_id) {
			//根据标签的关键字来进行匹配分类
			if (strstr($cate_name, $key)) {
				$cate_id = $category_id;
				break;
			}
		}
		return $cate_id;
	}


	/**
	 * @note 获取小说的路径
	 *
	 * @param $title string 小说名
	 * @param $author string 作者
	 * @return string
	 */
	public static function getBookFilePath($title = "", $author = "")
	{
		if (!$title || !$author) {
			return false;
		}
		//特殊判断下
		$md5_str = NovelModel::getAuthorFoleder($title, $author);
		if ($md5_str) {
			//拼装组织的路径信息
			$save_path = Env::get('SAVE_JSON_PATH') . DS . substr($md5_str, 0, 2) . DS . $md5_str . '.' . NovelModel::$json_file_type;
			if (!file_exists(dirname($save_path))) {
				createFolders(dirname($save_path));
			}
			return $save_path;
		} else {
			return false;
		}
	}

	/**
	 * @note 获取远程图片
	 *
	 * @param $cate_name str分类名称
	 * @return string
	 */
	public static function curl_file_get_contents($durl)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $durl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		// curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
		curl_setopt($ch, CURLOPT_REFERER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$r = curl_exec($ch);
		curl_close($ch);
		return $r;
	}


	/**
	 * @note 远程抓取图片保存到本地
	 *
	 * @param $url string  图片地址的url
	 * @param $title string 小说标题
	 * @param $author string 作者
	 * @param $pinyin_class object 拼音转英文翻译类
	 * @return string
	 */
	public static function saveImgToLocal($url, $title = '', $author = '', $pinyin_class = '')
	{
		if (!$url) {
			return false;
		}
		$save_img_path = Env::get('SAVE_IMG_PATH');
		//转换标题和作者名称按照第一个英文首字母保存
		if ($title && $author) {
			$imgFileName = self::getFirstImgePath($title, $author, $url, $pinyin_class);
		} else {
			//默认从规则里url取
			$t = explode('/', $url);
			$imgFileName = end($t);
		}
		$monthDay = date('Ym');
		$filename = $save_img_path . DS . $monthDay . DS . $imgFileName;
		createFolders(dirname($filename)); //以每周的日期创建目录
		//判断文件是否存在，如果不存在就直接保存到本地
		$save_img_path = Env::get('SAVE_IMG_PATH');
		if (!is_dir($save_img_path)) {
			createFolders($save_img_path);
		}
		    
		//判断文件是否存在或者文件损坏
		if (!file_exists($filename)  || !@getimagesize($filename)) {
			//先判断远程文件是否存在，如果不存在给一个默认的
			if(!check_url($url)){
				$url = Env::get('APICONFIG.DEFAULT_PIC');
			}
			//匹配台湾的网站，用挂代理的方式进行访问
			//正常的请求url
			$res = webRequest($url, 'GET'); //利用图片信息来下载
		    if(strstr($res, 'File or directory not found') || $res == ""){
		    	$res = webRequest(Env::get('APICONFIG.DEFAULT_PIC'),'GET');
		    }
			$img_con = $res ?? '';
			@writeFileCombine($filename, $img_con);
		}
		return $filename;
	}

	/**
	 * @note 解析当前的参数配置信息和设置信息
	 *
	 * @param $story_link array  网站的源URL
	 * @return string
	 */

	public static function parseXugesData($data, $url)
	{
		if (!$data || !$url) {
			return	false;
		}
		//远端的基本URL地址
		$remote_url = preg_replace('/index\.htm/', '', $url);
		foreach ($data as $key => &$val) {
			if (in_array($key, ['title', 'author', 'cover_logo', 'intro', 'location'])) {
				$val = array_iconv($val); //自动转换编码信息
			}
			if ($key == 'cover_logo' && empty($val)) {
				$val = 'fm.jpg'; //这个是默认的，每次都需要拼接
			}
			if ($key == 'author') $val = str_replace('作者：', '', $val); //处理作者
			if ($key == 'intro') $val = trimBlankSpace($val); //处理空格
			if ($key == 'cover_logo') $val = $remote_url . $val; //处理图片的前缀问题
			if ($key == 'intro') $val = filterHtml($val);
		}
		return $data;
	}

	/**
	 * @note 获取网站的url的配对的源
	 *
	 * @param $story_link array  网站的源URL
	 * @return string
	 */
	public static function getSourceUrl($story_link = '')
	{
		if (!$story_link) {
			return '';
		}
		$url = parse_url($story_link);
		$source = '';
		if (isset($url['host']) && !empty($url['host'])) {
			//匹配以www.开头的到.结束的为当前的来源
			//比如www.baidu.com 只取baidu为当前的标识来源
			preg_match('/www\.(.*?)\./', $url['host'], $matrix);
			if (isset($matrix[1])) {
				$source = trim($matrix[1]);
			}
		}
		if (!$source) {
			$source = 'unknow'; //未定义的或者未知的
		}
		return $source;
	}

	/**
	 * @note 对返回的数组做排序
	 *
	 * @param $html_data array  文章列表
	 * @return string
	 */
	public static function sortHtmlData($html_data = [])
	{
		if (!$html_data)
			return [];
		foreach ($html_data as $key => $val) {
			$pathArr = explode('-', $key);
			$num = end($pathArr);
			array_pop($pathArr);
			$path = implode('-', $pathArr);
			$new_data[$path][] = $num;
		}
		//排序计算
		$sortDataBlank = function ($arr) {
			$items = [];
			foreach ($arr as $k => $v) {
				sort($v); //按照数组进行排序设置,以免数据错乱
				$items[$k] = $v;
			}
			return $items;
		};
		//调用构造函数里的排序
		$new_data = $sortDataBlank($new_data);
		$novelList = [];
		if (!empty($new_data)) {
			//按照数组来进行排序。返回数组信息
			foreach ($new_data as $gkey => $gval) {
				foreach ($gval as $k => $v) {
					$index = $gkey . '-' . $v;
					//.echo $index."\r\n";
					if (isset($html_data[$index])) {
						$novelList[$index] = $html_data[$index] ?? '';
					}
				}
			}
		}
		return $novelList;
	}


	/**
	 * @note 获取缓存中的代理里的配置信息
	 *
	 * @return string
	 */
	public static function getProxyItem()
	{
		$proxy_data = getZhimaProxy();
		if (!$proxy_data)
			return false;
		//获取代理的配置信息凭借wget的参数命令
		$proxyauth = $proxy_data['ip'] . ':' . $proxy_data['port'];
		if (isset($proxy_data['username']) && isset($proxy_data['password'])) {
			$proxyauth .= $proxy_data['username'] . ':' . $proxy_data['password'];
		}
		return $proxyauth;
	}

	/**
	 * @note 获取字符的首字母
	 *
	 * @param $title string  小说名称
	 * @param $author string 作者名称
	 * @param $url string URL图片信息
	 * @param $pinyin_class object 实例化对象类
	 * @return string
	 */
	public static function getFirstImgePath($title, $author, $url, $pinyin_class = [])
	{
		if (!$title) {
			return false;
		}
		//如果为空就初始化类，传的话就用本身的增加提高性能利用率
		if (!empty($pinyin_class)) {
			$pinyin = $pinyin_class;
		} else {
			$pinyin = new Pinyin();
		}
		//构造函数
		$trimBlank = function ($arr) use (&$pinyin) {

			//替换空白字符
			$arr = trimAllSpace($arr);
			    
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


		    
		$cover_logo = '';
		if (!empty($url)) {
			    
			$title_string = $trimBlank($title);
			$author_string = $trimBlank($author);
			$imgInfo = pathinfo($url);
			$extension = $imgInfo['extension'] ?? self::$imageType;
			    
			if (!empty($author_string)) {
				//如果作者不为空，进行作者和标题链接
				$cover_logo =  $title_string . '-' . $author_string . '.' . $extension;

			} else {
				//如果作者为空，只计算标题
				$cover_logo =  $title_string . '.' . $extension;
			}
		}
		return $cover_logo;
	}


	/**
	 * @note 获取加密的md5串
	 *
	 * @param $title 小说标题
	 * @param $author string 作者名称
	 * @return string
	 */
	public static function getAuthorFoleder($title = '', $author = '')
	{
		if (!$title) {
			return false;
		}
		$title = trim($title);
		$author = $author ?  trim($author) : '未知';
		return md5($title . $author);
	}

	/**
	 * @note 根据标签名自动匹配tag_id
	 *
	 * @param $tag_name string 标签名称
	 * @return string|unknow
	 */
	public static function getNovelTagByIName($tag_name = '')
	{
		$tag_name = trim($tag_name);
		if (!$tag_name) {
			return false;
		}
		$tagList = self::initTagList();
		if (!$tagList)
			return false;
		$s_tag_id = 0;
		//按照正常的键值交换数据请求
		$tagsArr = double_array_exchange_by_field($tagList, 'id');
		    
		foreach ($tagsArr as $tag_id => $value) {
			//根据标签的关键字来进行匹配分类
			if (strstr($value['tag_name'],$tag_name)) {
				$s_tag_id = $value['id'] ?? 0;
				break;
			}
		}
		$s_tag_id = intval($s_tag_id);
		return $s_tag_id;
	}

	/**
	 * @note 判断小说是否为经典类型
	 *
	 * @param $tid int 标签ID
	 * @return interger
	 */
	private static function isBookClassic($tid = 0)
	{
		$is_classic = 0;
		if (!$tid) {
			return $is_classic;
		}
		$tagList = self::initTagList();
		if (is_array($tagList) && count($tagList) > 0) {
			foreach ($tagList as $tag_id => $value) {
				$column_type = (int) $value['column_type']; //栏目类型 1-推荐 2-热门 3-经典
				//只有满足column_type
				if ($column_type == 3 &&  $value['id'] > 0 && $tid == $value['id']) {
					$is_classic = 1;
					break;
				}
			}
		}
		return $is_classic;
	}

	/**
	 * @note 替换转换一些源的url信息
	 *
	 * @param $url string 连接地址
	 * @return string
	 */
	public static function  handleCollectUrl($url=""){
		if(!$url){
			return false;
		}
		if(strstr($url, 'ipaoshuba')){
			$url = str_replace('Book', 'Partlist', $url);
		}else if(strstr($url, 'banjiashi') && strstr($ur,'xiaoshuo')){//存在佳士而且是首页的时候做替换
			$url = str_replace('xiaoshuo', 'index', $url);
			$url.='1/';
		}
		return $url;
	}


	/**
	 * @note 获取拼接的图片的路径，并以日期创建文件夹
	 *
	 * @param $book_name 名称
	 * @param $title string 作者
	 * @param $cover_url string 小说封面URL
	 * @return string
	 */
	public static function getNovelToPic($title, $author, $cover_logo)
	{
		if (!$title || !$author || !$cover_logo) {
			return false;
		}
		$monthDay = date('Ym'); #获取每周的日期的星期一，以这样子的格式存储
		$save_filename = Env::get('SAVE_IMG_PATH') . DS . $monthDay . DS . self::getFirstImgePath($title, $author, $cover_logo);
		$save_path = dirname($save_filename);
		//判断是否存在该目录
		if (!is_dir($save_path)) {
			createFolders($save_path); //创建文件夹
		}
		return $save_filename;
	}

	/**
	 * @note 转换对应的字段信息并同步数据到mc_book表
	 *
	 * @param $data 预处理的数据
	 * @param $mysql_obj string 连接句柄
	 * @return string
	 */
	public static  function exchange_book_handle($data, $mysql_obj)
	{
		if (!$data)
			return false;
		    
		//先按照源数据进行判断
		$ex_key = [];
		foreach (self::$dict_exchange as $key  => $val) {
			if (!$key)
				continue;
			$ex_key[$key] = 1;
		}
		    

		    
		foreach ($data as $key => $val) {
			if (isset($ex_key[$key])) {
				$info[self::$dict_exchange[$key]] = trim($val);
			}
		}

		$info['tid'] = self::getNovelTagByIName($info['tags']); //根据category获取当前的cid匹配
		$info['cid'] = self::getNovelCateId($info['class_name']); //获取标签tag计算当前的tid匹配
		$info['addtime']  = time();
		$info['author'] = $info['author'] ? trim($info['author']) : '未知';
		$info['book_name'] = trim($info['book_name']); //去除首尾空格
		//判断发hi我饿u为经典书籍
		//处理图片的存储路径
		if ($info['book_name'] || $info['author']) {
			//处理图片的存储路径问题，直接保存对应的按照：中文转换成英文，取英文的首字母，书名+作者的首字母计算返回
			$weekDay = getWeekDate();
			$image_str = self::getNovelToPic($info['book_name'], $info['author'], $info['pic']);
			if (!$image_str) {
				$image_str =  self::$default_pic;
			}
			$info['pic'] = $image_str;
		}
		//处理小说是否完本状态
		if ($info['serialize'] == '连载中') {
			$serialize = 1; //连载
		} else if ($info['serialize'] == '已经完本') {
			$serialize = 2; //完结
		} else {
			$serialize = 3; //太监
		}

		$info['tag_name'] = $info['tags']; //标签名称
		//判断该书是否为经典
		$info['is_classic'] = NovelModel::isBookClassic($info['tid']);
		$now_time = date('Y-m-d 00:00:00');
		//目前判断是更新时间大于当前系统时间的都算新书
		if (($info['last_chapter_time'] > 0 &&  $info['last_chapter_time'] > strtotime($now_time)) || $data['status'] == '新书上传') {
			$info['is_new'] = 1; //判断是否为新书
		}
		if(!isset($info['text_num'])){
			$info['text_num'] = self::getTextNum($info['book_name'], $info['author']); //小说字数
		}
		$info['serialize'] = $serialize;
		$info['category_name'] = $info['tags']; //小说原网站分类名称
		$score = getScoreRandom(); //随机8-10分的书
		$score = round($score, 1);
		$info['score'] = $score; //随机小数评分
		$info['book_type'] = 1; //默认就男生的书籍同步
		$info['read_count'] = rand(10, 100000); //最新阅读数
		$info['hits'] = rand(1000, 10000); //浏览数量
		$info['hits_month'] = rand(10000, 20000); //月点击
		$info['hits_week'] = rand(5000, 10000); //周点击
		$info['hits_day'] = rand(100, 5000); //日点击
		$info['shits'] = rand(100, 300); //收藏人气
		$info['read_count']  = rand(1000, 5000);
		$info['search_count'] = rand(100, 599); //搜索总次数
		self::$table_name = Env::get('TABLE_MC_BOOK'); //获取配置信息
		//判断是否为佳士小说特殊处理下url
		// if (strpos($info['source_url'], 'banjiashi')) {
		// 	#替换连接地址
		// 	$source_url = BanjishiModel::replaceTrueUrl($info['source_url']);
		// 	$source_url && $info['source_url'] = $source_url;
		// }else if(strpos($info['source_url'], 'ipaoshuba') || strpos($info['source_url'], 'paoshu8')) {//泡书吧的走这个判断
		// 	$source_url = NovelModel::replaceIpaoshubaUrl($info['source_url']);
		// 	$source_url && $info['source_url'] = $source_url;
		// 	// //判断是存在标题和作者有修改的情况
		// 	$oldData = NovelModel::getOnlineBookInfo($source_url);
		// 	if(
		// 		$oldData && 
		// 			( 
		// 				$oldData['book_name']!=$info['book_name']
		// 			|| 	$oldData['author']!=$info['author']
		// 			)
		// 		){
		// 		$condition = "id = {$oldData['id']}";
		// 		//存在差异就用库里的，因为已经生成记录了
		// 		$update_data['update_chapter_time'] = time();
		// 		echo "url = {$info['source_url']}\t （ 远程数据 book_name=【{$info['book_name']}】 |author=【{$info['author']}】 ）"."和 （本地book_name=【{$oldData['book_name']} 】|author=【{$oldData['author']}】 ）不一致，数据异常 ，无法更新\r\n";
		// 		//执行删除操作，匹配最关联的数据信息
		// 		$sql = "delete from ".self::$table_name." where book_name ='{$oldData['book_name']}' and author ='{$oldData['author']}' limit 1";
		// 		echo "delete sql = {$sql} \r\n";
		// 		// $mysql_obj->update_data($update_data, $condition, self::$table_name, false, 0, self::$db_conn);
		// 		$mysql_obj->query($sql,self::$db_conn);
		// 		// NovelModel::killMasterProcess();
		// 		// exit;
		// 	}
		// }


		////获取每次章节的最后一个更新的ID和名称
		// $md5_str = NovelModel::getAuthorFoleder($info['book_name'], $info['author']);
		// $json_file = Env::get('SAVE_JSON_PATH') . DS .substr($md5_str, 0,2).DS. $md5_str .  '.' . NovelModel::$json_file_type;
		$json_file = NovelModel::getBookFilePath($info['book_name'], $info['author']);
		
		$json_data = readFileData($json_file);
		$chapter_item = json_decode($json_data, true);
		$info['chapter_num'] = count($chapter_item);
		if (!empty($chapter_item)) {
			//获取最后一个数组元素
			$return = array_slice($chapter_item, -1, 1);
			$infoArr = $return[0] ?? [];
			$info['update_chapter_id'] = $infoArr['id'] ?? 0; //最后一次更新的章节ID
			$info['update_chapter_title'] = addslashes($infoArr['chapter_name']) ?? ''; //最后一次更新的章节名称
			$info['update_chapter_time'] = time(); //最后的更新时间
		}
		//根据书籍名称和坐着来进行匹配
		$where_data = 'book_name ="' . $info['book_name'] . '" and author ="' . $info['author'] . '" limit 1';
		$sql = "select id from " . self::$table_name . " where {$where_data}";
		$novelInfo = $mysql_obj->fetchAll($sql, self::$db_conn);
		    
		if (empty($novelInfo)) {
			//插入入库
			$data =  handleArrayKey($info);
			$id =  $mysql_obj->add_data($data, self::$table_name, self::$db_conn);
		} else {
			//如果是这本书存在了，就不更新图片了，防止编辑上传了图片又被覆盖掉了。
			if(isset($info['pic'])){
				unset($info['pic']);
			}
			//更新书籍的主要信息
			$update_where = "id =" . $novelInfo[0]['id'];
			unset($info['addtime']);
			//更新对应的配置信息
			// $t = $mysql_obj->update_data($info, $update_where, self::$table_name, false, 0, self::$db_conn);
			$id = intval($novelInfo[0]['id']);
		}
		    
		return $id;
	}



	/**
	 * @note 获取小说的基本信息
	 *
	 * @param $source_url sring 目标来源
	 * @return object
	 */
	public static function getOnlineBookInfo($source_url){
		if(!$source_url){
			return false;
		}
		global $mysql_obj;
		$urlData = parse_url($source_url);
		$path = $urlData['path'] ?? '';
		if($path){
			$path = str_replace('Partlist','Book',$path);
			$sql = "select id ,book_name,author from " . Env::get('TABLE_MC_BOOK') . " where instr(source_url,'{$path}')>0";
			$info = $mysql_obj->fetch($sql,self::$db_conn);
			return $info;
		}
	}


	/**
	 * @note 替换分页中的url信息返回
	 *
	 * @param $url array 连接地址
	 * @return string
	 */
	public static function replaceIpaoshubaUrl($url = '')
	{
		if (!$url) {
			return false;
		}
		$source_url = preg_replace('/Partlist/', 'Book', $url);
		return $source_url;
	}
	/**
	 * @note 获取小说的的字数
	 *
	 * @param $data 预处理的数据
	 * @param $mysql_obj string 连接句柄
	 * @return string
	 */
	public static function getTextNum($title, $author)
	{
		if (!$title || !$author) {
			return 0;
		}
	 	$json_path = NovelModel::getBookFilePath($title, $author);
		$info = readFileData($json_path);
		$num = 0;
		if ($info) {
			$t = json_decode($info, true);
			$num  = self::$text_num * count($t);
		}
		return $num;
	}

	/**
	 * @note 清洗掉不需要的字段
	 *
	 * @param $items array 章节列表
	 * @param $fileter_key interer 章节类目
	 * @return array
	 */
	public static function cleanArrayData($items = [], $filter_key = [])
	{
		if (!$items || !$filter_key) return [];
		$list = [];
		foreach ($items as $key => $val) {
			$info = [];
			foreach ($val as $k => &$v) {
				//如果在过滤的字段里，直接切除
				if (!in_array($k, $filter_key)) {
					$info[$k] = $v;
				}
			}
			$list[$key] = $info;
		}
		return $list;
	}


	/**
	 * @note 创建生成本地的json文件
	 *
	 * @param $data 预处理的数据
	 * @param $pro_book_id int 线上小说ID-暂时不用，应该是废弃了
	 * @param $referer_url string 采集的域名，不带后面的path和/路径
	 * @param $url string url信息 带全路径的信息
	 * @return string
	 */
	public static function createJsonFile($info = [], $data = [], $pro_book_id = 0, $referer_url = '',$url)
	{
		if (!$data || !$info) {
			return false;
		}

		// echo "<pre>";
		// var_dump($info);
		// echo "</pre>";
		// echo md5('小蘑菇'.'一十四洲');
		// exit();
		    
		//获取标题+文字的md5串
		$md5_str = self::getAuthorFoleder($info['title'], $info['author']);
		if (!$md5_str) {
			return false;
		}
		/*
    $data[] = [
                'id'    =>$key+1 ,
                'sort'  =>$key+1,
                'chapter_link'  =>$val['link_url'],
                'chapter_name'  =>$val['link_name'],
                'vip'   =>  0,
                'cion'  =>  0,
                'is_first' =>   0,
                'is_last'   => 0,
                'text_num'  => 2000,
                'addtime'   =>$val['createtime'],
            ];

     */
		$json_list = [];
		    
		foreach ($data as $key => $val) {
			//特殊判断下,由于这个站采集没有/前面的路由
			if(strstr($url,'siluke520')){
				$chapter_link = sprintf("%s%s",$url , $val['link_url']);
			}else{
				//判断是否以http开头的，如果不是就需要拼接后缀
				if (!preg_match('/http.*:\/\//', $val['link_url'])) {
					$chapter_link = $referer_url . $val['link_url'];
				} else {
					$chapter_link = $val['link_url'];
				}
			}
			//配置指定的json地址信息
			$json_list[] = [
				'id'    => $key + 1, //ID
				'sort'  => $key + 1,   //排序
				'chapter_link'  => $chapter_link, //抓取的地址信息
				'chapter_name'  => $val['link_name'], //文件名称
				'vip'   =>  0, //是否为VIP
				'cion'  =>  0, //章节标题
				'is_first' =>   0, //是否为首页信息
				'is_last'   => 0, //是否为最后一个
				'text_num'  => rand(3000, 10000), //随机生成文本字数统计
				'addtime'   => (int) $val['createtime'], //添加时间
			];
		}
		    
		    
		$save_path = Env::get('SAVE_JSON_PATH') . DS . substr($md5_str, 0, 2); //保存json的路径
		    
		//获取对应的json目录信息
		if (!is_dir($save_path)) {
			createFolders($save_path);
		}
	
		$filename = NovelModel::getBookFilePath($info['title'], $info['author']);
		echo $filename."\r\n";
		// $filename = $save_path . DS . $md5_str . '.' . self::$json_file_type;
		//保存对应的数据到文件中方便后期读取
		$json_data = json_encode($json_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		writeFileCombine($filename, $json_data); //把json信息存储为对应的目录中去
		return $json_list;
	}

	/**
	 * @note 解析当前url的域名
	 * @param $url string url地址
	 * @return array
	 */
	public static function urlHostData($url)
	{
		if (!$url)
			return false;
		$hostData = parse_url($url);
		$referer_url = $hostData['scheme']  . '://' . $hostData['host'];
		return $referer_url;
	}

	/**
	 * @note 自动补齐当前的buffer数据信息
	 * @param $detail array 抓取到的匹配数据
	 * @param $content string HMTL内容返回的数据
	 * @return array
	 */
	protected static function bufferMetaData($detail = [], $content = [])
	{
		if (!$detail &&  !$content) {
			return false;
		}
		// if(preg_match('/otcwuxi/',$content)){ //校验锡海小说网
		//       if(empty($detail['meta_data']) || empty($detail['href'])){
		//         //匹配这一段开头的数据jumppage到</heade>中间的内容
		//          preg_match('/document\.onkeydown=jumpPage.*?<\/head>/ism',$content,$contentarr);
		//          $html = $contentarr[0] ?? '';
		//          if($html){
		//              //通过正则取出来对应额连接
		//               $meta_reg = '/zzurl=\'(.*?)\'/si'; //meta_data信息
		//               $link_reg  = '/bookurl=\'(.*?)\'/si' ; //href的主要信息
		//               preg_match($meta_reg,$html,$meta_link);
		//               preg_match($link_reg , $html,$href_link);
		//               $detail['meta_data'] = $meta_link[1] ?? '';//获取meta的基础信息
		//               $detail['href'] = $href_link[1] ?? '';
		//          }
		//       }
		// }
		$reg = '/lastread\.set.*<\/script>/';
		preg_match($reg, $content, $matches);
		$results = '';
		if (isset($matches[0])) {
			$list = $matches[0] ?? '';
			preg_match('/\"\,\"\/chapter\/.*\.html/', $list, $chapterMatches);
			$results = $chapterMatches[0] ?? '';

			$results = str_replace('"', '', $results);
			$results = str_replace(',', '', $results);
		}
		$detail['meta_data'] = $results;

		return $detail;
	}

	/**
	 *  @note 获取对应的请求数据信息
	 * @param $data array 需要处理的章节列表
	 * @param $txt_path string 存储的路径
	 * @return array
	 */
	public static function getDataListItem($title = '', $data, $txt_path)
	{
		if (!$data)
			return false;
		$chapterList = [];
		$firstData = reset($data);
		$chapter_link_ref = $firstData['chapter_link'] ?? '';
		//后去当前的链接地址的来源
		// $source = self::getSourceUrl($chapter_link_ref);
		$source_ref = NovelModel::getSourceUrl($chapter_link_ref); //获取网站来源
		#特殊渠道判断处理流程
		if ($source_ref == 'banjiashi') {
			return BanjishiModel::getChapterAllList($data, $txt_path, $title);
		} else if ($source_ref == 'douyinxs') {
			return DouyinModel::getDouyinChapterAllList($data, $txt_path, $title);
		}

		foreach ($data as $key => $val) {
			//获取请求的域名
			$referer_url = self::urlHostData($val['chapter_link']);
			//章节的连接
			$mobilePath = $val['link_url'] ?? '';
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


		global $urlRules;
		//获取采集的标识

		$is_fanti_ex = $source_ref != 'twking' ? false: true;
		#获取采集规则
		$rules = CommonService::collectContentRule($chapter_link_ref);
		    
		//开启多线程请求,使用当前代理IP去请求，牵扯到部署需要再境外服务器
		//////////////////处理请求的链接start
		$list = StoreModel::swooleRquest($t_url);
		    
		//重试防止有错误的
		$list = StoreModel::swooleCallRequest($list, $chapterList, $referer_url);
		if (!$list) {
			return false;
		}
		$exchagne_arr  = [
			'otcwuxi',
			'biquge5200',
		]; //配置需要转换的URL
		
		foreach ($list as $gkey => $gval) {
			//非跑书8需要做转换
			if ($source_ref == 'xuges') {
				//由于https://www.xuges.com这个网站取法用querylist抓取后会断章，考虑下还是用正则来进行抓取吧，方便管理
				// $html = array_iconv($gval);
				// preg_match('/<tr><td class=\"jz\">.*?<tr><td class=\"zhx\">/ism', $html, $matches);
				$html = $gval; //重复赋值，方便下面的逻辑流程跑通
				preg_match('/<tr><td>.*?<tr><td class=\"zhx\">/ism', $html, $matches);
				$store_content = $matches[0] ?? '';
			} else {
				//指定key进行采集
				$data = QueryList::html($gval)
					->rules($rules)
					->query()
					->removeHead()
					->getData();
				$html = $data->all();
				//处理是否为繁体转换，如果是就需要对相关联的进行转换
				if ($is_fanti_ex) {
					$html = StoreModel::traverseEncoding($html);
				}
				$store_content = $html['content']  ?? '';
				//判断来源是否转换
				if ($source_ref!='paoshu8' && in_array($source_ref, $exchagne_arr)) {
					// $store_content = iconv('gb2312', 'utf-8//ignore', $store_content);
					$store_content = array_iconv($store_content); //转换编码，牵扯到转换编码的统一调用array_iconv函数，方便进行管理，修改也容易更好找到地方
				}
			}

			//替换内容里的广告
			$store_content = NovelModel::replaceContent($store_content, $referer_url, $gkey, 12);
			//如果确实没有返回数据信息，先给一个默认值
			if (!$store_content || empty($store_content)) {

				echo "当前章节内容有空内容哦~~~ \r\n";
				$store_content = '未完待续...';
			}
			if ($store_content) {
				$store_content = str_replace(array("\r\n", "\r", "\n"), "", $store_content);
				//替换文本中的P标签
				$store_content = str_replace("<p>", '', $store_content);
				$store_content = str_replace("</p>", "\n\n", $store_content); //如果内容是P标签，用P来替换
				$store_content = str_replace("<br>", "\n\n", $store_content); //如果内容是br标签用br来进行替换
				$store_content = str_replace("<br />", "\n\n", $store_content); //如果内容是br /标签用br来进行替换
				//替换try{.....}cache的一段话JS这个不需要了,还有一些特殊字符
				$store_content = preg_replace('/{([\s\S]*?)}/', '', $store_content);
				$store_content = preg_replace('/try\scatch\(ex\)/', '', $store_content);
				$store_content = preg_replace('/content1()/', '', $store_content);
			}
			// $store_detail[$html_path] = $store_content;
			$store_detail[$gkey] = $store_content;
		}
		    
		$allNovelList = [];
		if (!empty($store_detail)) {
			foreach ($store_detail as $gtkey => $gtval) {
				//获取对应的键值数据信息
				$chapter_info = isset($chapterList[$gtkey]) ? $chapterList[$gtkey] : '';
				$results = array_merge($chapter_info, ['content' => $gtval ?? '']);
				$allNovelList[$gtkey] = $results ?? [];
			}
		}
		return $allNovelList;
	}


	/**
	 * @note 从远程获取相关内容并存储到缓存里
	 *
	 * @param $url string 链接地址
	 * @param $timeout stirng 缓存的超时时间
	 * @param $tag string 缓存标记
	 * @return string
	 */
	public static function getRemoteHmtlToCache($url, $tag = '', $timeout = 0)
	{
		if (!$url || !$tag) {
			return '';
		}
		if (!is_array($url)) {
			$t_url[] = $url;
		} else {
			$t_url = $url;
		}
		global $redis_data;
		$content = $redis_data->get_redis($tag);
		if (!$content) {
			//使用代理获取数据
			$info = MultiHttp::curlGet($t_url, null, true);
			$content = $info[0] ?? ''; //获取内容信息
			$redis_data->set_redis($tag, $content, $timeout);
		}
		return $content;
	}





	/**
	 * @note 获取移动端的组装的链接
	 *
	 * @param $data str array处理的数据
	 * @return $html str 抓取的url 返回抓取后的curl请求连接
	 */
	public static function getMobileHtmlUrl($meta_data, $html)
	{
		if (!$meta_data || !$html)
			return false;
		$path = substr($meta_data, 0, -1);
		$page_num = 0;
		$con_str = preg_split('/<\/p>/', $html); //按照P标签切割函数
		$pages = str_replace("\r\n", '', $con_str[0]); //替换里面的空行
		$content = filterHtml($pages); //过滤特殊字符
		$t = explode('/', $content); // (第1/3页) 替换
		if (!$t)
			return [];
		preg_match('/\d/', $t[1], $allPage);
		$all_num = intval($allPage[0]); //总的页码数量，需要判断是否有1页以上


		if (isset($t[0]) && !empty($t[0])) {
			$info = explode('(', $t[0]); //按照(来分割字符
			$lastElement = array_pop($info); //只取最后一个,如果存在多个(匹配会有问题
			if (isset($lastElement) && !empty($lastElement)) {
				//匹配含有字符的数据内容
				preg_match('/\d/', $lastElement, $matches);
				if (isset($matches[0])) {
					$page_num = intval($matches[0]);
				}
			}
		}
		//组装移动端内容的数据
		$mobile_url = $path . '-' . $page_num;
		return $mobile_url;
	}


	/**
	 * @note curl抓取远程章节类目并组装数据
	 *
	 * @param $data str array处理的数据
	 * @return 返回抓取后的curl请求连接
	 */

	public static function getHtmlData($data)
	{
		global $urlRules;
		if (!$data)
			return false;
		$getUrl = [];
		foreach ($data as $key => $val) {

			//处理连接地址有的部分是处理过url了
			$link_url = $val['mobile_url'] ?? ''; //移动端的url
			$link_name = $val['link_name'];
			$tdk = parse_url($link_url);
			$link_str = $tdk['path'] ?? '';
			// if( !strstr($val['link_url'] , Env::get('APICONFIG.PAOSHU_HOST')) ){
			//     $link_url = Env::get('APICONFIG.PAOSHU_HOST') .$val['link_url'];
			// }else{
			//      $link_url =  $val['link_url'];
			// }
			$item[$link_str] = $val;
			$item[$link_str]['link_name'] = $link_name;
			$getUrl[] = $link_url;
		}
		$getUrl = array_unique($getUrl); //防止url重复
		$list = guzzleHttp::multi_req($getUrl, 'empty'); //不适合含有404的页面会阻断程序
		// $list = curl_pic_multi::Curl_http($t_url,3);
		$list = array_filter($list ?? []);
		if (!$list)
			return [];
		//获取对应的curl的信息
		// $shell_cmd = NovelModel::getCommandInfo($item);
		// if(!$shell_cmd)
		//   return [];
		// //执行对应的shell命令获取对应的curl请求
		// $string = shell_exec($shell_cmd);
		// //防止返回空数据的情况，做特殊判断
		// if(!$string){
		//     return [];
		// }
		//按照正则来切割shell返回的内容
		// $s_content=preg_split('/<\/html>/', $string);
		// //过滤空的数组
		// $s_content =array_map('trim', $s_content);
		// //处理过滤账号信息
		// $list = array_filter($s_content);
		//获取采集的标识
		$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
		// $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
		$store_c =  [];
		foreach ($list as $key => $val) {
			//<script id="ab_set_2">ab_set_2();</script>
			$data = QueryList::html($val)->rules($rules)->query()->getData();
			$html = $data->all();
			$store_content = $html['content'] ?? '';
			$meta_data = $html['meta_data'] ?? '';
			// $href = $html['href'] ?? '';
			//获取拼装的html路径信息
			// $html_path = getHtmlUrl($meta_data,$href);
			//获取当前的url信息
			$html_path = self::getMobileHtmlUrl($meta_data, $store_content);
			// echo $html_path.PHP_EOL;
			//判断如果不是503返回且无内容 并且确实这个章节没内容
			//如果是404直接跳出去
			if (strstr($val, '您请求的文件不存在')) {
				continue;
			}

			//非503为空访问
			//503 Service
			//请求失败
			if (!strstr($val, '请求失败') && !$store_content) {
				$store_content = '此章节作者很懒，什么也没写';
			}

			if ($store_content) {
				//处理剔除第一行的标题显示和相关广告
				$store_content = self::removeLineData($store_content);
				$store_content = str_replace(array("\r\n", "\r", "\n"), "", $store_content);
				//替换文本中的P标签
				$store_content = str_replace("<p>", '', $store_content);
				$store_content = str_replace("</p>", "\n\n", $store_content);
				//替换try{.....}cache的一段话JS这个不需要了
				$store_content = preg_replace('/{([\s\S]*?)}/', '', $store_content);
				$store_content = preg_replace('/try\scatch\(ex\)/', '', $store_content);
			}
			$store_c[$html_path] = $store_content;
		}
		// echo '<pre>';
		// print_R($store_c);
		// echo '</pre>';
		// exit;
		//组装contents的内容
		foreach ($item as $k => $v) {
			$item[$k]['content'] = isset($store_c[$k])  ? $store_c[$k] : '';
		}
		//获取对象的内容信息
		$return_list = self::reloadMultiCon($item);
		//还原数据
		$arr_list = array_values($return_list);
		//需要单独处理下内容的切割
		return $arr_list;
	}

	/**
	 * @note 剔除第一场的数据不要移动端的标题
	 *
	 * @param $content str 保存的内容设置
	 * @param $tag str 设置按照什么进行切割
	 * @return string
	 */
	public static function removeLineData($content = '')
	{
		if (!$content)
			return '';
		//按照正则p的标签七个开相关的信息
		preg_match_all('/<p.*?>.*?<\/p>/ism', $content, $matches);
		if (isset($matches[0]) && !empty($matches[0])) {
			$list = $matches[0];
			//移除第一行的字样 类似“光明皇帝 (第3/3页)”这种不需要出现在正文中
			if (isset($list[0])) {
				array_shift($list);
			}
			if (!$list) return ''; //防止有空数据
			$item = [];
			foreach ($list as $key => $val) {
				//只判断最后一行字是否存在这些字样
				if (!strstr($val, '本章未完，请点击下一页继续阅读') || !strstr($val, '点击下一页继续阅读')) {
					$item[] = $val;
				}
			}
			if (!$item) return '';
			//还原文章信息
			$return_content = implode('', $item);
			return $return_content;
		} else {
			return '';
		}
	}


	/**
	 * @note 自动把移动端的内容分割成一个对象中
	 *
	 * @param $data str 待保存的数据
	 * @return 返回抓取后的curl请求连接
	 */
	protected static function reloadMultiCon($data)
	{
		if (!$data)
			return [];
		$list = $contents = [];
		foreach ($data as $key => $val) {
			$link_url = $val['link_str']; //指定文章的key内容
			$contents[$link_url][] = $val['content'];
			if (isset($val['content']))   unset($val['content']);
			$list[$link_url] = $val;
		}
		//组装内容，这里比较特殊需要处理下
		foreach ($list as $k => $v) {
			$content_str = '';
			if (isset($contents[$k])) {
				$content_str = implode('', $contents[$k]);
			}
			$list[$k]['content'] = $content_str;
		}
		return $list;
	}



	/**
	 * @note 保存本地文件信息
	 *
	 * @param $data str 待保存的数据
	 * @return 返回抓取后的curl请求连接
	 */
	public static function saveLocalContent($data)
	{
		if (!$data)
			return false;
		$y = $n = 0;
		foreach ($data as $key => $val) {
			if (!$val)
				continue;
			if ($val['content']) {
				$y++;
				echo  "num：" . ($key + 1) . " chapter_name = {$val['link_name']}\t -- success  file to loal path：{$val['file_path']} \r\n";
				//同步写入文件，移动端由于数据被分割成N*M个URL后，可能一篇小说会同时出现在上下分页对应的数据中，所以必须追加，不然会按照最新的显示
				$a = writeFileAppend($val['file_path'], $val['content']);
			} else {
				$n++;
				echo "num：" . ($key + 1) . " chapter_name = {$val['link_name']} --  error  " . $val['link_url'] . " no data\r\n";
			}
		}
		echo "this times success_num：" . $y . " error_num：" . $n . PHP_EOL;
	}

	/**
	 * @note 重复调用请求，防止有空数据返回做特殊调用
	 *
	 * @param $content_arr array  请求的HTML数据
	 * @param $goods_list array 原始请求的校验数据
	 * @param $type string 验证类型 curl:curl请求验证 ghttp:采用guzzlehttp来验证
	 * @param $type 获取移动端的代理配置 4：列表的代理 2：统计移动端页面的代理 3：修补空数据的代理
	 * @return unnkower
	 */
	public static function callRequests($contents_arr = [], $goods_list = [], $type = '', $proxy_type = '')
	{
		if (!$contents_arr || !$goods_list)
			return [];
		if (!in_array($type, self::$validate_type)) {
			$type = 'curl'; //默认采用curl来验证
		}
		$goods_list = array_values($goods_list);
		//获取出来成功和失败的数据
		$returnList = NovelModel::getErrSucData($contents_arr, $goods_list, $type);
		// echo '<pre>';
		// print_R($returnList);
		// echo '</pre>';
		// exit;
		//取出来成功和失败的数据
		$sucData = $returnList['sucData'] ?? []; //成功的数据
		$errData = $returnList['errData'] ?? []; //失败的数据
		echo "success_num：" . count($sucData) . "\terror_num：" . count($errData) . PHP_EOL;
		$repeat_data = $curl_contents1 = [];
		if (!empty($errData)) {
			$successNum = 0;
			$old_num = count($errData);
			$urls = array_column($errData, 'mobile_url'); //进来先取出来
			while (true) { //连接总数和请求成功数量不一致轮训
				//重新请求对应的信息
				$curl_contents1 = curl_pic_multi::Curl_http($urls, $proxy_type);
				// echo "================11111\r\n";
				$temp_url = []; //设置中间变量，如果是空的，就需要把对应的URL加到临时变量里
				foreach ($curl_contents1 as $tkey => $tval) {
					//防止有空数据跳不出去,如果非请求失败确实是空，给一个默认值
					if ($type == 'ghttp') { //ghttp验证方式
						//判断返回页面里不为空的情况，还要判断是否存在异常 如果不是200会返回guzzle自定义错误根据这个来判断
						if (empty($tval)) { //为空的情况
							echo "章节数据内容为空，会重新抓取======================{$urls[$tkey]}\r\n";
							$temp_url[] = $urls[$tkey];
						} else if (!preg_match('/id="content"/', $tval)) { //断章处理，包含有502的未响应都会
							echo "有断章，会重新抓取======================{$urls[$tkey]}\r\n";
							$temp_url[] = $urls[$tkey];
						} else {
							$repeat_data[] = $tval;
							unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
							unset($curl_contents1[$tkey]);
							$successNum++;
						}
					} else if ($type == 'curl') { //采用curl来验证
						if (empty($tval)) { //为空的情况
							echo "章节数据内容为空，会重新抓取======================{$urls[$tkey]}\r\n";
							$temp_url[] = $urls[$tkey];
						} else {
							$repeat_data[] = $tval;
							unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
							unset($curl_contents1[$tkey]);
							$successNum++;
						}
					}
				}

				$must_count = count($temp_url);
				if ($must_count > 0) {
					echo "---------------当前有（" . $must_count . "）个URL需要重新去获取\r\n";
				}
				$urls = $temp_url; //起到指针的作用，每次只存失败的连接
				$urls = array_values($urls); //重置键值，方便查找
				$curl_contents1 = array_values($curl_contents1); //获取最新的数组
				//如果已经满足所有都取出来就跳出来
				if ($old_num == $successNum) {
					echo "数据清洗完毕等待入库\r\n";
					break;
				}
				// sleep(1);
			}
		}
		//合并最终的需要处理的数据
		$finalData = array_merge($sucData, $repeat_data);
		return $finalData;
	}

	/**
	 * @note 保存HTML实体到指定目录
	 *
	 * @param $noveList arrya 小说列表
	 * @return bool
	 */
	public static function saveDetailHtml($novelList = [])
	{
		if (!$novelList) {
			return false;
		}
		$combineData = [];
		$cache_path = Env::get('SAVE_HTML_PATH');
		foreach ($novelList as $key => $val) {
			$urlPath  = $cache_path . DS . 'detail_' . $val['story_id'] . '.' . NovelModel::$file_type;
			//只有检测需要同步的数据才去保存
			if (!file_exists($urlPath)) {
				$combineData[$urlPath] = $val;
			}
		}
		if (!$combineData) return true;
		$urls = array_column($combineData, 'story_link');
		//获取关联的数据信息
		$list = curl_pic_multi::Curl_http($urls);
		//重复获取数据防止有漏掉的
		$list = NovelModel::callMultiListRquests($list,  $novelList);

		if (empty($list)) {
			return false;
		}
		global $urlRules;
		//指定的规则
		$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['detail_url'];
		$store_content = [];
		foreach ($list as $key => $val) {
			$data = QueryList::html($val)
				->rules($rules)
				->query()
				->getData();
			$content = $data->all();
			$story_id = $content['path'] ?? '';
			$story_id = str_replace('/', '', $story_id);
			//写入指定的文件信息
			$file_path  = $cache_path . DS . 'detail_' . $story_id . '.' . NovelModel::$file_type;
			if (!empty($val)) {
				$store_content[$file_path] = $val;
			}
			// writeFileCombine($file_path,$val); //写入文件操作
			// echo "path = $path \t";
		}
		if (!$store_content) return false;
		echo "=========================同步文件到指定缓存目录 {$cache_path}\r\n";
		$i = 0;
		foreach ($combineData as $k => $v) {
			$i++;
			$content = $store_content[$k] ?? '';
			echo "num =" . ($i + 1) . "\ttitle = {$v['title']} \t author = {$v['author']}\t url = {$v['story_link']} path = {$k} \tHTML页面缓存成功\r\n";
			writeFileCombine($k, $content); //自动写入追加文件的HTML页面

		}
		return true;
	}


	/**
	 * @note 获取小说详情记录
	 *
	 * @param $story_id string  第三方网站的id
	 * @param $source string 来源 paoshu8：泡书吧 xsw：台湾小说
	 * @return unnkower
	 */
	public static function getNovelInfoById($story_id = '', $source = '', $field = 'store_id,title,author,note')
	{
		if (!$story_id || !$source) {
			return false;
		}
		$sql = "select {$field} from " . Env::get('APICONFIG.TABLE_NOVEL') . " where story_id='{$story_id}' and source='{$source}'";
		// echo "sql  = [{$sql}]\r\n";
		global $mysql_obj;
		$info = $mysql_obj->fetch($sql, 'db_slave');
		return !empty($info) ? $info : [];
	}

	/**
	 * @note 根据小说名+作者获取详情信息
	 *
	 * @param $title string  标题
	 * @param $author stirng 作者
	 * @param $field string  指定字段信息
	 * @return unnkower
	 */
	public static function getNovelByName($title = '', $author = '', $field = 'store_id,title,author,story_link')
	{
		if (!$title || !$author) {
			return  false;
		}
		//整体思路：先从book_cener表中去搜索是否存在有此本小说
		//假如book_center表中存在此本小说就返回
		//如果条件2中的不满足，还需要从mc_book表中去检索，根据对应的状态去进行返回:
		//判断第三条里的数据是否满足，如果存在了就直接返回，不存在就说明是新书
		$sql = "select {$field} from " . Env::get('APICONFIG.TABLE_NOVEL') . " where title='{$title}' and author='{$author}' and is_async = 1"; //只查等于1的已经同步的，如果是0说明是新添加的
		global $mysql_obj;
		$info = $mysql_obj->fetch($sql, 'db_slave');
		if (empty($info)) {
			//查询是否在mc_book表里有相关的数据信息
			$sql = "select id as store_id ,book_name as title,author,source_url as story_link from " . Env::get('TABLE_MC_BOOK') . " where book_name='{$title}' and author='{$author}'";
			$results = $mysql_obj->fetch($sql, self::$db_conn);
			return !empty($results) ? $results : [];
		} else {
			return !empty($info) ? $info : [];
		}
	}

	/**
	 * @note 重复调用请求，防止有空数据返回做特殊调用--小说章节详情页检测
	 *
	 * @param $content_arr array  请求的HTML数据
	 * @param $goods_list array 原始请求的校验数据
	 * @param $proxy_type 获取移动端的代理配置 4：列表的代理 2：统计移动端页面的代理 3：修补空数据的代理
	 * @return unnkower
	 */
	public static function callMultiListRquests($contents_arr = [], $goods_list = [], $proxy_type = 1)
	{
		if (!$contents_arr || !$goods_list) {
			return [];
		}
		$goods_list = array_values($goods_list);
		$errData  =  $sucData  = [];
		$patterns = '/id="list"/'; //按照正文标签来匹配，如果没有确实是有问题
		foreach ($contents_arr as $key => $val) {
			if (!preg_match($patterns, $val)) {
				$errData[] = $goods_list[$key] ?? [];
			} else {
				$sucData[] = $val;
			}
		}
		$repeat_data = $curl_contents1 = [];
		//数据为空的情况判断
		if (!empty($errData)) {
			$successNum = 0;
			$old_num = count($errData);
			$urls = array_column($errData, 'story_link'); //进来先取出来
			while (true) {
				$curl_contents1 = curl_pic_multi::Curl_http($urls, $proxy_type);
				$temp_url = []; //设置中间变量
				foreach ($curl_contents1 as $tkey => $tval) {
					if (empty($tval)) { //为空的情况
						echo "获取数据为空，会重新抓取======================{$urls[$tkey]}\r\n";
						$temp_url[] = $urls[$tkey];
					} else if (!preg_match($patterns, $tval)) { //断章处理，包含有502的未响应都会
						echo "不全的HTML，会重新抓取======================{$urls[$tkey]}\r\n";
						$temp_url[] = $urls[$tkey];
					} else {
						$repeat_data[] = $tval;
						unset($urls[$tkey]); //已经请求成功就踢出去，下次就不用重复请求了
						unset($curl_contents1[$tkey]);
						$successNum++;
					}
				}
				$urls = $temp_url; //起到指针的作用，每次只存失败的连接
				$urls = array_values($urls); //重置键值，方便查找
				$curl_contents1 = array_values($curl_contents1); //获取最新的数组
				if ($old_num == $successNum) {
					echo "数据清洗完毕等待入库\r\n";
					break;
				}
			}
		}
		$retuernList = array_merge($sucData, $repeat_data);
		return $retuernList;
	}

	/**
	 * @note 利用信号机制结束当前进程
	 *
	 * @return unknown
	 */
	public static function killMasterProcess()
	{
		echo "=====================================\r\n";
		posix_setsid();
		echo "安装信号处理程序...\n";
		pcntl_signal(SIGTERM,  function ($signo) {
			echo "信号处理程序被调用\n";
		});
		//获取当前主进程ID
		$masterPid = posix_getpid();
		if (!$masterPid) {
			echo "no this process\r\n";
		}
		//杀掉当前已经存在的主进程
		posix_kill($masterPid, SIGTERM);
		echo "Worker Exit,killed by pid, PID = {$masterPid}\n";
		echo "分发处理信号程序...\n";
		echo "time =" . date('Y-m-d H:i:s') . "\r\n";
		pcntl_signal_dispatch();
	}

	/**
	 * @note 获取成功和失败的数据信息
	 *
	 * @param  $goods_list array 章节信息
	 * @param  $data array 关联章节数组
	 * @param $type staring 类型 curl :curl判断 ghttp:通过ghttp来判断
	 * @return array
	 */
	public static function getErrSucData($content, $data, $type = 'ghttp')
	{
		if (!$content)
			return [];
		//该网站被大量用户举报，网站含有未经证实的信息，可能造成您的损失，建议谨慎访问
		//strstr($tval, '请求失败')
		$sucData = $errData = [];
		foreach ($content as $key => $val) {
			if ($type == 'ghttp') {
				//如果存在这个就需要去判断
				//如果存在503或者抓取有大量被举报的返回值就需要去判断
				if (empty($val)) { //如果为空或者503错误就存储对应的记录信息或者是403的页面也需要重新抓取
					$errData[] = $data[$key] ?? [];
				} else if (!preg_match('/id="content"/', $val)) { //断章处理
					$errData[] = $data[$key] ?? [];
				} else {
					$sucData[] = $val;
				}
			} else if ($type == 'curl') { //采用curl来进行验证
				// if(empty($tval) || strstr($tval,'503 Service') || strstr($tval, '403 Forbidde')){
				if (empty($val)) { //如果为空或者503错误就存储对应的记录信息或者是403的页面也需要重新抓取
					$errData[] = $data[$key] ?? [];
				} else {
					$sucData[] = $val;
				}
			}
		}
		$info['sucData'] = $sucData;
		$info['errData'] = $errData;
		return $info;
	}

	/**
	 * @note 获取url里的ID信息
	 *
	 * @param  $url array url信息
	 * @return array
	 */
	public static function getUrlById($url = "")
	{
		if (!$url)
			return false;
		$urlArr = parse_url($url);
		$arr  = explode('/', $urlArr['path']);
		$arr = array_filter($arr);
		$ccx = end($arr);
		if (!$ccx) return false;
		return $ccx;
	}


	/**
	 * @note 获取缓存的基础信息
	 *
	 * @param  $url array url信息
	 * @return array
	 */
	public static function cacheStoryDetail($url, $expire_time = 87600)
	{
		if (!$url) return false;
		$novel_id = NovelModel::getUrlById($url);
		$redis_cache_key = 'xsw_detail_' . $novel_id;
		global $redis_data;
		$info = $redis_data->get_redis($redis_cache_key);
		if (!$info) {
			//没有缓存从缓存中获取一次
			$info = webRequest($url, 'GET'); //获取页面缓存信息
			$redis_data->set_redis($redis_cache_key, $info, $expire_time);
			$data = $info;
		} else {
			//直接取出来信息
			$data  = $info;
		}
		return $data;
	}

	/**
	 * @note 构建需要添加的json数据信息
	 *
	 * @param  $value array 小说信息
	 * @return array
	 */
	public static function buildCombindData($value = []){
		if(!$value)
			return false;
		$id = intval($value['id']);
		$book_name = trim($value['book_name']);
		$author = trim($value['author']);
		$data['id'] = $id;
		// if($book_name && $author){
		// 	$data['text'] = sprintf("%s@%s",$book_name,$author);
		// }else{
		// 	$data['text'] = sprintf("%s",$book_name);
		// }
		$data['text'] = $book_name;
		$document= [
			'book_id'	=> $id,
			'book_name' => $book_name,
			'author'	=> $author,
		];
		$data['document'] = $document;
		// $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		return $data;	    
	}

	//
	/**
	 * @note 同步添加gofound数据
	 *
	 * @param  $value array 小说信息
	 * @return array
	 */
	public static function synGofundAdd($gofound,$val){
		if(!$val || !$gofound){
			return false;
		}
		//处理需要同步索引的请求数据信息
		$post_data = NovelModel::buildCombindData($val);
		if($post_data){
			$res = $gofound->add(
				$post_data['id'], 
				$post_data['text'],
				$post_data['document']
			);
			return $res;
		}
	}

	/**
	 * @note 获取缓存中的检索的key
	 * @param $book_id  int  小说ID
	 * @return string
	 */
	public static function getSearchBookData($bookKey = 0){
		if(!$bookKey){
			return false;
		}
		global $redis_data;
		if($bookKey){
			$bookData = $redis_data->get_redis($bookKey);//检查当前文件是否已经同步了
			return $bookData;
		}
		return null;
	}


	/**
	 * @note 获取缓存的对应的key信息
	 * @param $book_id  int  小说ID
	 * @return string
	 */
	public static function getGofoundKey($book_id){
		if(!$book_id){
			return false;
		}
		$bookKey = sprintf("xunsearch_book:%s",$book_id);
		return $bookKey;
	}

	/**
	* @note 获取搜索引擎的索引
	* @param $book_id  int  小说ID
	* @return string
	*/
	public static function synsearchData($list = []){
		if($list){
			global $redis_data;
			$gofound = new GoFound();
			foreach($list as $key =>$val){
				if(!$val) continue;
				//先判断文件中是否有对应的key信息
				$bookKey  = NovelModel::getGofoundKey($val['id']); //获取对应的缓存key
				// $redis_data->del_redis($bookKey);
				// echo 3;die;
				// $bookInfo = NovelModel::getSearchBookData($bookKey); //取出来对应的缓存数据
				// if(!$bookInfo){
					//同步添加res数据信息
				$res = NovelModel::synGofundAdd($gofound,$val);
				//同步数据信息
				if(isset($res['state']) && $res['state'] == true){
					echo "index = {$key}\t  id = {$val['id']}\ttitle = {$val['book_name']}\t author={$val['author']}\tgofondRes: status :{$res['state']} -----message：{$res['message']}\r\n";
				}else{
					dd($res);
					echo "index = {$key}\t id = {$val['id']}\ttitle = {$val['book_name']}\t author={$val['author']} \t gofondRes: ".var_export($res,true)."\r\n";
				}
				// 	$redis_data->set_redis($bookKey,1); //同步对应的索引文件更新
				// }else{
				// 	//已同步
				// 	echo "index = {$key}\t id = {$val['id']}\ttitle = {$val['book_name']}\t author={$val['author']} \t gofondRes: has syn data !!!\r\n";
				// }
			}
		}else{
			echo "no data\r\n";
			return false;
		}
	}
}

