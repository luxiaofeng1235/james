<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2021年03月11日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :list.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:同步三级联动街道数据
// ///////////////////////////////////////////////////

ini_set("limit_memory",'8000M');
set_time_limit(600);
//获取当前的路径位置
$dirname = dirname(dirname(__FILE__)); //返回根目录
$dirname = str_replace('\\', '/', $dirname);
require_once ($dirname."/library/init.inc.php");
$mysql_init = $mysql_obj; //主要便于赋值操作
//导入的sql
function import_sql($data,$table_name=''){
	if(!$data || !$table_name)
		return false;
	$sql="insert into ".$table_name." (`city_code`,`name`,`upid`,`ctime`) values ";
	$val_save ='';
	foreach($data as $key =>$val){
		 $arr = array();
		 foreach($val as $k =>$v){
		 		$arr[]  =  "'$v'";
		 }
		 $val_save.="(".implode(',', $arr)."),";
	}
	$val_save  = rtrim($val_save,',');
	if($val_save){
		$sql .= $val_save;
	}
	return $sql;
}


/**
* @note 检验是否已经导入了
*
* @param [number] $[upid] [<上级联动ID>]
* @param [str]  $[table_name] [<表名称>]
* @author [xiaofeng] <[<luxiaofneg.200@163.com>]>
* @Date 2020-1221
* @return object|bool
*/
function get_street_code($upid ='',$table_name=''){
	$upid = intval($upid);
	$table_name = trim($table_name);
	if(!$upid)
		return false;
	global $mysql_init;
	$sql = "select count(1) as num from ".$table_name." where upid=".$upid;	 

	$rows = $mysql_init->fetch($sql,$mysql_init->db_master); //查询主
	return intval($rows['num']);
}

$ts_condition = "level = 3"; //把区线得数据查出来
$table_name ='jishigou_common_distrinct';

$sql = "select count(id) as num from $table_name where ".$ts_condition;
$pagesize = 5000;

/**
* @note 正则切割中文字符
*
* @param [string] $[str] [<需要切割的字符串>]
* @author [xiaofeng] <[<luxiaofneg.200@163.com>]>
* @Date 2020-1221
* @return string
*/
function mb_str_split($str){  
    return preg_split('/(?<!^)(?!$)/u', $str );  
} 

#处理的业务类型
$oper = isset($_GET['oper'])  ? trim($_GET['oper']) : 1;
$page = $_GET['page'] ?? 1;
if($oper == 1){
	$table_name = 'bs_province';
	$level = 1;
	$code_type ='PROVINCE_CODE';
	$show_name = 'PROVINCE_NAME';
}else if($oper ==2){
	$table_name ='bs_city';
	$code_type ='CITY_CODE';
	$show_name = 'CITY_NAME';
	$level = 2;
}else if($oper ==3){
	$table_name ='bs_area';
	$code_type ='AREA_CODE';
	$show_name = 'AREA_NAME';
	$level = 3;
}else{
	$table_name ='bs_street';
	$code_type ='STREET_CODE';
	$show_name = 'STREET_NAME';
	$level =4;
}

//关联查询
// $sql = "select dg.`{$code_type}`,dg.`{$show_name}`,dg.LNG,dg.LAT,common.id,dg.`SORT`,dg.`GMT_MODIFIED`,common.`city_code` from jishigou_common_distrinct common left join ".$table_name." dg on dg.{$code_type}=common.city_code
// 	where common.level = '{$level}' and dg.`{$code_type}` is not null and common.lng='' and dg.LNG!='' " ;
// 	
$sql = "select * from jishigou_common_distrinct WHERE name!='市辖区' and  (LENGTH(pin_yin)=2 or LENGTH(pin_yin)=3) and name!='阿里地区' order by id asc";
	     

if($level ==4){
	// $sql .=" limit ".($page-1)*$pagesize.','.$pagesize;
}

