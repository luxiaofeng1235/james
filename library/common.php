<?php

use QL\QueryList;
//数组转换，主要导需要用
function Array_transdata($array, $field)
{
	$trans_data = array();
	if (!$array || !$field) {
		return $trans_data;
	}
	//指定过滤的类型:1：trim 2:intval 3:floatval（暂时支持这三种过滤）
	$filterParam = array('1' => 'trim', '2' => 'intval', '3' => 'doubleval');
	if ($field && count($field)) {
		if ($array) {
			foreach ($array as $val) {
				$innerData = array(); //控制每次都要清空指定数组
				foreach ($field as $tk => $tv) {
					//以指定的方式进行过滤处理优化
					$innerData[] = $filterParam[$tv]($val[$tk]);
				}
				$trans_data[] = $innerData;
			}
		} else {
			$trans_data[] = array();
		}
	} else {
		foreach ($array as $key => $val) {
			$trans_data[] = array_values($val);
		}
	}
	$temp = [];
	if (!empty($trans_data)) {
		$temp = $trans_data;
		if (isset($trans_data))
			unset($trans_data);
	}
	return $temp;
}

function is_cli()
{
	return preg_match("/cli/i", php_sapi_name()) ? true : false;
}



function ForcDownload($file_path, $file_name)
{
	if ($file_path == "" || $file_name == "") return false;
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . $file_name);
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file_path));

	ob_clean();
	flush();
	readfile($file_path);
	exit;
}

/**
 * @note 获取分页返回的数据信息
 *
 * @param [int] $[$page] [<页码>]
 * @param [int] $[$page_sizs] <最大显示数>
 * @author [xiaofeng] <[<luxiaofneg.200@163.com>]>
 * @Date 2020-12-24
 * @return object|bool
 */
function getPageOrSize($page, $page_size)
{
	$page_size = $page_size ? intval($page_size) : 20;
	$page = intval($page) < 1 ? 1 : intval($page);
	return array($page, $page_size);
}

/**
 * @note 对emoji做表情编码
 *
 * @param $str 提交的内容
 * @return str
 */
function userTextEncode($str = '')
{
	if (!is_string($str)) return $str;
	if (!$str || $str == 'undefined') return '';

	$text = json_encode($str); //暴露出unicode
	$text = preg_replace_callback("/(\\\u[2def][0-9a-f]{3})/i", function ($str) {
		return addslashes($str[0]);
	}, $text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
	return  json_encode($text);
}

/**
 * @note 过滤字符串
 *
 * @param $office str
 * @return
 */

function filter_words($office = '')
{
	if (!$office) return '';
	$office = htmlspecialchars_decode($office); //转换一下格式反解出来
	$office = str_replace('<br />', '\n', $office);
	$office = str_replace('</p>', '\n', $office);
	$office = strip_tags($office);
	$text = json_encode($office); //暴露出unicode
	$text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
		return '\\';
	}, $text); //将两条斜杠变成一条，其他不动
	$office = json_decode($text, 1);

	$key_arr = explode(PHP_EOL, $office);
	$str = '';
	if ($key_arr && is_array($key_arr)) {
		foreach ($key_arr as $v) {
			if (empty($v)) {
				$str .= "<br>";
			} else {
				$str .= "<p>" . $v . "</p>";
			}
		}
	}

	return $str;
}

/**
 * @note 过滤特殊字符
 *
 * @param $str str 需要过滤的字符
 * @return array
 */

function filterHtml($str)
{
	if (!$str) return false;

	$str = htmlspecialchars_decode($str);

	$html = str_replace("<p>", "", $str);
	$html = str_replace("</p>", "\n\n", $html);
	$html = str_replace("<br>", "\n", $html);
	$html = strip_tags($html);


	return $html;
}


/* 截取合适长度的字符显示
 */
function cut_str($str, $length, $etc = '...', $start = 0, $code = 'UTF-8')
{
	$ret = '';
	$count = 0;
	$string = html_entity_decode(trim(strip_tags($str)), ENT_QUOTES, $code);
	$strlen = mb_strlen($string, $code);
	for ($i = $start; (($i < $strlen) && ($length > 0)); $i++) {
		$c = mb_substr($string, $i, 1, $code);
		if (preg_match("#[\x{4e00}-\x{9fa5}]#iu", $c)) {
			$count += 1;
		} else {
			$count += 0.5;
		}
		if ($count > $length) {
			break;
		}
		$ret .= $c;
	}
	$ret = htmlspecialchars($ret, ENT_QUOTES, $code);
	if ($i < $strlen) {
		$ret .= $etc;
	}
	return $ret;
}

/**
 * 分割字符串
 * @param $str : 要分割的字符串
 * @param $cut_len : 间隔
 * @param $f : 分割的字符
 */
function cut_string($str = '', $cut_len = 0, $f = ' ')
{
	$len = mb_strlen($str, 'utf-8'); //获取字符串长度
	$content = '';
	for ($i = 0; $i < ceil($len / $cut_len); $i++) {
		$content .= mb_substr($str, $cut_len * $i, $cut_len, 'utf-8') . $f; //遍历添加分隔符
	}
	$content = trim($content, $f); //去除字符串中最后一个分隔符
	return $content;
}

/**
 * @note 获取当前时间
 *
 * @param $format datetime 当前日期
 * @param $tiemset int 过期时间戳
 * @return object
 */
function MyDate($format = 'Y-m-d H:i:s', $timest = 0)
{
	global $cfg_cli_time;
	$addtime = $cfg_cli_time * 3600;
	if (empty($format)) {
		$format = 'Y-m-d H:i:s';
	}
	return gmdate($format, $timest + $addtime);
}

