<?php
use QL\QueryList;
//数组转换，主要导需要用
function Array_transdata($array,$field){
	$trans_data =array();
	if(!$array || !$field){
		return $trans_data;
	}
	//指定过滤的类型:1：trim 2:intval 3:floatval（暂时支持这三种过滤）
	$filterParam = array('1'=>'trim','2'=>'intval','3'=>'doubleval');
	if($field&&count($field)){
		if($array){
			foreach($array as $val){
				$innerData= array(); //控制每次都要清空指定数组
				foreach($field as $tk =>$tv){
					//以指定的方式进行过滤处理优化
					$innerData[]= $filterParam[$tv]($val[$tk]);
				}
				$trans_data[]=$innerData;
			}
		}else{
			$trans_data[]=array();
		}
	}else{
		foreach($array as $key=>$val){
			$trans_data[]=array_values($val);
		}
	}
	$temp= [];
	if (!empty($trans_data)){
		$temp = $trans_data;
		if (isset($trans_data))
			unset($trans_data);
	}
	return $temp;
}

function is_cli(){
	return preg_match("/cli/i", php_sapi_name()) ? true : false;
}

//分页封装
function paging($ob_pger="",$page_param){
	if(!is_array($page_param))return false;
	parse_str($page_param['param'], $query_string);
	$query_string['page'] = "#PAGE#";
	if(is_object($ob_pger)){
		$total_page = ceil($page_param['total'] / $page_param['page_size']);

		$page = $page_param['page'] > $total_page ? ( $total_page == 0 ? 1 : $total_page ) : $page_param['page'];

		return  $pager = $ob_pger->gen(array(
			'pager_html' => '<a href="#URL#">#PAGE#</a>',
			'curr_html' => '<a href="#URL" class="current">#PAGE#</a>',
			'page' => $page,
			'total_page' => $total_page,
			'base_url' => $page_param['method']."?" . http_build_query($query_string),
		));
	}else{
		return $page_param['method']."?" . http_build_query($query_string);
	}
	return false;
}

function ForcDownload($file_path,$file_name){
	if($file_path==""||$file_name=="")return false;
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$file_name);
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
function getPageOrSize($page,$page_size){
	$page_size = $page_size?intval($page_size):20;
	$page = intval($page)<1?1:intval($page);
	return array($page,$page_size);
}

/**
 * @note 对emoji做表情编码
 *
 * @param $str 提交的内容
 * @return str
 */