$str='luoheshi
xishiqu
jingde
zhaoan
wudi
xupu
luxi
cenxi
xiqu
gulin
aba
luxi
mengla
sajia
yufa
qiema
zhenzi
tuoli
zizhong
caohe
liwu
xizhong
dahulun
zhuolu
bozhen
fuzhen
guxian
manghe
guxian
gujiang
lizhai
que
tuban
yidie
kezhen
huzhen
dazhe
gushi
feihe
daxu
xuzhen
suixi
fuyu
changpu
xunyu
yiqi
qizili
chahe
wuxu
yanji
guzhen
huhu
feihe
xutuan
muzhen
songxi
qitao
guyong
suixi
heshi
fotan
duxun
banzai
xushi
fushi
juxi
daixi
ehu
wubei
lashi
lixi
gushi
maao
heshi
huangxi
maquan
sili
gubei
daa
lishi
qulai
guxian
heshi
mashi
sixi
dushi
libei
aoxi
heshi
maxu
lixu
jiandi
huqiao
ehu
zibu
guxian
dakuang
tuanli
xizou
caohe
zhaizhen
datuan
yiwen
mizhen
zizhen
zhenan
dashi
huazi
aji
sihe
yima
taohe
emu
wujing
yexie
xujing
yaxi
ehu
hudai
huangtu
xizhu
gupi
ahu
huangli
mudu
liuhe
juzhen
jinghe
dasi
jishi
yishi
mazhu
dalan
chunhu
sixi
ganpu
daixi
hefu
sian
zhangwu
lizhu
ruao
yafan
lipu
nianli
huzhen
daixi
danian
guying
dawei
yuzhen
guxian
zhaizhen
guxian
xiguo
chuhe
guxian
guxian
yanji
liaodi
mafan
xuezi
jizhong
zhuhu
moshi
guyi
lishi
cenhe
mishi
bianhe
zishi
dayuan
chahe
caohe
dafan
dahu
huanxi
miandu
qizi
xushi
wushi
heshi
reshi
moshi
lucidu
zhexi
longbo
gongba
xiaopu
daxu
mashi
yushi
yuanhe
chaxi
dalan
pushi
lishi
mashi
wujing
gushi
xilu
xiqiao
daao
douhu
heliao
tanba
qijing
aozai
maxu
tonghu
mabei
huzhen
huliao
rezhe
dazhe
ebu
suqu
heshi
zishi
xiao
anbu
dacheng
zhenan
wuxu
suxu
luxu
zhemu
daxu
dadong
daxu
muzi
madong
dadong
xiyin
yixu
dee
wuxu
lüfeng
zhenan
xituo
ganxi
guyi
heshi
washi
hushi
gulin
fuyi
manao
zhenxi
liuma
lishi
muxi
danan
nanmu
muya
liaoye
juexi
yile
dongdi
muai
haoba
zhenyu
guxian
lushi
heshi
dushi
sima
wuhuang
zhenzi
lujia
aba
qiongxi
gaer
niga
qiaowo
mishi
lugu
sidu
jianba
douru
hushi
yaopu
nidang
mingu
aoshi
gudong
jiasa
pupiao
zhenan
guixi
lashi
mengda
mengma
mengka
mengku
mengsa
yisa
mengla
juli
sajia
zuan
qizhen
yingge
beixi
gushi
puzhen
fazhen
mazhen
mazhen
hazhen
wuzhen
jiyuan
jizhen
wuzhen
xizhen
yinghu
yanba
xuanwo
linhe
sihao
yima
gushan';

$str_find =preg_split("/\n/", $str);
// echo '<pre>';
// print_R($str_find);
// echo '</pre>';
// exit;



     

$list = $mysql_init->fetchAll($sql,$mysql_init->db_slave);
if($list){
	foreach($list as $key =>$value){
		if(!$value) continue;
		$name = trim($value['name']);
		$name =str_replace('直辖市', '', $name);
		$name =str_replace('市', '', $name);
		$name =str_replace('省', '', $name);
		$name =str_replace('自治区', '', $name);
		$name =str_replace('自治州', '', $name);
		$name =str_replace('地区', '', $name);
		$name =str_replace('区', '', $name);
		$name =str_replace('特别行政', '', $name);
		
		// $name =str_replace('自治', '', $name);
		//

		if(strpos($name, '县')){
			//处理2个字得县
			$str = explode('　', $name);
			$str= array_filter($str);
			if(is_array($str)&&count($str)>1){
				//这种是两个字得不用处理
			}else{
				$name =str_replace('县', '', $name);
			}
		}
		// $name =str_replace('街道', '', $name);

		// if(end(mb_str_split($name)) =='镇'){

		// 	$name =str_replace('镇', '', $name);
	
		// }
		



		

		$pinyin =isset($str_find[$key]) ? ucfirst(trim($str_find[$key])): '';
		// $pinyin = ucfirst(Pinyin('漷县' ,'UTF-8'));
		$city_code = trim($value['city_code']);
		$id  = intval($value['id']);
		$update_where = "id = '".$id."' and city_code='".$city_code."' limit 1";



		

		// echo $name.'='.$pinyin;
		// echo "<br/>";
		 

		$update_data = array(

			'pin_yin'	=>$pinyin,
			// 'lng'	=> trim($value['LNG']),
			// 'lat'	=>trim($value['LAT']),
			// 'sort'	=>intval($value['SORT']),
			// 'utime'	=>trim($value['GMT_MODIFIED']),
		);
		$sql = "update jishigou_common_distrinct set";
		foreach($update_data as $k =>$v){
			$sql .=" $k='$v',";
		}

		     
		$sql =rtrim($sql,',');
		$sql .=" where ".$update_where;
		
	// 	echo $sql;
	// echo "<br/>";
		     
		     
		$res = $mysql_init->query($sql,$mysql_init->db_master);
		if($res){
			echo "index:{$key} code : ".$value['city_code']." show_name : ".$value['name']." 更新成功"."<br />";
		}else{
			echo $value['city_code']."  error"."<br />";
		}
  
	}
}else{
	echo "no data \r\n";
}
//1479962
?>