/**
 * @note 过滤字符
 *
 * @param $str string 输入字符
 * @return object
 */
function stripStr($str)
{
	if (is_string($str)) {
		if (!get_magic_quotes_gpc()) $str = stripslashes($str);
	} elseif (is_array($str)) {
		if (!get_magic_quotes_gpc()) {
			foreach ($str as $key => $val) {
				$str[$key] = stripslashes($val);
			}
		}
	}
	return $str;
}
/**
 * @note 转换数组按照key返回
 *
 * @param $trips object 转换的对象
 * @param [str] $[field] [<字段>]
 * @return object
 */

function double_array_exchange_by_field($trips, $field = '')
{
	if (!$trips || !$field)
		return [];
	$itemArr = [];
	foreach ($trips as $v) {
		if (!$v) continue;
		//按照某个key来进行输出入
		$itemArr[$v[$field]] = $v;
	}
	unset($trips);
	return $itemArr;
}

/**
 * 打印文件日志  一天一个
 * @param 文件名-不带后缀 $file_name
 * @param str $str
 * @author wangyan   2013-06-27
 */
function printlog($str = '', $file_name = 'file_log')
{
	$dir = ROOT . 'log';
	if (!$dir) {
		createFolders($dir);
	}
	$filename = $dir . DS . $file_name;
	//创建文件夹
	if(!is_dir(dirname($filename))){
		createFolders(dirname($filename));
	}
	$fp = fopen("{$filename}_" . date('Ymd') . ".txt", 'a+');
	flock($fp, LOCK_EX);
	$sdfsd = fwrite($fp, strftime("%Y/%m/%d %H:%M:%S", time()) . "\t -- $str \t\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

/**
 * @note cookie储存函数
 * @param $name str 存储名称
 * @param $value str 存储的具体值
 * @param $tiem int 设置对应的过期时间
 * @author xiaofeng   2020-06-27
 */
function cookie($name, $value = '', $time = 0)
{
	global $Global;
	if ($time == 0) $time = -1000;
	setcookie($name, $value, $Global['F_time'] + $time, '/', '.' . $Global['F_host']);
	//bug with localhost
	if ($Global['F_host'] == 'localhost') setcookie($name, $value, $Global['F_time'] + $time);
}
/**
 * @note 得到cookie值
 * @param $name str cookie名称
 * @author xiaofeng   2020-06-27
 */
function getCookie($name)
{
	if (!empty($_COOKIE[$name])) {
		return  $_COOKIE[$name];
	} else {
		return false;
	}
}

/**
 * 生成0-9 A-Z 随机字符串或数组
 * @param int $format   格式 1：字母数字组合 2：纯数字 3：纯字母
 * @param int $count    长度
 * @param string $type  类型 默认字符串
 * @param string $data  检测随机字符串是否存在
 * @return array|string
 */
function getMixedChars($format = 1, $count = 6, $type = 'str', $data = [])
{

	switch ($format) {
		case 2:
			$arr4 = range(0, 9);
			break;
		case 3:
			$arr4 = range('A', 'Z');
			break;
		default:
			$arr = range(0, 9);
			$arr3 = range('A', 'Z');
			$arr4 = array_merge($arr, $arr3);
			unset($arr, $arr3);
	}

	$keys = array_rand($arr4, $count);
	foreach ($keys as $value) {
		$arr5[] = $arr4[$value];
	}
	shuffle($arr5);
	if ($type == 'str') {
		if (in_array($arr5, $data)) {
			return $this->getMixedChars($count, $type, $data);
		}
		return implode($arr5);
	} else {
		return $arr5;
	}
}



/**
 * @note 判断远程文件是否存在
 * @param url_file str url对应的文件
 * @author xiaofeng   2020-10-27
 */
function remote_file_exists($url_file)
{
	$headers = get_headers($url_file);
	if (!preg_match("/200/", $headers[0])) {

		return false;
	}
	return true;
}

/**
 * @note 根据当前的url获取curl请求内容
 *
 * @param $url stirng url地址
 * @param $method stirng GET | POST
 * @param mixed $params 参数信息
 * @param $headaer string 头部信息
 * @return array
 */
function webRequest($url, $method, $params = [], $header = [])
{
	//初始化CURL句柄
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	//重要！
	curl_setopt($curl, CURLOPT_ACCEPT_ENCODING, "gzip,deflate");
	if (!empty($header)) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	}
	    
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'); //
	//请求时间
	    
	$timeout = 30;
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
	switch ($method) {
		case "GET":
			curl_setopt($curl, CURLOPT_HTTPGET, true);
			break;
		case "POST":
			// echo "<pre>";
			// var_dump($header);
			// echo "</pre>";
			
			// // if(is_array($params)){
			// // 	$params = json_encode($params,320);
			// // }
			// echo $params;
			// echo "<pre>";
			// var_dump($params);
			// echo "</pre>";
			// exit();
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
			break;
	}
	$data = curl_exec($curl);
	$errorno = curl_errno($curl);
	if ($errorno) {
	    return $errorno;
	}
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl); //关闭cURL会话
	//使用该函数对结果进行转码
	return $data;
}

/**
 * @note sha256加密
 *
 * @param
 * @return
 */

function encrypt_sha256($str = '')
{
	return hash("sha256", $str);
}

