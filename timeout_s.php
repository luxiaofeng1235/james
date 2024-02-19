<?php
$url ='https://www.cnblogs.com/Zjmainstay/archive/2012/03/08/PHP_FUNCTION_file_get_contents.html';
//设置内存的最大空间可腾出的执行空间
ini_set('memory_limit', '3000M');
//设置socket流的超时时间默
ini_set('default_socket_timeout', 3);
if($data = file_get_contents($url)) {
    echo $data;
}else {
    echo 'Timeout';
}
?>