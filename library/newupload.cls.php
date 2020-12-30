<?php
/**
*名称: .php
*作用:
*说明:
*版权: 亿度网络
*作者: Red	QQ:316765128
*时间:
**/
//upfile($path, $format = '', $maxsize = 0, $over = 0)
//参数说明$path:路径
class UploadFile{
	var $components = array('AvaFunc');

	//上传文件信息
	var $filename;

	// 随机字符串
	var $rd;

	// 保存名
	var $savename;

	// 保存路径
	var $savepath;

	// 文件格式限定，为空时不限制格式
	var $format = array('image/jpeg','image/pjpeg','image/gif','image/x-png','image/png');

	//因为png和bmp格式无法正常在界面显示。
	//var $format = array('image/jpeg','image/pjpeg','image/gif');
	//文件最大字节
	var $maxsize = 5242880;//5M

	//错误代号
	var $errno = 0;

	//路径结构
	var $pathmode = '';

	function delDirAndFile( $dirName )
	{
		if ( $handle = opendir( "$dirName" ) ) {
		   while ( false !== ( $item = readdir( $handle ) ) ) {
		   if ( $item != "." && $item != ".." ) {
		   if ( is_dir( "$dirName/$item" ) ) {
		   delDirAndFile( "$dirName/$item" );
		   } else {
		   if( unlink( "$dirName/$item" ) );//echo "成功删除文件： $dirName/$item<br />\n";
		   }
		   }
		   }
		   closedir( $handle );
		   if( rmdir( $dirName ) );//echo "成功删除目录： $dirName<br />\n";
		}
	}

	/*
	* 功能：检测并组织文件
	* $path 保存路径
	* $form 文件域名称
	* $format 文件格式(用逗号分开)
	* $maxsize 文件最大限制
	* $over 复盖参数
	*/
	function upload($path,$filear,$maxsize = 0){
		$this->rd = rrand(26,3);
		$this->savepath = $path.substr($this->rd,0,2).'/'.substr($this->rd,2,2).'/';
		$this->maxsize = !$maxsize ? $this->maxsize : $maxsize;//文件最大字节

		if(!isset($filear)){$this->halt('指定的文件域名称不存在。');}
		//不存在则创建
		if(!is_dir($this->savepath)){
			mkdir($this->savepath,0777,true);
		}
		if(!is_writable($this->savepath)){
			$this->halt('指定的路径不可写。');
		}
		if(!is_array($filear['name'])){		//上传单个文件
			$ext = $this->getext($filear["name"]);//取得扩展名
			$this->savename = substr($this->rd,4,22).'.'.$ext;
			$this->copyfile($filear);
			return substr($this->rd,0,2).'/'.substr($this->rd,2,2).'/'.$this->savename;
		}
	}

	/*
	* 功能：检测并复制上传文件
	* $filear 上传文件资料数组
	*/
	function copyfile($filear){
		if($filear['size'] > $this->maxsize){
			//$this->halt('上传文件 '.$filear['name'].' 大小超出系统限定值['.$this->maxsize.' 字节]，不能上传。');
			throw new Exception('上传文件 '.$filear['name'].' 大小超出系统限定值['.$this->maxsize.' 字节]，不能上传。');
		}
		if(file_exists($this->savepath.$this->savename)){
			//$this->halt($this->savename.' 文件名已经存在。');
			throw new Exception($this->savename.' 文件名已经存在。');
		}
		if(!in_array($filear['type'],$this->format)){
			//$this->halt(' 文件格式不允许上传。');
			throw new Exception($filear['type'].'文件格式不允许上传。');
		}
		if(!copy($filear['tmp_name'], $this->savepath.$this->savename)){
			$errors = array(
			0=>'文件上传成功',
			1=>'上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。 ',
			2=>'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。 ',
			3=>'文件只有部分被上传。 ',
			4=>'没有文件被上传。 ');
			//$this->halt($errors[$filear['error']]);
			throw new Exception('上传文件 '.$filear['name'].' 大小超出系统限定值['.$this->maxsize.' 字节]，不能上传。');
		}else{
			chmod($this->savepath.$this->savename, 0777);
			@unlink($filear['tmp_name']);	//删除临时文件
		}
	}
	/*
	* 功能: 取得文件扩展名
	* $filename 为文件名称
	*/
	function getext($filename){
		if($filename == "") return;
		$ext = explode(".", $filename);
		
		$new_ext = array_reverse($ext);
		Return $new_ext[0];
	}
	/*
	* 功能：错误提示
	* $msg 为输出信息
	*/
	function halt($msg){exit('<strong>注意：</strong>'.$msg);}
}
?>
