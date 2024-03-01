<?php
$dirname = dirname(__FILE__);
$dirname =str_replace("\\", "/", $dirname) ;
ini_set('memory_limit','9000M');
require_once($dirname.'/library/init.inc.php');
for ($i=0; $i <10 ; $i++) {
    printlog('我是来打印测试日志的11----'.$i);
}
?>