function Authorization($param = [], $mdkey = '')
{
	if (!$param) {
		return false;
	}
	/*
	首先将请求参数中的每一个一级字段按照0-9-A-Z-a-z的顺序排序（ASCII字典序），若遇到相同首字母，则看第二个字母，以此类推。注意：如goods、subOrders等嵌套结构字段，内部子字段无需排序。

	排序后的参数以key=value形式使用“&”字符连接,并拼接上通讯密钥key值（注意：通讯密钥key直接拼接），即为待签名字符串。
	* */
	//获取sign参数获取签名验证
	ksort($param);
	reset($param);


	if ($param) {
		$options = '';
		foreach ($param as $key => $item) {
			// if(!$item) continue; //异常判断
			if (!is_array($item)) { //普通的数据格式
				$options .= $key . '=' . $item . '&';
			} else { //处理里面有多多维数组的的 --主要银联那边不需要转码，原样返回
				$options .= $key . '=' . json_encode($item, JSON_UNESCAPED_UNICODE) . '&';
			}
		}
		$options = rtrim($options, '&'); //存在转义字符，那么去掉转义
		if (get_magic_quotes_gpc()) {
			$options = stripslashes($options);
		}

		//#签名规则：用sha256进行上报加密
		//#算法：所有的字段处理排序后用&链接和md5通讯串链接后返回sign
		//采用sha256加密

		//$pattern = '/[^\x00-\x80]/'; 判断含有中文
		//
		// $options = str_replace('\u6d4b\u8bd5', '测试', $options);
		// $options = str_replace('\u7535\u8d39', '电费', $options);
		if (empty($mdkey)) { //做一个兼容
			$mdkey = $this->mdkey;
		}
		$str = $options . $mdkey;
		// echo "待验签:".$str;
		// echo "<hr/>";
		//生成了秘钥
		$sign = encrypt_sha256($str);

		// echo "得到的sign:".$sign;
		// echo "<hr/>";
		// echo $sign;
		// exit;
		$param['sign'] = $sign;
		// echo '<pre>';
		// print_R($param);
		// echo '</pre>';
		return $param;
	}
	return [];
}

//判断含有中文
function checkChineseStr()
{
	$pattern = '/[^\x00-\x80]/';
	if (preg_match('/[^\x00-\x80]/', $str)) {
		return 1;
	} else {
		return 2;
	}
}

/**
 * 将数组转换为字符串
 *
 * @param   array   $data       数组
 * @param   bool    $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return  string  返回字符串，如果，data为空，则返回空
 */
function array2string($data, $isformdata = 1)
{
	if ($data == '') return '';
	if ($isformdata) $data = new_stripslashes($data);
	return addslashes(var_export($data, TRUE));
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string)
{
	if (!is_array($string)) return stripslashes($string);
	foreach ($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}

/**
 * 将字符串转换为数组
 *
 * @param   string  $data   字符串
 * @return  array   返回数组格式，如果，data为空，则返回空数组
 */
function string2array($data)
{
	$array = array();
	if ($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string)
{
	if (!is_array($string)) return addslashes($string);
	foreach ($string as $key => $val) $string[$key] = new_addslashes($val);
	return $string;
}


/**
 * 创建指定目录
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
if (!function_exists('createFolders')) {
	function createFolders($dir)
	{
		return is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0755) and chmod($dir, 0755));
	}
}


/**
 * @note   判断是否为json数据
 * @param url_file str url对应的文件
 * @author xiaofeng   2020-10-27
 */
function is_json($string)
{
	if (is_string($string)) {
		@json_decode($string);
		return (json_last_error() === JSON_ERROR_NONE);
	}
}

/**
 * 获取四月天的dialingIP
 * @return mixed
 */
function getSiyuetian()
{
	global $redis_data;
	$redis_cache_key = 'siyuetian:';
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	if (!$api_proxy_data) {
		$url = 'http://proxy.siyetian.com/apis_get.html?token=gHbi1yTU1EMPRUV65EVJl3TR1STqFUeORUQ61ERZhnTqlENPRVS00EVrJTTqFVN.AOyEDOzEDMxcTM&limit=1&type=1&time=&data_format=json&showTimeEnd=true';
		$info = webRequest($url, 'GET');
		$proxy_info  =    json_decode($info, true);
		if ($proxy_info['code'] == 1) {
			$data = $proxy_info['data'][0] ?? [];
			//一个IP缓存五分钟
			$redis_data->set_redis($redis_cache_key, json_encode($data), 300);
			return $data;
		} else {
			return [];
		}
	} else {
		$proxy_conf = json_decode($api_proxy_data, true);
		return $proxy_conf ?? [];
	}
}

/**
 * 获取芝麻的代理IP-【一般是请求静态IP】
 * @return mixed
 */
function getZhimaProxy()
{
	global $redis_data;
	//获取对应的缓存key的信息
	$redis_cache_key = Env::get('ZHIMA_REDIS_KEY');
	$api_proxy_data =  $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data, true);
	return $proxy_conf ?? [];
}


/**
 * 获取移动端的另外一个新的IP地址
 * @return mixed
 */
function getMobileProxy()
{
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_REDIS_MOBILE_KEY');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data, true);
	return $proxy_conf ?? [];
}

/**
 * 获取图片使用的代理
 * @return mixed
 */
function getImgProxy()
{
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_REDIS_IMG');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data, true);
	return $proxy_conf ?? [];
}

/**
 * 随机获取芝麻的配置代理 每次取一条
 * @return array
 */
function getQyZhimaRand()
{
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_QY_REDIS_KEY');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data, true);
	if (!empty($proxy_conf)) {
		$tmp = range(0, count($proxy_conf) - 1); //生成一定区间的随机数
		$rand = array_rand($tmp, 1); //每次取一条
		$proxy_ret = $proxy_conf[$rand] ?? [];
		return $proxy_ret;
	} else {
		return [];
	}
}

/**
 * 获取移动端页面为空处理获取的新的IP
 * @return mixed
 */
function getMobileEmptyProxy()
{
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data, true);
	return $proxy_conf ?? [];
}





