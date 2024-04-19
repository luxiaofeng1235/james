<?php
/*
 * 同步跑书吧最新入库和最新更新的章节信息

 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
require_once dirname(__DIR__).'/library/init.inc.php';
$url = Env::get('APICONFIG.PAOSHU_HOST');
use QL\QueryList;

$arr = webRequest($url,'GET');
echo '<pre>';
print_R($arr);
echo '</pre>';
exit;
?>