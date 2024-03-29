<?php


require_once (__DIR__ .'/library/SysCrypt.php');


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

$item = readFileData('./a.html');

$sc = new SysCrypt('novelCms');
$text = $item; //需要加密的数据

$str = $sc -> php_encrypt($text);

$info = $sc->php_decrypt($str);
echo '<pre>';
print_r ($info);
echo '</pre>';
 
?> 
