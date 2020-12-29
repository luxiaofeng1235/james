<?php
/**
*作用:全局变量设置
*说明:
*版权:
*作者:Red	QQ:316765128
*时间:2009-11-13
**/
if(!defined('IN')) exit('Access Denied');
ini_set('date.timezone','Asia/Shanghai');
$Global['time_zone']= '+8';
$Global['time_delay']= '';

$Global['cookie_prefix']='Cw2G9lk)';
$Global['cookie_hash']='5@vS2v8)#v71';
$Global['lang']='chs';

$Global['picurl'] = 'http://q.chinapet.com/upload/';   //线上地址
//$Global['picurl'] = 'http://petq.op/upload/';   //本地调试地址
 
//qq key 无用
$Global['qq']['appid'] = '218315';
$Global['qq']['appkey'] = 'edb6a92cf91174f8f7b78d5c19659922';
$Global['qq']['callback'] = 'http://q.chinapet.com/qq_callback.php';

//Q+ key
$Global['qplus']['app_id'] = '200002322';
$Global['qplus']['app_secret'] = 'MDjPsSsP85tXRLS6';


$Global['pet']['sex'] = array('母','公');
$Global['pet']['sort'] = array('狗狗','猫猫','小宠','水族','其它');
$Global['pet']['kind'] = array('正常','征婚','需要被领养','已经去世了','已送人','已走失');

//优惠卷分类
//道具分类
//勋章卷分类
//设置时区
date_default_timezone_set('Asia/Chongqing');


?>