/**
 * 获取代理的配置
 * @param $str 需要处理的路径
 * @return mixed
 */
function getProxyInfo()
{
	$url = Env::get('PROXY_GET_URL');
	$item = webRequest($url, 'GET');
	$tscode  = json_decode($item, true);
	$proxy_data = $tscode['data']['list'][0] ?? [];
	return $proxy_data;
}


/**
 * 获取代理的对应的key
 * @param $str 需要处理的路径
 * @return mixed
 */
function getRedisProyKey()
{
	$year = date('Y');
	$month = date('m');
	$day = date('d');
	$env_cache_key  = Env::get('CACHE_LIST_KEY'); //缓存的key
	$redis_cache_key = str_replace('{$year}', $year, $env_cache_key);
	$redis_cache_key = str_replace('{$month}', $month, $redis_cache_key);
	$redis_cache_key = str_replace('{$day}', $day, $redis_cache_key);
	return $redis_cache_key;
}

/**
 * 获取小说的目录和文件信息
 * @param $str 需要处理的路径
 * @return mixed
 */
function getStoreFile($str, $link_name = '')
{
	if (!$str) {
		return false;
	}
	$data = explode('/', $str);
	$filename = $data[1] ?? '';
	if (strpos($filename, '_')) {
		$prepare_filder = explode('_', $filename);
		$folder_name = $prepare_filder[1] ?? '';
	} else {
		$folder_name = $filename;
	}
	if ($link_name) {
		$save_file = $link_name . '.txt';
	} else {
		$save_file = $data[2] ?? ''; //获取保存的名称
		$save_file = str_replace('html', 'txt', $save_file); //用txt来进行存储保存
	}

	$return_str = ['folder'  =>  $folder_name, 'save_path'  =>  $save_file];
	return $return_str;
}

/**
 * 读取指定文件
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function readFileData($file_path)
{
	if (file_exists($file_path)) {
		$fp = fopen($file_path, "r");
		$str = "";
		$buffer = 1024; //每次读取 1024 字节
		while (!feof($fp)) { //循环读取，直至读取完整个文件
			$str .= fread($fp, $buffer);
		}
		// $str = str_replace("\r\n", "<br />", $str);
		return $str;
	}
}

/*
 * @note 获取小说的章节数据处理-章节列表
 * @param array $item 章节对象信息
 * @param  integer $store_id 小说ID
 * @param txt_path string 存储路径
 * @return mixed
 */
