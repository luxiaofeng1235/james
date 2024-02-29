<?php
/**
*名称:init.inc.php
*作用:全局配置文件
*说明:
*版权:
*作者:Red	QQ:316765128
*时间:2011/7/28
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
error_reporting(E_ALL);   //屏蔽所有错误
define('ROOT', str_replace("\\",'/',substr(dirname(__FILE__), 0, -7)));//ROOT => 根目录
define('BASE_PATH',ROOT);//定义基础项目的基础路径
define('IN', true);
define('DS', DIRECTORY_SEPARATOR);

#$mtime = explode(' ', microtime());
#$starttime = $mtime[1] + $mtime[0];
$Global = array();

function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
session_start();
header("Vary: Accept");
header("Content-Type: text/html; charset=utf-8");
header('Cache-control:Private');
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
require_once ROOT.'library/mysql_class.php';
require_once ROOT.'library/pinyin.func.php';
require_once ROOT.'library/common.php'; #加载公用函数
require_once ROOT.'library/curl_http.php';
require_once ROOT.'library/global.cfg.php';
require_once ROOT.'library/HttpClientRequest.php';
require_once ROOT.'library/newupload.cls.php';#文件上传
require_once ROOT.'library/Http.php'; //多线程请求curl支持post和get
require_once ROOT.'vendor/autoload.php';//自动加载第三方类的初始文件
require_once ROOT.'library/Env.php'; //环境变量
require_once ROOT.'library/redis_codes.php';
require_once ROOT.'library/mysql_class_pro.php';//线上操作库信息
require_once ROOT.'library/novelModel.php';//小说业务模型类


//小说的采集规则配置
$urlsRules  = [];
if (is_file(dirname(__DIR__) . '/config/urls_class.php')) {
    $urlsRules = require  dirname(__DIR__) . '/config/urls_class.php';
}

// require_once ROOT.'include/pimage.cls.php';
// include_once ROOT.'include/ec.func.php';
@extract($_GET);
@extract($_POST);
@extract($_FILES);
//@extract($_POST);


// $qplus = new QPlusOpen($Global['qplus']['app_id'],$Global['qplus']['app_secret']);

//$mysql_obj = new Mysql_class();
$mysql_obj =new Mysql_class(); //本地测试库
$mysql_obj_pro = new Mysql_class_pro(); //线上操作库
$http_client= new curl_http();
$redis_data = new redis_codes();

$socket_handle = new HttpClientRequest(); //定义socket链接类

//$qdb = new sql_db($Q_MySQL['hostname'], $Q_MySQL['username'], $Q_MySQL['password'], $Q_MySQL['database']);

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

$Global['F_userip'] = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : @$_SERVER['REMOTE_ADDR']);
$Global['F_host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :'';

$Global['F_time'] = time();


