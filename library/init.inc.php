<?php
/**
*名称:init.inc.php
*作用:全局配置文件
*说明:
*版权:
*作者:Red	QQ:513072539@qq.com
*时间:2024/3/28
**/
//echo $_SERVER['SERVER_ADDR'];
//die;
/*
if($_SERVER['SERVER_ADDR']=="127.0.0.1"){
	error_reporting(E_ALL);
}else{
	error_reporting(0);
}

 */
//error_reporting(E_ALL);
// error_reporting(E_ALL);   //屏蔽所有错误
define('ROOT', str_replace("\\",'/',substr(dirname(__FILE__), 0, -7)));//ROOT => 根目录
define('BASE_PATH',ROOT);//定义基础项目的基础路径
define('IN', true);
define('DS', DIRECTORY_SEPARATOR);

date_default_timezone_set('Asia/Shanghai');


#$mtime = explode(' ', microtime());
#$starttime = $mtime[1] + $mtime[0];
$Global = array();
// ob_end_clean();
function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();

// session_start();
// header("Vary: Accept");
// header("Content-Type: text/html; charset=utf-8");
// header('Cache-control:Private');
// header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
// require_once ROOT.'library/mysql_class.php';
// require_once ROOT.'library/pinyin.func.php';
require_once ROOT.'library/common.php'; #加载公用函数
// require_once ROOT.'library/curl_http.php'; //curl请求类
// require_once ROOT.'library/Http.php'; //多线程请求curl支持post和get
require_once ROOT.'vendor/autoload.php';//自动加载第三方类的初始文件
require_once ROOT.'library/Env.php'; //环境变量
require_once ROOT.'library/logger.php'; //轻量日志工具
require_once(ROOT.'library/PdoPool.class.php'); //pdo-mysql连接池类
require_once ROOT.'library/redis_codes.php'; //redis类

// require_once ROOT.'library/proxy_network.php';//小说业务模型类
// require_once ROOT.'library/curl_pic_multi.php';//多线程下载图片类
require_once ROOT.'library/guzzleHttp.php';//guzzlehttp的抓取实现类
require_once ROOT.'library/ClientModel.php'; //clientModel的业务类
require_once ROOT.'library/BanjiashiModel.php'; //佳士小说业务类

require_once ROOT.'library/Bqg24Model.php'; //笔趣阁24业务类
require_once ROOT.'library/NovelModel.php';//小说业务模型类-paoshu8
require_once ROOT.'library/StoreModel.php';//小说业务模型类-异步swoole实现
require_once ROOT.'library/BiqugeRequestModel.php';//笔趣阁模型类-异步swoole实现
require_once ROOT.'library/DouyinModel.php'; //抖音业小说模型类

require_once ROOT.'library/GoFound.php'; //gofound搜索
require_once ROOT.'library/XunSearch.class.php'; //迅搜全文检索
require_once ROOT.'biqugeService/CommonService.php';
require_once ROOT.'biqugeService/BiqugeService.php'; //笔趣阁的线上接口拉取接口
require_once ROOT.'library/ChapterAes.class.php'; //笔趣阁章节目录内容解码
require_once ROOT.'library/BiqugeModel.php'; //笔趣阁的主要核心业务


//小说的采集规则配置
$urlRules  = [];
if (is_file(dirname(__DIR__) . '/config/urls_class.php')) {
    $urlRules = require  dirname(__DIR__) . '/config/urls_class.php';
}

//加载配置的广告
$advertisement= [];
if (is_file(dirname(__DIR__) . '/config/advert.php')) {
    $advertisement = require  dirname(__DIR__) . '/config/advert.php';
}

@extract($_GET);
@extract($_POST);




$mysql_obj =new ConnectionPool(); //mysql连接
$redis_data = new redis_codes(); //redis类 - 延迟初始化，避免在测试时连接失败



// $socket_handle = new HttpClientRequest(); //定义socket链接类
//$qdb = new sql_db($Q_MySQL['hostname'], $Q_MySQL['username'], $Q_MySQL['password'], $Q_MySQL['database']);
$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

$Global['F_userip'] = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : @$_SERVER['REMOTE_ADDR']);
$Global['F_host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :'';

$Global['F_time'] = time();


//cli模式下进行刷新
if ( is_cli()){
	ob_end_flush();
}
