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
    return $trans_data;
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

/**:二维数组转换为一维
 * @param $data
 * @param $field_array
 */
function Array_transfrom($data,$field_array=array()){
    $rules=array('1'=>'trim','2'=>'intval','3'=>'doubleval',/*'4'=>'Y-m-d','5'=>'Y-m-d H:i:s'*/);

    if(!is_array($data)||!is_array($field_array))return false;
    $new=array();
    if(count($field_array)){
        foreach($data as $key=>$val){
            foreach($field_array as$k=>$va){
                if(isset($val[$k])){
                    $new[$key][]=$rules[$va]($val[$k]);
//                    $new[$key][$k]=$val[$k];
                }
            }
        }
    }else{
        foreach($data as $key=>$val){
            $new[]=array_values($val);
        }
    }

    return $new;
}


/**:二维数组转换为一维
 * @param $data
 * @param $field_array
 */
function Array_transfrom_new($data,$field_array=array(),$pipeline=null,$redis_key=null){
    if(!is_array($data)||!is_array($field_array))return false;
    $rules=array('1'=>'trim','2'=>'intval','3'=>'floatval',/*'5'=>'Y-m-d','6'=>'Y-m-d H:i:s'*/);
    $new=array();
    if(count($field_array)){
        foreach($data as $key=>$val){
            foreach($field_array as$k=>$va){
                if(isset($val[$k])){
                    $new[$key][]=$rules[$va]($val[$k]);
//                    $new[$key][$k]=$val[$k];
                }
            }
            if($pipeline!=null&&$redis_key!=null){
                $pipeline->rpush($redis_key,json_encode($new[$key]));
            }
        }
    }else{
        foreach($data as $key=>$val){
            $new[]=array_values($val);
        }
    }
    if($pipeline!=null&&$redis_key!=null)return;
    return $new;
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

//获取当前时间
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
//过滤字符
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
* @param [str] $[filed] [<字段>]
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
        Return $_COOKIE[$name];
    }else{
        Return false;
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

?>