function getStoryCotents($item = [], $store_id = 0, $txt_path = '')
{
	if (!$item)
		return false;
	$valid_curl = 'curl'; //curl验证
	$valid_ghttp = 'ghttp'; //ghttp验证

	//返回移动端的地址转换
	$data_arr  = NovelModel::exchange_urls($item, $store_id, 'count');
	$chapetList = [];
	foreach ($data_arr as $m_key => $gr) {
		$mobileArr = parse_url($gr['mobile_url']);
		$new_data[$mobileArr['path']] = $gr;
		$mobilePath = substr($mobileArr['path'], 0, -2);
		//存对饮的URL信息
		$chapetList[$mobilePath] = [
			//拼装移动端的地址
			'path'	=>	 $txt_path . DS . md5($gr['link_name']) . '.' . NovelModel::$file_type,
			'chapter_name'	=>	$gr['chapter_name'],
			'chapter_link'	=>	$gr['chapter_link'],
			'chapter_mobile_link'	=> substr($gr['mobile_url'], 0, -2),
		];
	}
	$urls = array_column($new_data, 'mobile_url');
	$detail_proxy_type  = ClientModel::getCurlRandProxy();
	$list = curl_pic_multi::Curl_http($urls, $detail_proxy_type); //默认用同步基础信息的代理取抓
	if (!$list || empty($list)) { //说明代理已经到期
		echo "代理已经到期了，请等待下一轮\r\n";
		NovelModel::killMasterProcess(); //退出主程序
		exit(1);
	}

	$rand_str = ClientModel::getCurlRandProxy(); //基础小说的代理IP
	//curl轮训进行请求
	$list  = NovelModel::callRequests($list, $new_data, $valid_curl, $rand_str);
	if (!$list) $list = [];
	$allNovel = [];
	if ($list) {
		global $urlRules;
		$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
		foreach ($list as $content) {
			if (!$content) continue;
			$data = QueryList::html($content)->rules($rules)->query()->getData();
			$html = $data->all();
			$store_content = $html['content'] ?? '';
			$meta_data = $html['meta_data'] ?? ''; //meta表亲爱
			$first_line = $html['first_line'] ?? ''; //第一行的内容
			//获取当前章节的页码数
			$html_path = NovelModel::getChapterPages($meta_data, $first_line);
			if (!$html_path) $html_path = array();
			$allNovel = array_merge($allNovel, $html_path);
		}
	}
	$new_list = [];
	foreach ($new_data as $gk => $gv) {
		$t = explode('/', $gv['mobile_url']);
		$anurl = explode('-', $t[3] ?? []);
		array_pop($anurl);
		//拼接新的url
		$curr_url = Env::get('APICONFIG.PAOSHU_MOBILE_HOSt') . '/' . implode('-', $anurl);
		$num = isset($allNovel[$gk]) ? intval($allNovel[$gk]) : 1;
		for ($i = 0; $i < $num; $i++) {
			$new_list[] = [
				'mobile_url'	=>	$curr_url . '-' . ($i + 1),
			];
		}
	}
	sleep(1);

	/*******************采集需要处理的N*3的一个章节 start************************/
	$chunkBuffer  = array_chunk($new_list, 50); //按照50个一组进行分割,由于采集下来是一个矩阵，导致数据比较多
	$finalList = [];
	foreach ($chunkBuffer as  $jhk => $jhv) {
		echo "==================== detail pages = " . ($jhk + 1) . "\r\n";
		//最终需要请求的列表
		$proxy_type = ClientModel::getCurlRandProxy();
		$cdata = curl_pic_multi::Curl_http(array_column($jhv, 'mobile_url'), $proxy_type);
		$rand_str_new = ClientModel::getCurlRandProxy(); //基础小说的代理IP
		//重复请求，防止数据丢失
		$cdata = NovelModel::callRequests($cdata, $new_list, $valid_curl, $rand_str_new);
		$finalList = array_merge($finalList, $cdata);
	}
	/*******************采集需要处理的N*3的一个章节 end************************/


	if ($finalList) {
		foreach ($finalList as $ck => $cv) {
			// if(!$cv) continue;
			$data1 = QueryList::html($cv)->rules($rules)->query()->getData();
			$html = $data1->all();
			$store_content = $html['content'] ?? '';
			$meta_data = $html['meta_data'] ?? ''; //meta表亲爱
			$first_line = $html['first_line'] ?? ''; //获取第一行的数据信息
			//获取每页的页码位置信息
			// echo $page_link_url."\r\n";
			//处理为空的情况
			//处理剔除第一行的标题显示和替换掉“本章未完，请点击下一页继续阅读”这种字样
			$store_content = NovelModel::removeLineData($store_content);
			//替换内容里的广告
			$store_content = NovelModel::replaceContent($store_content);

			//如果确实没有返回数据信息，先给一个默认值
			if (!$store_content || empty($store_content)) {
				$store_content = '未完待续...';
			}

			$store_content = str_replace(array("\r\n", "\r", "\n"), "", $store_content);
			//替换文本中的P标签
			$store_content = str_replace("<p>", '', $store_content);
			$store_content = str_replace("</p>", "\n\n", $store_content);
			//替换try{.....}cache的一段话JS这个不需要了
			$store_content = preg_replace('/{([\s\S]*?)}/', '', $store_content);
			$store_content = preg_replace('/try\scatch\(ex\)/', '', $store_content);
			//组装html内容 ,必须用页面中的返回的进行组装
			$store_content = preg_replace('/content1()/', '', $store_content);

			$currentPage = NovelModel::getCurrentPage($first_line);
			//存储每一页的内容
			$page_link_url = substr($meta_data, 0, -1) . '-' . $currentPage;
			//只有不为空才进行保存
			if (empty($currentPage)) {
				echo "为空了当前分页----" . $currentPage . "\r\n";
				// $html_contents[$page_link_url] = $store_content;
			}
			$html_contents[$page_link_url] = $store_content;
		}
	}
	if (!$html_contents) {
		return [];
	}
	$store_data = $tdk = [];
	foreach ($html_contents as $ggk => $ggv) {
		$index  = substr($ggk, 0, -2);
		$store_data[$index][] = $ggv;
	}

	if (!empty($store_data)) {
		foreach ($store_data as $gtkey => $gtval) {
			if (!$store_data) continue;
			$string = implode('', $gtval); //切割字符串,不能用,因为文章里含有，会错乱原因在这里
			$tdk[$gtkey]['chapter_name'] = $chapetList[$gtkey]['chapter_name'] ?? ''; //章节名称
			$tdk[$gtkey]['chapter_mobile_link'] = $chapetList[$gtkey]['chapter_mobile_link'] ?? '';
			$tdk[$gtkey]['chapter_link'] = $chapetList[$gtkey]['chapter_link'] ?? ''; //章节链接
			$tdk[$gtkey]['save_path'] = $chapetList[$gtkey]['path'] ?? ''; //保存的文件路径
			$tdk[$gtkey]['content'] = $string; //获取内容信息
		}
	}
	return $tdk ?? [];
}


/*
 * @param $str 根据CURL获取内容信息
 * @param $data array 需要处理的
 * @return mixed
 */
