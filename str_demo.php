<?php

/**
 * 将数组转换为字符串
 *
 * @param	array	$data		数组
 * @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return	string	返回字符串，如果，data为空，则返回空
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
 * @param	string	$data	字符串
 * @return	array	返回数组格式，如果，data为空，则返回空数组
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

##测试数据
$list = array(
    'app_content' =>array(
        'username' =>111,
        'password'  => 3334
    ),
    'send_message' =>33222,
    'status' =>array(
        'plist'=>1,
        'array'=>array(
            'http',
            'arr'
        )
    )
);

//解析数据格式
$mobile_data = array2string($list);
if(!empty($mobile_data)){
    //进行转换
    $result = string2array(stripslashes($mobile_data));
    $aids = is_array($result) && !empty($result) ? $result['status'] : array();
    echo '<pre>';
    print_R($aids);
    echo '</pre>';
    exit;
}
echo '<pre>';
var_dump($mobile_data);
echo '</pre>';
exit;

$list = checkdata();
echo '<pre>';
print_R($list);
echo '</pre>';
exit;