function userTextEncode($str=''){
	if(!is_string($str)) return $str;
	if(!$str || $str=='undefined') return '';

	$text = json_encode($str); //暴露出unicode
	$text = preg_replace_callback("/(\\\u[2def][0-9a-f]{3})/i",function($str){
		return addslashes($str[0]);
	},$text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
	return  json_encode($text);
}

/**
 * @note 过滤字符串
 *
 * @param $office str
 * @return
 */

function filter_words($office=''){
	if(!$office) return '';
	$office = htmlspecialchars_decode($office); //转换一下格式反解出来
	$office = str_replace('<br />','\n',$office);
	$office = str_replace('</p>','\n',$office);
	$office = strip_tags($office);
	$text = json_encode($office); //暴露出unicode
	$text = preg_replace_callback('/\\\\\\\\/i',function($str){
		return '\\';
	},$text); //将两条斜杠变成一条，其他不动
	$office = json_decode($text,1);

	$key_arr = explode(PHP_EOL,$office);
	$str ='';
	if($key_arr&&is_array($key_arr)){
		foreach($key_arr as $v){
			if(empty($v)){
				$str.= "<br>";
			}else{
				$str.="<p>".$v."</p>";
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

function filterHtml($str){
	if(!$str)return false;

	$str = htmlspecialchars_decode($str);

	$html=str_replace("<br></p >","\\n",$str);
	$html=str_replace("</p >","\\n",$html);
	$html=str_replace("<br>","\\n",$html);
	$html=strip_tags($html);


	return $html;
}


/* 截取合适长度的字符显示
 */
function cut_str($str, $length, $etc='...', $start = 0, $code='UTF-8'){
	$ret = '';
	$count = 0;
	$string = html_entity_decode(trim(strip_tags($str)), ENT_QUOTES, $code);
	$strlen = mb_strlen($string, $code);
	for($i = $start; (($i < $strlen) && ($length > 0)); $i++) {
		$c = mb_substr($string, $i, 1, $code);
		if(preg_match("#[\x{4e00}-\x{9fa5}]#iu", $c)){
			$count +=1;
		}else{
			$count += 0.5;
		}
		if($count > $length){
			break;
		}
		$ret .= $c;
	}
	$ret = htmlspecialchars($ret, ENT_QUOTES, $code);
	if($i < $strlen)
	{
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
function cut_string($str='',$cut_len=0, $f = ' '){
	$len = mb_strlen($str,'utf-8');//获取字符串长度
	$content = '';
	for($i=0;$i<ceil($len/$cut_len);$i++){
		$content .= mb_substr($str,$cut_len*$i,$cut_len,'utf-8').$f;//遍历添加分隔符
	}
	$content = trim($content,$f);//去除字符串中最后一个分隔符
	return $content;
}

/**
 * @note 获取当前时间
 *
 * @param $format datetime 当前日期
 * @param $tiemset int 过期时间戳
 * @return object
 */
function MyDate($format='Y-m-d H:i:s', $timest=0)
{
	global $cfg_cli_time;
	$addtime = $cfg_cli_time * 3600;
	if(empty($format))
	{
		$format = 'Y-m-d H:i:s';
	}
	return gmdate ($format, $timest+$addtime);
}

/**
 * @note 过滤字符
 *
 * @param $str string 输入字符
 * @return object
 */
function stripStr($str){
	if(is_string($str)){
		if(!get_magic_quotes_gpc()) $str=stripslashes($str);
	}elseif(is_array($str)){
		if(!get_magic_quotes_gpc()){
			foreach($str as $key=>$val){
				$str[$key]=stripslashes($val);
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

function double_array_exchange_by_field($trips,$field=''){
	if(!$trips || !$field)
		return [];
	$itemArr = [];
	foreach($trips as $v){
		if(!$v) continue;
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
function printlog($str='',$file_name='file_log')
{
	$dir = ROOT .'log';
	if(!$dir){
		createFolders($dir);
	}
	$filename = $dir . DS . $file_name;
	$fp = fopen("{$filename}_".date('Ymd').".txt", 'a+');
	flock($fp, LOCK_EX) ;
	$sdfsd=fwrite($fp,strftime("%Y/%m/%d %H:%M:%S",time())."\t -- $str \t\n");
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
function cookie($name,$value='',$time=0){
	global $Global;
	if($time==0) $time = -1000;
	setcookie($name, $value, $Global['F_time']+$time, '/', '.'.$Global['F_host']);
	//bug with localhost
	if($Global['F_host'] == 'localhost')setcookie($name, $value, $Global['F_time']+$time);
}
/**
 * @note 得到cookie值
 * @param $name str cookie名称
 * @author xiaofeng   2020-06-27
 */
function getCookie($name){
	if(!empty($_COOKIE[$name])){
		return  $_COOKIE[$name];
	}else{
		return false;
	}
}

/**
 * @note 判断远程文件是否存在
 * @param url_file str url对应的文件
 * @author xiaofeng   2020-10-27
 */
function remote_file_exists($url_file){
	$headers = get_headers($url_file);
	if (!preg_match("/200/", $headers[0])){

		return false;

	}
	return true;
}

function webRequest($url,$method,$params=[],$header = []){
	//初始化CURL句柄
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	if(!empty($header)){
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
	}
	//请求时间
	$timeout = 30;
	curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
	switch ($method){
	case "GET" :
		curl_setopt($curl, CURLOPT_HTTPGET, true);
		break;
	case "POST":
		if(is_array($params)){
			$params = json_encode($params,320);
		}
		// echo $params;
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_POSTFIELDS,$params);
		break;
	}
	$data = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);//关闭cURL会话

	return $data;
}

/**
 * @note sha256加密
 *
 * @param
 * @return
 */

function encrypt_sha256($str = ''){
	return hash("sha256", $str);
}

function Authorization($param = [],$mdkey =''){
	if(!$param){
		return false;
	}
	/*
	   首先将请求参数中的每一个一级字段按照0-9-A-Z-a-z的顺序排序（ASCII字典序），若遇到相同首字母，则看第二个字母，以此类推。注意：如goods、subOrders等嵌套结构字段，内部子字段无需排序。

	排序后的参数以key=value形式使用“&”字符连接,并拼接上通讯密钥key值（注意：通讯密钥key直接拼接），即为待签名字符串。
	* */
	//获取sign参数获取签名验证
	ksort($param);
	reset($param);


	if($param){
		$options = '';
		foreach($param as $key =>$item){
			// if(!$item) continue; //异常判断
			if(!is_array($item)){ //普通的数据格式
				$options .= $key . '=' . $item .'&';
			}else{//处理里面有多多维数组的的 --主要银联那边不需要转码，原样返回
				$options .=$key . '=' . json_encode($item,JSON_UNESCAPED_UNICODE).'&';
			}
		}
		$options = rtrim($options, '&');//存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){
			$options = stripslashes($options);
		}

		//#签名规则：用sha256进行上报加密
		//#算法：所有的字段处理排序后用&链接和md5通讯串链接后返回sign
		//采用sha256加密

		//$pattern = '/[^\x00-\x80]/'; 判断含有中文
		//
		// $options = str_replace('\u6d4b\u8bd5', '测试', $options);
		// $options = str_replace('\u7535\u8d39', '电费', $options);
		if(empty($mdkey)){ //做一个兼容
			$mdkey = $this->mdkey;
		}
		$str = $options.$mdkey;
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
function checkChineseStr(){
	$pattern = '/[^\x00-\x80]/';
	if(preg_match('/[^\x00-\x80]/',$str)){
		return 1;
	}else{
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
function array2string($data, $isformdata = 1) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return addslashes(var_export($data, TRUE));
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}

/**
 * 将字符串转换为数组
 *
 * @param   string  $data   字符串
 * @return  array   返回数组格式，如果，data为空，则返回空数组
 */
function string2array($data) {
	$array =array();
	if($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string){
	if(!is_array($string)) return addslashes($string);
	foreach($string as $key => $val) $string[$key] = new_addslashes($val);
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
		return is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0777) and chmod($dir, 0777));
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
function getSiyuetian(){
	global $redis_data;
	$redis_cache_key = 'siyuetian:';
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	if(!$api_proxy_data){
		$url = 'http://proxy.siyetian.com/apis_get.html?token=gHbi1yTU1EMPRUV65EVJl3TR1STqFUeORUQ61ERZhnTqlENPRVS00EVrJTTqFVN.AOyEDOzEDMxcTM&limit=1&type=1&time=&data_format=json&showTimeEnd=true';
		$info = webRequest($url,'GET');
		$proxy_info  =    json_decode($info , true);
		if( $proxy_info['code'] == 1){
			$data = $proxy_info['data'][0] ?? [];
			//一个IP缓存五分钟
			$redis_data->set_redis($redis_cache_key,json_encode($data),300);
			return $data;
		}else{
			return [];
		}
	}else{
		$proxy_conf = json_decode($api_proxy_data , true);
		return $proxy_conf ?? [];
	}

}

/**
 * 获取芝麻的代理IP-【一般是请求静态IP】
 * @return mixed
 */
function getZhimaProxy(){
	global $redis_data;
	//获取对应的缓存key的信息
	$redis_cache_key = Env::get('ZHIMA_REDIS_KEY');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data , true);
	return $proxy_conf ?? [];
}


/**
 * 获取移动端的另外一个新的IP地址
 * @return mixed
 */
function getMobileProxy(){
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_REDIS_MOBILE_KEY');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data , true);
	return $proxy_conf ?? [];
}

/**
 * 获取图片使用的代理
 * @return mixed
 */
function getImgProxy(){
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_REDIS_IMG');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data , true);
	return $proxy_conf ?? [];
}

/**
 * 获取移动端页面为空处理获取的新的IP
 * @return mixed
 */
function getMobileEmptyProxy(){
	global $redis_data;
	$redis_cache_key = Env::get('ZHIMA_REDIS_MOBILE_EMPTY_DATA');
	$api_proxy_data = $redis_data->get_redis($redis_cache_key);
	$proxy_conf = json_decode($api_proxy_data , true);
	return $proxy_conf ?? [];
}

/**
 * 获取wget需要下载的命令
 * @param $urls array 下载链接
 * @return mixed
 */
function getWgetCommand($urls=[] ,$type = 1){
	if(!$urls)
		return false;
	$proxy_data = getZhimaProxy();
	if(!$proxy_data)
		return [];
	//切割html数据信息
	$html_url = implode(' ',$urls);
	//获取代理的配置信息凭借wget的参数命令
	$proxyauth = $proxy_data['ip'] . ':' .$proxy_data['port'];
	$str = 'wget --restrict-file-names=nocontrol   -e use_proxy=yes -e http_proxy='.$proxyauth.' --user-agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"  --header="Cookie: width=85%25; Hm_lvt_61f1afd153b0a37229eefe873fe6586a=1710121429,1710213284,1710295133,1710378533; Hm_lpvt_61f1afd153b0a37229eefe873fe6586a=1710403413"  '.$html_url.' -O '.Env::get('CHAPTER_PATH_LOG');
	return $str;
}




/**
 * 获取代理的配置
 * @param $str 需要处理的路径
 * @return mixed
 */
function getProxyInfo(){

	// $proxy = [
	// 	'ip'        =>  Env::get('PROXY.URL_HOST'), //代理的IP
	// 	'port'      =>  Env::get('PROXY.PORT'), //代理的端口号
	// 	'username'  =>  Env::get('PROXY.username'), //用户名
	// 	'password'  =>  Env::get('PROXY.password'), //密码
	// ];
	// return $proxy;
	//取代理的配置信息
	// global $redis_data;
	// $redis_cache_key = getRedisProyKey();
	// //默认先从配置去取
	// $api_proxy_data = $redis_data->get_redis($redis_cache_key);
	// $proxy_data = json_decode($api_proxy_data,true);
	// return $proxy_data ?? [];
	// if($api_proxy_data){
	//      $proxy_data = json_decode($api_proxy_data,true);
	//      return $proxy_data;
	// }else{
	    $url =Env::get('PROXY_GET_URL');
	    $item = webRequest($url,'GET');
	    $tscode  = json_decode($item,true);
	    $proxy_data = $tscode['data']['list'][0] ?? [];
	    return $proxy_data;
	    // $redis_data->set_redis($redis_cache_key,json_encode($proxy_data),NovelModel::$redis_expire_time);

	//     return $redis_data;
	// }
}


/**
 * 获取代理的对应的key
 * @param $str 需要处理的路径
 * @return mixed
 */
function getRedisProyKey(){
	$year = date('Y');
	$month = date('m');
	$day = date('d');
	$env_cache_key  = Env::get('CACHE_LIST_KEY');//缓存的key
	$redis_cache_key = str_replace('{$year}',$year,$env_cache_key);
	$redis_cache_key = str_replace('{$month}',$month,$redis_cache_key);
	$redis_cache_key = str_replace('{$day}',$day,$redis_cache_key);
	return $redis_cache_key;
}

/**
 * 获取小说的目录和文件信息
 * @param $str 需要处理的路径
 * @return mixed
 */
function getStoreFile($str,$link_name=''){
	if(!$str){
		return false;
	}
	$data =explode('/', $str);
	$filename = $data[1] ?? '';
	if(strpos($filename,'_')){
		$prepare_filder= explode('_',$filename);
		$folder_name = $prepare_filder[1]??'';
	}else{
		$folder_name = $filename;
	}
	if($link_name){
		$save_file = $link_name . '.txt';
	}else{
		$save_file = $data[2]??'';//获取保存的名称
		$save_file = str_replace('html','txt',$save_file); //用txt来进行存储保存
	}

	$return_str= ['folder'  =>  $folder_name , 'save_path'  =>  $save_file];
	return $return_str;
}

/**
 * 读取指定文件
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function readFileData($file_path){
	if (file_exists($file_path)) {
		$fp = fopen($file_path, "r");
		$str = "";
		$buffer = 1024;//每次读取 1024 字节
		while (!feof($fp)) {//循环读取，直至读取完整个文件
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
function getStoryCotents($item=[],$store_id=0,$txt_path=''){
	if(!$item)
		return false;

	$valid_curl = 'curl';//curl验证
	$valid_ghttp ='ghttp';//ghttp验证
	//返回移动端的地址转换
	$data_arr  = NovelModel::exchange_urls($item,$store_id,'count');
	$chapetList = [];
	foreach($data_arr as $m_key=> $gr){
		 $mobileArr =parse_url($gr['mobile_url']);
		 $new_data[$mobileArr['path']] = $gr;
		 $mobilePath = substr($mobileArr['path'],0,-2);
		 //存对饮的URL信息
		 $chapetList[$mobilePath] = [
		 		//拼装移动端的地址
		 		'path'	=>	Env::get('SAVE_NOVEL_PATH') .DS .$txt_path.DS.md5($gr['link_name']).'.'.NovelModel::$file_type,
		 		'chapter_name'	=>	$gr['chapter_name'],
		 		'chapter_link'	=>	$gr['chapter_link'],
		 		'chapter_mobile_link'	=> substr($gr['mobile_url'] , 0 , -2),
		 ];
	}
	$detail_proxy_type =4;//基础小说的代理IP
	$count_proxy_type= 2;//列表为空的代理IP
	$empty_proxy_type =3;//修补数据的代理IP
	$img_proxy_type =5;//处理图片的代理IP
	$urls = array_column($new_data,'mobile_url');
	// $list = guzzleHttp::multi_req($urls,'story');
	$list = curl_pic_multi::Curl_http($urls,$detail_proxy_type); //默认用同步基础信息的代理取抓
	if(!$list || empty($list)){//说明代理已经到期
		echo "代理已经到期了，请等待下一轮\r\n";
		NovelModel::killMasterProcess();//退出主程序
		exit(1);
	}
	//重复调用，防止有空对象返回以防万一
	//重复请求，防止数据丢失
	//随机获取一个代理
	$proxy_arr= array($detail_proxy_type, $count_proxy_type,$empty_proxy_type,$img_proxy_type);
	$rand_str =$proxy_arr[mt_rand(0,count($proxy_arr)-1)];
	//curl轮训进行请求
	$list  = NovelModel::callRequests($list , $new_data,$valid_curl,$rand_str);
	if(!$list) $list = [];




	$allNovel = [];
	if($list){
		global $urlRules;
		$rules =$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['mobile_content'];
		foreach($list as $content){
			if(!$content) continue;
			$data = QueryList::html($content)->rules($rules)->query()->getData();
			$html = $data->all();
			$store_content = $html['content'] ?? '';
			$meta_data = $html['meta_data']??''; //meta表亲爱
			$first_line = $html['first_line'] ?? '';//第一行的内容
			//获取当前章节的页码数
			$html_path = NovelModel::getChapterPages($meta_data,$first_line);
			if(!$html_path) $html_path = array();
			$allNovel = array_merge($allNovel,$html_path);
		}
	}
	$new_list= [];
	foreach($new_data as $gk =>$gv){
		 $t =explode('/',$gv['mobile_url']);
		 $anurl =explode('-',$t[3]??[]);
		 array_pop($anurl);
		 //拼接新的url
		 $curr_url = Env::get('APICONFIG.PAOSHU_MOBILE_HOSt'). '/'.implode('-',$anurl);
		 $num = isset($allNovel[$gk]) ? intval($allNovel[$gk]) : 1;
		 for ($i=0; $i <$num ; $i++) {
		 	 $new_list[]=[
		 	 	'mobile_url'	=>	$curr_url.'-'.($i+1),
		 	 ];
		 }
	}
	sleep(1);

	//最终需要请求的列表
	$finalList = curl_pic_multi::Curl_http(array_column($new_list,'mobile_url'),$empty_proxy_type);
	$temp_proxy_arr  =$proxy_arr;
	$rand_str_new =$temp_proxy_arr[mt_rand(0,count($temp_proxy_arr)-1)]; //随机代理重新分配
	// $finalList =  guzzleHttp::multi_req(array_column($new_list,'mobile_url'),'story');
	// unset($new_list);
	//重复请求，防止数据丢失
	$finalList = NovelModel::callRequests($finalList , $new_list,$valid_curl,$rand_str_new);
	// unset($new_data);
	// $finalList = curl_pic_multi::Curl_http($new_list,4);
	if($finalList){
		foreach($finalList as $ck =>$cv){
			if(!$cv) continue;
			$data1 = QueryList::html($cv)->rules($rules)->query()->getData();
			$html = $data1->all();
			$store_content = $html['content'] ?? '';
			$meta_data = $html['meta_data']??''; //meta表亲爱
			$first_line = $html['first_line'] ?? '';//获取第一行的数据信息
			//获取每页的页码位置信息


			// echo $page_link_url."\r\n";
			//处理为空的情况
			//处理剔除第一行的标题显示和替换掉“本章未完，请点击下一页继续阅读”这种字样
			$store_content= NovelModel::removeLineData($store_content);
			//替换内容里的广告
			$store_content = NovelModel::replaceContent($store_content);

			//如果确实没有返回数据信息，先给一个默认值
			if(!$store_content || empty($store_content)){
				$store_content ='未完待续...';
			}

			$store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
			//替换文本中的P标签
			$store_content = str_replace("<p>",'',$store_content);
			$store_content = str_replace("</p>","\n\n",$store_content);
			//替换try{.....}cache的一段话JS这个不需要了
			$store_content = preg_replace('/{([\s\S]*?)}/','',$store_content);
			$store_content = preg_replace('/try\scatch\(ex\)/','',$store_content);
            //组装html内容 ,必须用页面中的返回的进行组装

			$currentPage = NovelModel::getCurrentPage($first_line);
			//存储每一页的内容
			$page_link_url = substr($meta_data,0, -1).'-'.$currentPage;
			//只有不为空才进行保存
            if(!empty($currentPage)){
            	$html_contents[$page_link_url] = $store_content;
            }
            $html_contents[$page_link_url] = $store_content;
            // if(isset($new_list[$ck])){
            // 	$ttk = parse_url($new_list[$ck]['mobile_url']);
            // 	$html_contents[$ttk['path']] = $store_content;
            // }
		}
	}
	// unset($finalList);
	if(!$html_contents){
		return [];
	}

	// echo '<pre>';
	// print_R($html_contents);
	// echo '</pre>';
	// exit;
	//对返回的数组做排序,防止数据章节错乱
	// $html_contents = NovelModel::sortHtmlData($html_contents);

	$store_data= $tdk =[];
	foreach($html_contents as $ggk =>$ggv){
		  $index  = substr($ggk, 0, -2);
		  $store_data[$index][]=$ggv;
	}

	if(!empty($store_data)){
		foreach($store_data as $gtkey=>$gtval){
			if(!$store_data) continue;
			$string = implode('',$gtval);//切割字符串,不能用,因为文章里含有，会错乱原因在这里
			$tdk[$gtkey]['chapter_name'] = $chapetList[$gtkey]['chapter_name'] ?? '';//章节名称
			$tdk[$gtkey]['chapter_mobile_link'] = $chapetList[$gtkey]['chapter_mobile_link'] ??'';
			$tdk[$gtkey]['chapter_link'] = $chapetList[$gtkey]['chapter_link'] ?? ''; //章节链接
			$tdk[$gtkey]['save_path'] = $chapetList[$gtkey]['path']??''; //保存的文件路径
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
function getContenetNew($data){
	foreach($data as $key =>$val){
		$item[$val['link_url']] = $val;
		$link_name = $val['link_name'];
		$item[$val['link_url']]['link_name'] = $link_name;
		$urls[$val['link_url']]= Env::get('APICONFIG.PAOSHU_HOST'). $val['link_url'];
		$t_url[]=Env::get('APICONFIG.PAOSHU_HOST'). $val['link_url'];
	}
	global $urlRules;
	//获取采集的标识
	$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['content'];
	//开启多线程请求,使用当前代理IP去请求，牵扯到部署需要再境外服务器
	$list = MultiHttp::curlGet($t_url,null,true);
	foreach($list as $key =>$val){

		$data = QueryList::html($val)->rules($rules)->query()->getData();
		$html = $data->all();
		$store_content = $html['content'] ?? '';
		$meta_data = $html['meta_data']??'';
		$href = $html['href'];
		$html_path = getHtmlUrl($meta_data,$href);
		if($store_content){
			$store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
			//替换文本中的P标签
			$store_content = str_replace("<p>",'',$store_content);
			$store_content = str_replace("</p>","\n\n",$store_content);
			//替换try{.....}cache的一段话JS这个不需要了
			$store_content = preg_replace('/{([\s\S]*?)}/','',$store_content);
			$store_content = preg_replace('/try\scatch\(ex\)/','',$store_content);
		}
		$store_c[$html_path] = $store_content;
	}
	foreach($item as $k =>$v){
		$item[$k]['content'] = $store_c[$k] ?? '';
	}
	$arr_list= array_values($item);
	return $arr_list;
}

/*
 * @param $str 需要处理的路径
 * @param $href 路径
 * @param $meta meta标记信息
 * @return mixed
 */
function getHtmlUrl($meta,$href){
	$meta_data = $meta;
	$real_path = explode('/',$meta_data);
	$str = $real_path[3] ?? '';
	$c_data = explode('-',$str);
	@$link = $href .$c_data[2].'.html';
	return  $link;
}

/*
 *  @note 半角和全角转换函数
 *  @param $str 处理的字符串
 * @param $href 第二个参数如果是0,则是半角到全角；如果是1，则是全角到半角
 * @return mixed
 */
function SBC_DBC($str,$args2=1) {
	$DBC = Array(
		'０' , '１' , '２' , '３' , '４' ,
		'５' , '６' , '７' , '８' , '９' ,
		'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
		'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
		'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
		'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
		'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
		'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
		'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
		'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
		'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
		'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
		'ｙ' , 'ｚ' , '－' , '　'  , '：' ,
		'．' , '，' , '／' , '％' , '＃' ,
		'！' , '＠' , '＆' , '（' , '）' ,
		'＜' , '＞' , '＂' , '＇' , '？' ,
		'［' , '］' , '｛' , '｝' , '＼' ,
		'｜' , '＋' , '＝' , '＿' , '＾' ,
		'￥' , '￣' , '｀'
	);
	$SBC = Array( //半角
		'0', '1', '2', '3', '4',
		'5', '6', '7', '8', '9',
		'A', 'B', 'C', 'D', 'E',
		'F', 'G', 'H', 'I', 'J',
		'K', 'L', 'M', 'N', 'O',
		'P', 'Q', 'R', 'S', 'T',
		'U', 'V', 'W', 'X', 'Y',
		'Z', 'a', 'b', 'c', 'd',
		'e', 'f', 'g', 'h', 'i',
		'j', 'k', 'l', 'm', 'n',
		'o', 'p', 'q', 'r', 's',
		't', 'u', 'v', 'w', 'x',
		'y', 'z', '-', ' ', ':',
		'.', ',', '/', '%', '#',
		'!', '@', '&', '(', ')',
		'<', '>', '"', '\'','?',
		'[', ']', '{', '}', '\\',
		'|', '+', '=', '_', '^',
		'$', '~', '`'
	);
	if($args2==0)
		return str_replace($SBC,$DBC,$str);  //半角到全角
	if($args2==1)
		return str_replace($DBC,$SBC,$str);  //全角到半角
	else
		return false;
}

/**
 * @note 替换中文字符|||清洗不必要的特殊字符
 *
 * @param $str 需要处理的字符
 * @return  string
 */

function replaceCnWords($str){
	if(!$str)
		return false;
	$newStr = preg_replace('/\*/','',$str);
	$newStr =  preg_replace('/（/', '',$newStr);
	$newStr =  preg_replace('/）/', '',$newStr);
	$newStr =  preg_replace('/:/', '',$newStr);
	$newStr =  preg_replace('/：/', '',$newStr);
	$newStr =  preg_replace('/>/', '',$newStr);
	$newStr =  preg_replace('/</', '',$newStr);
	$newStr =  preg_replace('/？/', '',$newStr);
	$newStr =  preg_replace('/"/', '',$newStr);
	$newStr =  preg_replace('/\'/', '',$newStr);
	$newStr =  preg_replace('/\|/', '',$newStr);
	$newStr =  preg_replace('/\r\n/','',$newStr);
	$newStr =SBC_DBC($newStr,1);
	return $newStr;
}


/**
 * @note 处理数组中的key进行添加
 *
 * @param $key_data array需要处理的数据
 * @return
 */
function handleArrayKey($key_data){
	if(!$key_data) return false;
	$new_data =[];
	foreach($key_data as $key =>$val){
		$tkey ="`$key`";
		$new_data[$tkey] = $val;
	}
	return $new_data;
}

/**
 * @note 检测获取含有代理的状态
 * @param $url str  url地址
 * @return bool
 */
function curlProxyState($url,$data=[]){
    if(!$url )
        return false;
	$proxy = $data['ip'] ?? ''; //代理IP
    $port = $data['port'] ?? ''; //端口
    $proxyauth ='';
    if(isset($data['username']) && isset($data['password'])){
        $proxyauth = $data['username'].':'.$data['password']; //用户名密码
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYPORT, $port);
    if(isset($data['username']) && isset($data['password'])){
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
    }
    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $curl_scraped_page = curl_exec($ch);
    $httpcode = curl_getinfo($ch);
    curl_close($ch);//关闭cURL会话
    return $httpcode;
}

/**
 * @note 检测URL是否为404
 * @param $url str  url地址
 * @return bool
 */
function check_url($url = ''){
	stream_context_set_default(
		array(
			'http' => array(
				'timeout' => 5,
			)
		)
	);
	$header = get_headers($url,1);
	if(strpos($header[0],'200')){
		return true;
	}
	if(strpos($header[0],'404')){
		return false;
	}
	if (strpos($header[0],'301') || strpos($header[0],'302')) {
		if(is_array($header['Location'])) {
			$redirectUrl = $header['Location'][count($header['Location'])-1];
		}else{
			$redirectUrl = $header['Location'];
		}
		return check_url($redirectUrl);
	}
}

/**
 * @note 获取评分随机小数
 *
 * @return str
 */
function getScoreRandom(){
	$min = 5; // 最小值
	$max = 9.9; // 最大值
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
function getRemoteIp(){
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
 * @note 写入文件操作
 *
 * @param $file_path str 文件路径
 * @param $content str 文件内容
 * @return str
 */
function writeFileCombine($file_path , $content){
	if(!$file_path || !$content)
		return false;
	$file = fopen($file_path,'w');
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
function writeFileAppend($file_path , $content)
{
	if(!$file_path || !$content)
		return false;
	$fp = fopen($file_path, 'a+');
	flock($fp, LOCK_EX) ;
	$sdfsd=fwrite($fp ,"\n$content\n");
	flock($fp, LOCK_UN);
	fclose($fp);
	return true;
}

?>