function getContenetNew($data)
{
	foreach ($data as $key => $val) {
		$item[$val['link_url']] = $val;
		$link_name = $val['link_name'];
		$item[$val['link_url']]['link_name'] = $link_name;
		$urls[$val['link_url']] = Env::get('APICONFIG.PAOSHU_HOST') . $val['link_url'];
		$t_url[] = Env::get('APICONFIG.PAOSHU_HOST') . $val['link_url'];
	}
	global $urlRules;
	//获取采集的标识
	$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
	//开启多线程请求,使用当前代理IP去请求，牵扯到部署需要再境外服务器
	$list = MultiHttp::curlGet($t_url, null, true);
	foreach ($list as $key => $val) {

		$data = QueryList::html($val)->rules($rules)->query()->getData();
		$html = $data->all();
		$store_content = $html['content'] ?? '';
		$meta_data = $html['meta_data'] ?? '';
		$href = $html['href'];
		$html_path = getHtmlUrl($meta_data, $href);
		if ($store_content) {
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
	foreach ($item as $k => $v) {
		$item[$k]['content'] = $store_c[$k] ?? '';
	}
	$arr_list = array_values($item);
	return $arr_list;
}

/**
 * @note 处理返回的路径信息
 * @param $meta_meta html内容里的meta标签信息
 * @param  $html_contents string html页面信息
 * @return mixed
 */
function getHtmlUrl($meta_data = '', $html_contents = '')
{
	if (!$meta_data) {
		echo "1111111111111111111\r\n";
		return false;
	}
	//如果没有解析到url=就用原来的做返回
	// if(!strpos($meta_data,'url=')){
	// 	echo '<pre>';
	// 	print_R($html_contents);
	// 	echo '</pre>';
	// 	echo "**************************************\r\n";
	// 	echo '<pre>';
	// 	print_R('ccc' . $meta_data);
	// 	echo '</pre>';

	// 	echo "2222222222222222222\r\n";
	// 	exit;
	// 	return $meta_data;
	// }
	// $meta_data = $meta;
	echo '<pre>';
	print_R($meta_data);
	echo '</pre>';
	$link_reg = '/zzurl=(.*)/si'; //匹配含有url=的通配符preg_match($link_reg, $data , $matches);
	preg_match($link_reg, $meta_data, $matches);
	echo '<pre>';
	print_R($matches);
	echo '</pre>';
	exit;
	$chapterLink = '';
	//如果匹配到就直接返回不需要任何处理
	if (isset($matches[1]) && !empty($matches[1])) {
		$urlData = parse_url($matches[1]);
		$chapterLink = $urlData['path'];
	}
	return $chapterLink;
}



/**
 * @note 去除首尾字符空格
 *
 * @param $str 需要处理的字符
 * @return  string
 */
function replaceLRSpace($str)
{
	if (!$str)
		return false;
	//负责去除首尾空格信息，其他的不处理
	$string = preg_replace('/^\s*|\s*$/i', '', $str);
	if (!$string) {
		$string = '';
	}
	return $string;
}


/**
 * @note 处理数组中的key进行添加
 *
 * @param $key_data array需要处理的数据
 * @return
 */
function handleArrayKey($key_data)
{
	if (!$key_data) return false;
	$new_data = [];
	foreach ($key_data as $key => $val) {
		$tkey = "`$key`";
		$new_data[$tkey] = $val;
	}
	return $new_data;
}

/**
 * @note 通过本地代理获取远程的资源
 * @param $url str  url地址
 * @param $data array 传入的必须字段
 * 
 * @return  strin|bool
 */
function curlContetnsByProxy($url,$proxy=[])
{
	if (!$url)
		return false;
	$host = $proxy['ip'] ?? ''; //代理IP
	$port = $proxy['port'] ?? ''; //端口
	$username = $proxy['username'] ??'';//账户
	$password = $proxy['password'] ??'';//密码
	$proxyauth = '';
	if ($username && $password) {
		$proxyauth = $proxy['username'] . ':' . $proxy['password']; //用户名密码
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	//判断代理验证
	if ($username && isset($password)) {
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
	}
	//判断代理的端口
	if($host && $port){
		echo "代理方式请求  ip = {$host}\t port = {$port}\r\n";
		curl_setopt($ch, CURLOPT_PROXY, $host);
		curl_setopt($ch, CURLOPT_PROXYPORT, $port);
		curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	}
	
	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$curl_scraped_page = curl_exec($ch);
	curl_close($ch); //关闭cURL会话
	return $curl_scraped_page;
}

/**
 * @note 检测URL是否为404
 * @param $url str  url地址
 * @return bool
 */
function check_url($url = '')
{
	stream_context_set_default(
		array(
			'http' => array(
				'timeout' => 5,
			)
		)
	);
	$header = get_headers($url, 1);
	if (strpos($header[0], '200')) {
		return true;
	}
	if (strpos($header[0], '404')) {
		return false;
	}
	if (strpos($header[0], '301') || strpos($header[0], '302')) {
		if (is_array($header['Location'])) {
			$redirectUrl = $header['Location'][count($header['Location']) - 1];
		} else {
			$redirectUrl = $header['Location'];
		}
		return check_url($redirectUrl);
	}
}

/**
 * @note 生成随机字符串
 * @param $length int 长度
 * @return str
 */
function generateRandomString($length) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = mt_rand(0, strlen($characters) - 1);
        $randomString .= $characters[$randomIndex];
    }
    
    return $randomString;
}


/**
 * @note 获取评分随机小数
 *
 * @return str
 */
function getScoreRandom()
{
	$min = 8; // 最小值
	$max = 10; // 最大值
	$precision = 1; // 小数位数
	$randomInt = mt_rand($min * pow(10, $precision), $max * pow(10, $precision));
	$randomFloat = $randomInt / pow(10, $precision);
	return $randomFloat;
}


/**
 * @note 获取外网IP
 *
 * @return str
 */
function getRemoteIp()
{
	$url = 'https://api.ipify.org/?format=json';
	$data = webRequest($url, 'GET', []);
	if ($data) {
		//获取远程的ip请求
		$ret = json_decode($data, true);
		$address_ip = isset($ret['ip']) ? $ret['ip'] : '';
		return $address_ip;
	} else {
		return '';
	}
}

/**
 * @note 写入文件操作--不追加文件
 *
 * @param $file_path str 文件路径
 * @param $content str 文件内容
 * @return str
 */
function writeFileCombine($file_path, $content)
{
	if (!$file_path || !$content)
		return false;
	$file = fopen($file_path, 'w');
	fwrite($file, $content);
	fclose($file);
	return true;
}

/**
 * @note 以追加的方式写入文件
 *
 * @param $file_path str 文件路径
 * @param $content str 文件内容
 * @return str
 */
function writeFileAppend($file_path, $content)
{
	if (!$file_path || !$content)
		return false;
	$fp = fopen($file_path, 'a+');
	flock($fp, LOCK_EX);
	$sdfsd = fwrite($fp, "\n$content\n");
	flock($fp, LOCK_UN);
	fclose($fp);
	return true;
}

/**
 * @note 转码处理
 *
 * @param $arr str 处理转换的字符
 * @param $in_charset str 转换字符集 默认GBK
 * @param $out_charset str 输出字符集
 * @return str
 */
function array_iconv($arr, $in_charset = "GBK", $out_charset = "utf-8//ignore")
{
	//测试
	// $encode = mb_detect_encoding($arr,array("ASCII","GB2312","GBK",'BIG5','UTF-8'));
	return iconv($in_charset, $out_charset, $arr);
	// $ret =iconv($in_charset, $out_charset, $arr) ;
	// 	// $ret = eval('return '.iconv($in_charset,$out_charset,var_export($arr,true).';'));
	// 	return $ret;
	//  // 这里转码之后可以输出json
	//  // return json_encode($ret);
}

/**
 * @note 重组代理参数
 *
 * @param $data array 代理信息
 * @return str
 */
function combineProxyParam($data)
{
	if (!$data)
		return [];
	if (!isset($data['port'])) $data['port'] = $data['proxy_port'] ?? ''; //端口转换
	if (!isset($data['ip'])) $data['ip'] = $data['real_ip'] ?? ''; //端口IP
	//兼容其他代理返回的数据
	if (isset($data['ip']) && strpos($data['ip'], ':')) {
		$ipData = explode(':', $data['ip']);
		$data['ip'] = $ipData[0];
		$data['port'] = $ipData[1];
	}
	return $data;
}


/**
 * @note 遍历当前的文件夹列表
 * @param   string   $path [要读取的文件目录]
 */
function traverse($dir = '.')
{
	// 使用 glob() 函数获取指定目录下的所有 txt 文件
	$file_list = glob($dir . "/*.txt");
	$list = [];
	// 循环遍历文件列表并输出每个文件的名称
	foreach ($file_list as $file) {
		$list[] = $file;
	}
	return $list;
}

/**
 * @note 繁体转换
 * @param   string  $content 换换的字体
 * @return array
 */
function traditionalCovert($content = '')
{
	if (!$content) {
		return '';
	}
	//不知道为啥mb_convert_encoding转换会乱码，先这么用吧
	// @$str = iconv('big5','utf8',$content);
	// echo '<pre>';
	// print_R($str);
	// echo '</pre>';
	// exit;
	$od = opencc_open("tw2sp.json");
	//转换繁体到简体，利用本地语言库来实现
	$text = opencc_convert($content, $od);
	opencc_close($od);
	return $text;
}

/**
 * @note 处理转义字符 
 * @param   string  $data 输入字符
 * @return string
 */
function myaddslashes($data)
{
    if(false == get_magic_quotes_gpc())
    {
        return addslashes($data);//未启用魔术引用时,转义特殊字符
    }
    return $data;
}


/**
 * @note 替换视频中的所有空格，收尾+中间
 * @param   string  $str 输入字符
 * @return string
 */
function trimAllSpace($str=''){
	if (!$str) {
		return '';
	}

	// 使用 preg_replace 替换所有空白字符
	$qian=array(" ","　","\t","\n","\r");
	$replaceString=  str_replace($qian, '', $str);
	return $replaceString;
}

/**
 * @note 去除首尾空格字符，包含空白字符
 * @param   string  $str 输入字符
 * @return string
 */
function trimBlankSpace($str = '')
{
	if (!$str) {
		return '';
	}
	$str = trim($str); //不管什么先替换一下
	$replacement = '';
	// $pattern = '/\A\s+|\s+\z/u';
	$pattern = '~^\h+|\h+$~u'; // \h 匹配任何水平空白字符
	$trimmedStr = preg_replace($pattern, $replacement, $str);
	return $trimmedStr;
}

/**
 * @note 替换字符中含有\r \n \r\n的字符
 * @param   string  $str 输入字符
 * @return string
 */
function trimBlankLine($str = '')
{
	if (!$str) {
		return false;
	}
	$patten = array("\r\n", "\n", "\r");
	//先替换掉\\r\\n,然后是否存在\\n,最后替换\\r
	$str = str_replace($patten, '', $str);
	return $str;
}

/**
 * @note 剔除章节里的软回车符
 * @param   string  $str 输入字符
 * @return string
 */
function removeTabEnter($str)
{
	if (!$str)
		return false;
	$result = array(); // 转换后的结果
	$tokens = preg_split('/[\r\n]+/', $str);
	foreach ($tokens as &$val) {
		$val = trimBlankSpace($val);
	}
	$td = implode('', $tokens);
	return $td;
}


/**
 * @note 获取图片尺寸信息
 * @param   string  $filename 图片路径
 * @return string
 */
function getImageSpace($imagePath = '')
{
	if (!$imagePath) {
		return false;
	}
	// 检查文件是否存在
	if (file_exists($imagePath)) {
		// 获取文件大小，单位为字节
		$size = filesize($imagePath);

		// 转换为其他单位
		$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$bytes = max($size, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		// 计算有效数字，并格式化
		$bytes /= (1 << (10 * $pow));
		$bytes = round($bytes, 2);
		// $str = "{$bytes} {$units[$pow]}";
		return $bytes; //返回实际的占用大小单位是KB
	} else {
		return 0;
	}
}


/**
 * @note 获取每周星期一的具体时间
 * @param   string  $filename 图片路径
 * @return string
 */
function getWeekDate($time = ""){
	if($time){
		$currentDate =$time;
	}else{
		$currentDate =date('Ymd');
	}
	// 获取当前日期是周几
	$currentDayOfWeek = date('N', strtotime($currentDate));

	// 计算星期一的日期
	if ($currentDayOfWeek != 1) {
	    $monday = date('Ymd', strtotime($currentDate . ' -' . ($currentDayOfWeek - 1) . ' days'));
	} else {
	    $monday = $currentDate;
	}
	return $monday;
}


/**
 * @note 把txt文本分割为内容数组信息
 * @param string  $string  字符串信息
 * @param int $len 分割长度
 * @return string
 */
function mbStrSplitChapter($string, $len=1) {
    $start = 0;
    $strlen = mb_strlen($string);
    while ($strlen) {
        $array[] = mb_substr($string,$start,$len,"utf8");
        $string = mb_substr($string, $len, $strlen,"utf8");
        $strlen = mb_strlen($string);
    }
    return $array;
}

/**
 * @note 把阿拉伯数字转换成大写
 * @param $number  int  数值信息
 * @return string
 */
function convertChineseUppercase($number) {
    $digits = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $units = array('', '十', '百', '千', '万', '十万', '百万', '千万', '亿', '十亿', '百亿', '千亿', '兆');

    $result = '';
    $num_length = strlen((string)$number);

    for ($i = 0; $i < $num_length; $i++) {
        $digit = (int)substr((string)$number, $num_length - $i - 1, 1);
        if ($digit != 0 || ($digit == 0 && ($i == 0 || $i == $num_length - 1))) {
            $result = $digits[$digit] . $units[$i] . $result;
        }
    }

    return $result;
}


/**
 * @note 遍历获取当前目录下的文件
 * @param $dir  string  路径名称
 * @return string
 */
function getFilesInDirectory($dir) {
    // Ensure the directory exists
    if (!is_dir($dir)) {
        return [];
    }
    // Scan directory and filter out '.' and '..'
    $files = array_diff(scandir($dir), ['.', '..']);
    // Create an associative array with filenames and their modification times
    $fileModTimes = [];
    foreach ($files as $file) {
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_file($filePath)) {
            $fileModTimes[$file] = filemtime($filePath);
        }
    }
    // Sort files by modification time in descending order
    arsort($fileModTimes);
    return array_keys($fileModTimes);
}



/**
 * @note 成功的返回
 * @param $message  string  提醒消息
 * @param $data array 结构体
 * @param  $code int 状态码
 * @return string
 */
function responseSuccess($message, $data = [],$code = 1) {
    $response = [
        'code' => $code,
        'message' => $message,
        'data' => $data
    ];
    header('Content-Type: application/json');
    return json_encode($response,JSON_UNESCAPED_UNICODE);
}

/**
 * @note 失败的返回
 * @param $message  string  提醒消息
 * @param $data array 结构体
 * @param  $code int 状态码
 * @return string
 */
function responseError($message, $code = 0) {
    $response = [
        'code' => $code,
        'message' => $message,
    ];
    header('Content-Type: application/json');
    return json_encode($response,JSON_UNESCAPED_UNICODE);
}


/**
 * @note 处理ajax_return的返回值
 * @param $data array 结构体
 * @param  $callback string 异步回调的callbak
 * @return string
 */
function ajax_return($data=array(),$callback=''){
	$result = is_array($data) ? json_encode($data) : $data ;
	if($callback){
		$result = $callback . "(". $result . ")" ;
	}
	return $result ;
}

/**
 * @note 处理根据其中的某几位返回前3位
 * @param $number int 输入的数值
 * @return  string|unknow
 */
function getFirstThreeDigits($number) {
	if(!$number){
		return 0;
	}
	$number = intval($number);
	$firstThreeDigits = (int) $number / 1000; //按照三位数进行显示
	$firstThreeDigits  = intval($firstThreeDigits);
	return $firstThreeDigits;
	    
	    
    // 将数值转为字符串
    // $numberStr = (string) abs($number); // 取绝对值并转换为字符串
    // $number = strlen($numberStr);
    // $cc = $number /1000;
    return $cc;
    // if ($number === 6) {
    //    	$firstThreeDigits = substr((string)$numberStr, 0, 3);// 六位数返回前三位
    // } elseif ($number === 7) {
    //   	$firstThreeDigits = substr((string)$numberStr, 0, 4); // 七位数返回前四位
    // }else{
    // 	$firstThreeDigits = substr((string)$numberStr, 0, 3); //其余都按照3位来进行处理
    // }
    // if(!$firstThreeDigits || $firstThreeDigits == ""){
    // 	$firstThreeDigits = 0;
    // }
    return $firstThreeDigits;
}

/**
 * @note 递归删除文件夹和文件内容
 * @param $dir string 文件夹内容
 * @return  string|unknow
 */
function deleteDirectory($dir) {
    // 检查目录是否存在

    if (!is_dir($dir)) {
        return;
    }
        

    // 扫描目录下的所有文件和子目录
    $files = scandir($dir);
        
    foreach ($files as $file) {
        // 跳过 '.' 和 '..'
        if ($file === '.' || $file === '..') {
            continue;
        }

        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

        // 如果是目录，递归调用
        if (is_dir($fullPath)) {
            deleteDirectory($fullPath);
        } else {
            // 如果是文件，删除
            unlink($fullPath);
        }
    }

    // 删除空目录
    rmdir($dir);
}
