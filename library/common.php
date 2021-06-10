<?php
//数组转换，主要导需要用
function Array_transdata($array,$field){
    $trans_data =array();
    if(!$array || !$field){
        return $trans_data;
    }
    //指定过滤的类型:1：trim 2:intval 3:floatval（暂时支持这三种过滤）
    $filterParam = array('1'=>'trim','2'=>'intval','3'=>'doubleval');
    if($field&&count($field)){
        if($array){
            foreach($array as $val){
                $innerData= array(); //控制每次都要清空指定数组
                foreach($field as $tk =>$tv){
                    //以指定的方式进行过滤处理优化
                    $innerData[]= $filterParam[$tv]($val[$tk]);
                }
                $trans_data[]=$innerData;
            }
        }else{
            $trans_data[]=array();            
        }
    }else{
        foreach($array as $key=>$val){
            $trans_data[]=array_values($val);
        }
    }
    $temp= [];
    if (!empty($trans_data)){
        $temp = $trans_data;
        if (isset($trans_data))
            unset($trans_data);
    }
    return $temp;
}

//分页封装
function paging($ob_pger="",$page_param){
    if(!is_array($page_param))return false;
    parse_str($page_param['param'], $query_string);
    $query_string['page'] = "#PAGE#";
    if(is_object($ob_pger)){
        $total_page = ceil($page_param['total'] / $page_param['page_size']);

        $page = $page_param['page'] > $total_page ? ( $total_page == 0 ? 1 : $total_page ) : $page_param['page'];

        return  $pager = $ob_pger->gen(array(
            'pager_html' => '<a href="#URL#">#PAGE#</a>',
            'curr_html' => '<a href="#URL" class="current">#PAGE#</a>',
            'page' => $page,
            'total_page' => $total_page,
            'base_url' => $page_param['method']."?" . http_build_query($query_string),
        ));
    }else{
        return $page_param['method']."?" . http_build_query($query_string);
    }
    return false;
}

function ForcDownload($file_path,$file_name){
    if($file_path==""||$file_name=="")return false;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$file_name);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    ob_clean();
    flush();
    readfile($file_path);
    exit;
}

/**
* @note 获取分页返回的数据信息
*
* @param [int] $[$page] [<页码>]
* @param [int] $[$page_sizs] <最大显示数>
* @author [xiaofeng] <[<luxiaofneg.200@163.com>]>
* @Date 2020-12-24
* @return object|bool
*/
function getPageOrSize($page,$page_size){
    $page_size = $page_size?intval($page_size):20;
    $page = intval($page)<1?1:intval($page);
    return array($page,$page_size);
}

 /**
  * @note 对emoji做表情编码
  *
  * @param $str 提交的内容
  * @return str
  */
