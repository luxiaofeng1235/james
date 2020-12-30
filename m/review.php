<?php
#获取主机的名称 或者通过ping trace命令来实现
$url = 'www.fumubang.com';
$arr = gethostbyname($url);

#通过grep来进行搜索
#sed -i /yyyy/xxxx/g./*.txt/i 或者通过find xargs来进行匹配


#哈希队列的值的范围来进行搜索
#赋值操作处理
$b= $a=5;
$f = $a++; //先把值赋给F,在进行相加运算
echo $f;
echo "<br/>";
echo $a;
echo "<br/>";
$x = 0;
if($x++){ //这里是重点体现在于x还是0 $x = $x+1;
    echo "12";
}else{
    echo "34";
}
echo "<br/>";

$num = 10;
function print_A(){
    $num = 35;//如果上下的位置颠倒了，就可能是35了。需要注意
    global $num;
    echo $num;
}
echo $num;
echo "<br/>";
echo print_A();

echo "<hr/>";

#针对不同的++i 和i++来进行综合考验
$x = 1 ;
++$x; //此时x的值为2
$y = $x++;//如果是++x Y的值为1  此时还是2,直接饮用2的值

printf('y value：%d',$y);
echo "<br/>";

#字符串切割操作
$first = "This course is very easy !";
$second = explode(" ",$first);
$str= implode(',' , $second);
echo $str;
echo "<br/>";

#切割字符为数组
$result = '1234';
$string = str_split($result);
echo '<pre>';
var_dump($string);
echo '</pre>';

#字符串查找替换
$email = 'langwan@thizlinux.com.cn';
$arr = strstr($email,'@');
$ret  = explode('.',$arr);
echo '<pre>';
print_R($ret);
echo '</pre>';

#PHP 日期操作

#先输出当前日期在输出下周日期，   格式化了一下
$nextWeek = time() + (7 * 24 * 60 * 60);
echo 'Now: '. date('Y-m-d') ."\n";
echo 'Next Week: '. date('Y-m-d', $nextWeek) ."\n";

echo "<br/>";
#函数的调用和使用
function print_x(){
    $A = "phpchina";
    echo "A值为: ".$A."<p>";
    //return ($A); 如果注释去掉的话就返回空，否则返回A的值
}

$B = print_x();   #运行时会出错，因为没有返回值，是空的
echo "B值为: ".$B."</br>";

#考察PHP的变量作用域饮用
$a = "aa";
$aa = "bb";
echo $$a."<br />";

#变量宏替换的基本操作
$a = 10;
$b = &$a;   #取a 10
echo $b;      #b也指向10
$b = 15;
echo $a; #这时候B赋值因为A和B直线的是同一个地址，所以A变成了15
echo "<br/>";
echo "<hr/>";
#字符串操作
echo "123abdddfs"+6;
echo "<br/>";
echo "abc112abd"+10;
echo "<br/>";

#PHP输出反斜杠的用法  \\的相关用法
echo rand(1,10); #随机数生成
echo "<br/>";

#三目运算日常操作
$a = "cc";
#变量的常规操作替换操作
$cc = "dd";
#CC和字符相等，输出对应的内存中的地址应该是DD
echo $a=="cc"?"{$$a}":$a;



#千万级网站架构需要考虑的技术点：
/*
 * 1/数据库方面：选用合适的字段，即DML模型规范
 * 2、使用数据库索引以及相关的存储过程的选用
 * 3、数据分析分库分表。中间件的使用
 * 4、数据库读写分离
 * 5、数据库分布式部署提高网站的吞吐吞量
 * 6。基于业务使用数据缓存提高网站的数据高可用性。
 * 7。服务层SOA框架的引用，提高网站负载
 * 8、消息队列的使用
 * 9.服务器选用nginx以及第三方插件来做限流使用
 * 10.前端页面静态化访问，提高网站访问速度。
 * */

