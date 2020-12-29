<?php
/* 
 * soket request
 * author luxiaofeng
 * version 1.2(兼容ssl协议,兼容xml)
 * @param $debug 开启调试模式
 * @param $socket 句柄 兼容https协议
 */ 
class HttpClientRequest{
    public $timeout = 20;
    public $debug = false;
    private $port = 80;
    private $xml = false;
    private $is_update = false;
    private $method = "GET";

    
    /**
    * 构造函数
    */
	public function __construct(){
		
	}

    /**
    * @note 执行相关结果
    * @param $url string url地址
    * @param $port 端口
    * @param $timeout int 超时时间
    * @return string
    */
    public function run($url, $method=NULL, $port=NULL, $timeout=20) {
        $urlArr = parse_url($url);
        if($method)  $this->method = strtoupper($method);
        if(!$port && $urlArr['scheme'] == 'https') $this->port = 443;
        return $this->getRequest($urlArr);
    }


    /**
    * @note 获取网络相关的请求
    * @param $imgName 货物
    * @param $file_dir 
    * @paran $s_img 
    * @param return
    */
    private function getRequest($urlInfo='') {
        $header = $this->method." ".$urlInfo['path'].($this->method == "GET" ? '?'.$urlInfo['query'] :'')." HTTP/1.1\r\n";
        @$header .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
        $header .= "Host: ".$urlInfo['host']."\r\n";
        $header .= "Connection: Close\r\n";
        if($this->method == 'POST') {
            $header .= ($this->xml) ? "Content-Type: text/xml\r\n" :"Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length: ".strlen($urlInfo['query'])."\r\n\r\n";
            $header .= $urlInfo['query']."\r\n\r\n";
        } else {
            $header .= "\r\n";
        }
        //开启网络连接请求
        $fp = fsockopen($urlInfo['scheme'] == 'https' ? 'ssl://'.$urlInfo['host'] : $urlInfo['host'], $this->port, $errno, $errstr, $this->timeout);
        $headers = '';
        $comment = '';
        if(!$fp) {
            $this->error($errno.$errstr);
        } else {
            //从连接的节点位置发送数据
            if(!fputs($fp, $header, strlen($header))) {
                $this->error('发送数据失败');
            }
            //截取返回头信息
            while($str = trim(fgets($fp, 4096))) {
                $headers .= $str;
            }
            //获取返回正文
            while(!feof($fp)) {
                $comment .= fgets($fp);
            }
            fclose($fp);
        }
        $content= trim( $this->unchunk($comment) );
        return $content;
    }
     
    /**
    * 
    * @note 返回错误信息
    * @param $info array 数据信息 
    * @return bool   
    */
    private function error($info) {
        if($this->debug) {
            return $info;
        }
        return false;
    }

    /**
    *
    * 
    * @note  异常错误捕捉
    * @param $result array 结果集
    * @return array return   
    **/
    private function unchunk($result) {
        return preg_replace_callback(
            '/(?:(?:\r\n|\n)|^)([0-9A-F]+)(?:\r\n|\n){1,2}(.*?)'.
            '((?:\r\n|\n)(?:[0-9A-F]+(?:\r\n|\n))|$)/si',
            @create_function(
                '$matches',
                'return hexdec($matches[1]) == strlen($matches[2]) ? $matches[2] : $matches[0];'
            ),
            $result
        );
    }
}
?>
