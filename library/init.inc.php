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
 
define('IN', true);

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
// require_once ROOT.'include/pimage.cls.php';
// include_once ROOT.'include/ec.func.php';
@extract($_GET);
@extract($_POST);
@extract($_FILES);
//@extract($_POST);


// $qplus = new QPlusOpen($Global['qplus']['app_id'],$Global['qplus']['app_secret']);

//$mysql_obj = new Mysql_class();
$mysql_obj =new Mysql_class();
$http_client= new curl_http();

$socket_handle = new HttpClientRequest(); //定义socket链接类

//$qdb = new sql_db($Q_MySQL['hostname'], $Q_MySQL['username'], $Q_MySQL['password'], $Q_MySQL['database']);

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

$Global['F_userip'] = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : @$_SERVER['REMOTE_ADDR']);
$Global['F_host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :'';

$Global['F_time'] = time();