/*
 * 缓存穿透主要优化的技术点：
 * 1.前端增加页面验证，数字签名增加过滤，防止进行大批量访问压垮DB
 * 2、针对空数据做一个缓存策略部署，rdis_null的30秒有效访问，这样子可以减少对DB的无数据重复的DB的压力
 *
 * 缓存击穿：
 * 使用分布式锁来控制，缓存失效后，先对数据加锁，如果加锁成功，在操作localDB 否则轮查询使用
 *
 * 缓存雪崩：
 *
 * 1.设置缓存有效期，针对性增加访问随机数，把缓存时间隔开。
 * 2、缓存分部署部署，将缓存分布式部署在不同 的机器上进行访问、
 * 3、针对固定板不变的缓存 ，可以让缓存永不过期。
 *
 *
 * swoole比fpm快的主要原因在于，在swoole内部的master/worker模式中，其rector负责监听socket句柄链接变化，这是FPM所不具备的，可以提高网络的并发请求量，此过程采用非阻塞模式。
 *
 *
 * 主从数据不同步的几个原因：
 *
 * 1、数据量访问量大，写入慢导致binlog同步慢
 * 2、网络抖动
 * 3、数据库硬件设置，主要是节点在不同的区域内，会造成同步延迟
 *
 * 解决方案：
 * 1.使用云存储数据库或者使用mysql中间件来减少并发请求量，一定程度上减少对高并发的截流
 * 2、基于业务对不同的业务做优化，主要在数据库上做处理。
 *
 * PHP内部优化的提高效率的方法
 *
 * 1、使用内部的函数常量变量
 * 2、对冗余代码进行优化
 * 3、减少魔术方法的使用，降低系统的开销
 * 4、使用unset释放内存空间
 * 5、减少不必要的复杂的程序计算
 *
 * 分库分表的设计规则，以用户的日志每天五十万递增来说，可以设计一个按月的统计日志
 * 考虑每天的五十万增量，使用innerDB索引，按月每个月生成一个增量表来存储，
 *
 * 如果用户每天五十万的注册信息存储，这时候我们需要从以下几个方面来考虑，
 * 一个是按照range来分割存储uid的对应分表，比如按照指定的range集合来进行，或者对数据进行求模计算。
 *hash表的风阀，以uid的hash算法来生成一个hash串，取前两位来记性存储或者利用md5加密的算法取出来前两位来进行存储
 *、
 * 有一个一千万的表，这时候我们查询从十万的时候开始查询有什么比较好的方法取出来十条，考虑数据库的瓶颈和效率
 *
 * 1、这个时候我们采用通过id>主键在某个位置处的id来进行定位搜索limit 10条来计算
 * 2、也可以利用覆盖索引+inner join单表关联的方式来索引提高命中索引效率。
 * */
echo "<hr/>";

#常量的引用
define("GREETING", "Welcome to W3School.com.cn!");
define('GREETING' ,"my is second values");
#输出是输出define定义的第一个的值，因为常量在存储过程中不会改变，这个需要注意
echo 'GREETING\'s value: ' . GREETING;
echo "<br/>";

#利用file函数来读取文件内容
$dir = './word.txt';
#注意一点是，file函数读取出来是一个数组
$res_handle = file($dir);
#写一个构造函数，需要把读取出来的文件的内容中的换行处理掉
$trimBlank = function ($arr) {
    foreach ($arr as $k=> &$v) {
         if($v){
             $v = preg_replace("/\r\n/",'',$v);
         }else{
             unset($arr[$k]);
         }
    }
    return array_merge(array(),$arr);
};
#返回对应的参数优化
$fileData = $trimBlank($res_handle);
echo '<pre>';
print_R($fileData);
echo '</pre>';

#处理面向对象的三大特性 ：继承 封装 多态

#按照一定的规则进行拆分处理

/*
 * http和https的不同之处：
 * http:是超文本传输协议，https是采用http+ssl进行数据传输和网络加密方式。
 * http返回是无状态的
 * http采用明文传输，而https一般是密文传输，采用ca证书进行验证，一般ca证书大部分都收费，很少有免费的
 * */

#数组的合并用+进行操作会怎么样：
$a112  =array('a'=>'apple','b'=>'banana');
$b12  =array('a'=>'pear','b'=>'strawberry','c'=>'cherry');

$c23 = $a112+$b12; //以出现的第一个键值为依据
#结果返回pear、strawberry、cherry 不过应该是通过减值来处理的，因为第一个里面ab优先取第一个的，然后把C的value加上。
echo '<pre>';
var_dump($c23);
echo '</pre>';

#求一个字符串中的出现的某个字符的数量
$text = 'gdfgfdgd59gmkblg';
echo substr_count($text,'d');
echo "<br/>";

/** 普通局部变量 */
function local() {
    $loc = 0; //这样，如果直接不给初值0是错误的。 ,如果加上static就会保存上一次的值，自动保存 为1.2.3
    ++$loc;
    echo $loc . '<br>';
}
local(); //1
local(); //1
local(); //1

echo "<hr/>";

