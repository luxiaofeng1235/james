<?php
//1111
class Sftp {
    // 初始配置为NULL
    private $config = NULL;
    // 连接为NULL
    private $conn = NULL;
    //sftp resource
    private $ressftp = NULL;
    // 初始化
    public function __construct()
    {
        $this->config =[
        	'host'=> '140.206.74.65',
        	'username'=> 'JXYJBY_fan',
        	'port'=>'22',
        	'password' =>'g^7zb5AL',
        ];
        $this->connect();
    }
    public function connect()
    {
        $this->conn = ssh2_connect($this->config['host'], $this->config['port']);
        if( ssh2_auth_password($this->conn, $this->config['username'], $this->config['password']))
        {
            $this->ressftp = ssh2_sftp($this->conn);
        }else{
            return "用户名或密码错误";
        }
    }
    // 下载文件
    public function downftp($remote, $local)
    {
        return copy("ssh2.sftp://{$this->ressftp}".$remote, $local);
    }
    // 文件上传
    public function upftp( $local,$remote, $file_mode = 0777)
    {
        return copy($local,"ssh2.sftp://{$this->ressftp}".$remote);
    }
    //创建目录
    public function ssh2_sftp_mchkdir($path)  //使用创建目录循环
    {
        ssh2_sftp_mkdir($this->ressftp, $path,0777,true);
    }
    //判段目录是否存在
    public function ssh2_dir_exits($dir){
        return file_exists("ssh2.sftp://{$this->ressftp}".$dir);
    }
}

//本地文件目录
$localpath = "D:/ab.txt";		//本地文件路径
$serverpath='/upload/'.date('Ymd');       //远程目录（需要上传到的目录）
$sftp = new Sftp();
$serveFile = $serverpath.'/e.txt';
$res = $sftp->ssh2_dir_exits("$serverpath");
//如果目录存在直接上传
if($res){
    $sftp->upftp($localpath,$serveFile);
}else{
    $sftp->ssh2_sftp_mchkdir($serverpath);
    $sftp->upftp($localpath,$serveFile);
}
?>