function userTextEncode($str=''){
    if(!is_string($str)) return $str;
    if(!$str || $str=='undefined') return '';
 
    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback("/(\\\u[2def][0-9a-f]{3})/i",function($str){
        return addslashes($str[0]);
    },$text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
    return  json_encode($text);
}

/**
* @note 过滤字符串
*
* @param $office str 
* @return 
*/
         
 function filter_words($office=''){
    if(!$office) return '';
    $office = htmlspecialchars_decode($office); //转换一下格式反解出来
    $office = str_replace('<br />','\n',$office);
    $office = str_replace('</p>','\n',$office);
    $office = strip_tags($office);
    $text = json_encode($office); //暴露出unicode
    $text = preg_replace_callback('/\\\\\\\\/i',function($str){
        return '\\';
    },$text); //将两条斜杠变成一条，其他不动
    $office = json_decode($text,1);

    $key_arr = explode(PHP_EOL,$office);
    $str ='';
    if($key_arr&&is_array($key_arr)){
        foreach($key_arr as $v){
            if(empty($v)){
                 $str.= "<br>";
            }else{
                $str.="<p>".$v."</p>";
            }
        }
    }

    return $str;
}

 /**
* @note 过滤特殊字符 
*
* @param $str str 需要过滤的字符
* @return array
*/
     
 function filterHtml($str){
   if(!$str)return false;
    
    $str = htmlspecialchars_decode($str);

    $html=str_replace("<br></p >","\\n",$str);
    $html=str_replace("</p >","\\n",$html);
    $html=str_replace("<br>","\\n",$html);
    $html=strip_tags($html);


    return $html;
}


/* 截取合适长度的字符显示
*/
function cut_str($str, $length, $etc='...', $start = 0, $code='UTF-8'){
    $ret = '';
    $count = 0;
    $string = html_entity_decode(trim(strip_tags($str)), ENT_QUOTES, $code);
    $strlen = mb_strlen($string, $code);
    for($i = $start; (($i < $strlen) && ($length > 0)); $i++) {
        $c = mb_substr($string, $i, 1, $code);
        if(preg_match("#[\x{4e00}-\x{9fa5}]#iu", $c)){
            $count +=1;
        }else{
            $count += 0.5;
        }
        if($count > $length){
            break;
        }
        $ret .= $c;
    }
    $ret = htmlspecialchars($ret, ENT_QUOTES, $code);
    if($i < $strlen)
    {
        $ret .= $etc;
    }
    return $ret;
}

/**
 * 分割字符串
 * @param $str : 要分割的字符串
 * @param $cut_len : 间隔
 * @param $f : 分割的字符
 */
function cut_string($str='',$cut_len=0, $f = ' '){
    $len = mb_strlen($str,'utf-8');//获取字符串长度
    $content = '';
    for($i=0;$i<ceil($len/$cut_len);$i++){
        $content .= mb_substr($str,$cut_len*$i,$cut_len,'utf-8').$f;//遍历添加分隔符
    }
    $content = trim($content,$f);//去除字符串中最后一个分隔符
    return $content;
}

/**
 * @note 获取当前时间
 *
 * @param $format datetime 当前日期
 * @param $tiemset int 过期时间戳
 * @return object
 */
function MyDate($format='Y-m-d H:i:s', $timest=0)
{
    global $cfg_cli_time;
    $addtime = $cfg_cli_time * 3600;
    if(empty($format))
    {
        $format = 'Y-m-d H:i:s';
    }
    return gmdate ($format, $timest+$addtime);
}

/**
 * @note 过滤字符
 *
 * @param $str string 输入字符
 * @return object
 */
function stripStr($str){
    if(is_string($str)){
        if(!get_magic_quotes_gpc()) $str=stripslashes($str);
    }elseif(is_array($str)){
        if(!get_magic_quotes_gpc()){
            foreach($str as $key=>$val){
                $str[$key]=stripslashes($val);
            }
        }
    }
    return $str;
}
/**
* @note 转换数组按照key返回
*
* @param $trips object 转换的对象
* @param [str] $[field] [<字段>]
* @return object
*/

function double_array_exchange_by_field($trips,$field=''){
    if(!$trips || !$field)
        return [];
    $itemArr = [];
    foreach($trips as $v){
        if(!$v) continue;
        //按照某个key来进行输出入
        $itemArr[$v[$field]] = $v;
    }
    unset($trips);
    return $itemArr;
}

/**
 * 打印文件日志  一天一个
 * @param 文件名-不带后缀 $file_name  
 * @param str $str
 * @author wangyan   2013-06-27
 */
function printlog($str='',$file_name='file_log')
{
    $fp = fopen("{$file_name}_".date('Ymd').".txt", 'a+');  
    flock($fp, LOCK_EX) ;
    $sdfsd=fwrite($fp,strftime("%Y/%m/%d %H:%M:%S",time())."\t -- $str \t\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * @note cookie储存函数
 * @param $name str 存储名称
 * @param $value str 存储的具体值
 * @param $tiem int 设置对应的过期时间
 * @author xiaofeng   2020-06-27
 */
function cookie($name,$value='',$time=0){
    global $Global;
    if($time==0) $time = -1000;
    setcookie($name, $value, $Global['F_time']+$time, '/', '.'.$Global['F_host']);
    //bug with localhost
    if($Global['F_host'] == 'localhost')setcookie($name, $value, $Global['F_time']+$time);
}
/**
 * @note 得到cookie值
 * @param $name str cookie名称
 * @author xiaofeng   2020-06-27
 */
function getCookie($name){
    if(!empty($_COOKIE[$name])){
        return  $_COOKIE[$name];
    }else{
        return false;
    }
}

/**
 * @note 判断远程文件是否存在
 * @param url_file str url对应的文件
 * @author xiaofeng   2020-10-27
 */
function remote_file_exists($url_file){
    $headers = get_headers($url_file);
    if (!preg_match("/200/", $headers[0])){

        return false;

    }
    return true;
}

function webRequest($url,$method,$params,$header = []){
        //初始化CURL句柄
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($header)){
            curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
        }
        //请求时间
        $timeout = 30;
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        switch ($method){
            case "GET" :
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                if(is_array($params)){
                    $params = json_encode($params,320);
                }
                // echo $params;
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS,$params);
                break;
        }
        $data = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);//关闭cURL会话

        return $data;
    }

  /**
  * @note sha256加密
  *
  * @param 
  * @return 
  */
       
  function encrypt_sha256($str = ''){
        return hash("sha256", $str);
  }

   function Authorization($param = [],$mdkey =''){
        if(!$param){
            return false;
        }
        /*
           首先将请求参数中的每一个一级字段按照0-9-A-Z-a-z的顺序排序（ASCII字典序），若遇到相同首字母，则看第二个字母，以此类推。注意：如goods、subOrders等嵌套结构字段，内部子字段无需排序。

        排序后的参数以key=value形式使用“&”字符连接,并拼接上通讯密钥key值（注意：通讯密钥key直接拼接），即为待签名字符串。
            * */
        //获取sign参数获取签名验证
        ksort($param);
        reset($param);

      
        if($param){
            $options = '';
            foreach($param as $key =>$item){
                // if(!$item) continue; //异常判断
                if(!is_array($item)){ //普通的数据格式
                    $options .= $key . '=' . $item .'&';
                }else{//处理里面有多多维数组的的 --主要银联那边不需要转码，原样返回
                    $options .=$key . '=' . json_encode($item,JSON_UNESCAPED_UNICODE).'&';
                }
            }
            $options = rtrim($options, '&');//存在转义字符，那么去掉转义
            if(get_magic_quotes_gpc()){
                $options = stripslashes($options);
            }
           
            //#签名规则：用sha256进行上报加密
            //#算法：所有的字段处理排序后用&链接和md5通讯串链接后返回sign
            //采用sha256加密

            //$pattern = '/[^\x00-\x80]/'; 判断含有中文
            //
             // $options = str_replace('\u6d4b\u8bd5', '测试', $options);
            // $options = str_replace('\u7535\u8d39', '电费', $options);
            if(empty($mdkey)){ //做一个兼容
                $mdkey = $this->mdkey;
            }
            $str = $options.$mdkey;
            // echo "待验签:".$str;
            // echo "<hr/>";
            //生成了秘钥
            $sign = encrypt_sha256($str);

            // echo "得到的sign:".$sign;
            // echo "<hr/>";
            // echo $sign;
            // exit;
            $param['sign'] = $sign;
            // echo '<pre>';
            // print_R($param);
            // echo '</pre>';
             return $param;
        }
        return [];
    }

  //判断含有中文
  function checkChineseStr(){
        $pattern = '/[^\x00-\x80]/';
        if(preg_match('/[^\x00-\x80]/',$str)){
           return 1;
        }else{
          return 2;
        }
  }

  /**
 * 将数组转换为字符串
 *
 * @param   array   $data       数组
 * @param   bool    $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return  string  返回字符串，如果，data为空，则返回空
 */
function array2string($data, $isformdata = 1) {
    if($data == '') return '';
    if($isformdata) $data = new_stripslashes($data);
    return addslashes(var_export($data, TRUE));
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
    if(!is_array($string)) return stripslashes($string);
    foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
    return $string;
}

/**
 * 将字符串转换为数组
 *
 * @param   string  $data   字符串
 * @return  array   返回数组格式，如果，data为空，则返回空数组
 */
function string2array($data) {
    $array =array();
    if($data == '') return array();
    @eval("\$array = $data;");
    return $array;
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string){
    if(!is_array($string)) return addslashes($string);
    foreach($string as $key => $val) $string[$key] = new_addslashes($val);
    return $string;
}
?>