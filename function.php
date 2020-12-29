<?php

function checkPhoneNumber(){
	return 12;
	//获取当前的进程号
	posix_getpid();
}

if(!function_exists('posix_getpid')){
    function posix_getpid(){
        return getmypid();
    }
}

?>