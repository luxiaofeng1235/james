<?php
//验证一个字符中只能包含大于0的对应数字222
class regGx{
	public static function checkEmail($str){
		if(!$str)
			return false;
		if(preg_match('/^[1-9,]+$/', $str)){
			return true;
		}else{
			return false;
		}
	}
}


//输入一列字符创，进行排序

class sort{

	private $str;
	public function __construct(){
		$this->str = strtolower($str);
	}
	//切割字符串
	public function explodes($str=''){
		if(!$str) return array();
		$arr = explode(' ', $str);
		return is_array($arr) ? $arr : array($this->str);
	}
	//缓存穿刺使用的策略是：设置对应每次过期的时间间隔错开，比如第一个设置10秒，第二个和第三个设置20和40秒等，上游服务端nginx采用限流来进行并发数的显示。

	//如果不用cookiecunchu可以考虑用其他的方式 例如铜鼓ourl传递sid参数，或者利用文件韩村或者redis的策略来进行。
	//开启php.ini的use_trans_sid的方式实现跨页面查找

	//find -name '*.txt'|xargs -perl -pe -i '|s|测试|data|g'
	//sed -i s/test/arr/g./arr.txt
	// //自动架子啊函数
	// public function __aotoload($class_name=''){

	// }
	//gbk占用两个字符 $str = ’hello你好世界’;

	//echo strlen($str); 输出13array+combine 铜鼓欧联哥哥数组创建一个新的住宿 range范围内创建 compact 建立一个数组
	//array_chunk数组分割成多个 array_slice 返回指定范围的数组 array_idff求茶几 array_intersect 求交集 array_merge合并数组
	//array_search搜索数组 array_flip 交换数组中的k和v

//php读取文件的方法有哪些？ fopen fread file_get_contetns
////php加速扩展模式 zend opetion 加速器， 调试工具：xdebug
///常用的mysql操作数据库命令：show databses show tables drop table inert into update 表明 set 字段=value wehre 条件
///delete fomr biaoming wehre tiao
///inert into se;ect * from orders whre order_id = xxx
///show databases  列出当前的数据库列表
///show create table user 列出user的dll结构 desc user 查询user表结构
///show variables like "%conn%" 显示当前系统变量中有conn的相关信息
///select a.id,a.class from a left join b on a.id = b.id where a.id>0
///in 在查询的结果中 not int 不在 exists 存在结果集 not exists 不存在 gcc gcc-c++
///cookie的用法 setcookie 名称 值 有效期 当前域
///$_COOKIE 获取打NGQIANDECOOKIE
///
///inerface b{...}
///interface c{...}
///
///class xx imploments B,c{..}
	//做排序
	public function sorts(){
		$explode = $this->explodes();
		sort($explode);
		return $explode;
	}
}
$str='Apple Orange Banana Strawberry';
$result = new sort($str);
echo '<pre>';
var_dump($result->sorts);
echo '</pre>';
exit;

$str = '1234,6';
$arr=regGx::checkEmail($str);
echo '<pre>';
var_dump($arr);
echo '</pre>';
exit;
?>