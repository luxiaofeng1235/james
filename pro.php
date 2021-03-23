<?php
//获取当前的URL配置
$dir= dirname(__FILE__);
$ret = pathinfo($dir);

$hostname = 'www.baidu.com';
$addr = '127.0.0.1';

#$_SERVER['HTTP_HOST']是获取当前网站的请求域名
#$_SERVER['SCRIPT_NAME']获取执行的脚本名称
#gethostbyname根据url获取主机ip
#R_SERVER['SERVER_ADDR'] 服务器的ipd
#$_SERVER['REMOTE_ADDR'] 客户端的ip
#$_SERVER['SCRIPT_FILENAME']获取执行的脚本文件路径 
#$_SERVER['query_string'] 取出来执行的参数
#
#、、Magic_quotes_gpc是在进行体积爱post或者get开启的情况下自动进行转义
#
#、、不适合简历索引的情况：查询比较复杂的表达式或者条件 
#一些数据类型不宜简历索引。比如text文本型
echo '<pre>';
print_R($_SERVER);
echo '</pre>';
exit;
#获取主机的方法为：可以利用ping或者trace获取利用gethostbyname函数来获取。
#程序中获取PHP的webroot目录 $_SERVER['DOCUMENT_ROOT'] 
#
#
#通过session生成一个session_id在URL中带过来?sid=session_id对于国内的值，在第一次请求在cookie存储session_id.第二次从cookie中取出来他的值
/*
大包的linux命令国内“tar zcf 大包 大包  tar xzf 解压un
unzip解压zip文件
大包zip gunzip res.txt arr.zip
#*/

/*
匹配url的正则：
/^(https?|ftps?):\/\/(www)\.([^\.\/]+)\.(com|cn|org)(\/[\w-\.\/\?\%\&\=]*)?/i


/^(http|ftps):\/\/(www)\.([^\/\/]+)\.(com|org|cn|edu)([\w\.\&、&\?=*])?/i
 */
$url ='www.fumubang.com';//当前的URL
$data = gethostbyname($url);
preg_match("/^[1-10,]+$/", $arr)
echo '<pre>';
print_R($data);
echo '</pre>';
exit;

print_r(gethostbyname($hostname));
echo '<br>';
exit;

print_r(gethostbyaddr($addr));
echo '<br>';

print_r(gethostbynamel($hostname));
echo '<pre>';
print_R($ret);
echo '</pre>';
exit;

##crontab的格式：分 时 日 月 周 
#0,30 18-23 * * * 表示每天的18-23时的0至30分执行此脚本

#45 10 1,10,22 * * 表示每个月的1 10 22号的十点45分来执行此脚本


/*
每月每天的午夜 0 点 20 分, 2 点 20 分, 4 点 20 分....执行 echo "haha"
20 0-23/2 * * * echo "haha" 每个月单位2号执行

crontab -l 列出当前的crontab进程表
crontab -e 对相关的进行进行编辑
crontab -r 删除当前的时程表

 */


/*
常用操作端口号
 sphinx的端口号9312 redis 6479 http 80 mysql 3306 memcached11211 
openid 获取appid+appscrent手动授权获取sccode.获取对应redirect_uri进行。
scapi—_base 和scapi_userinfo basse是会获取少量的用户信息。而userinfo是需要用户去提示进行操作是否同意。，需要用户点确认进行授权，同意的话会调换到rediret_uri
*/


/*
杀掉phpfim的进程

kill -9 $(ps -ef|grep "php-fpm"|awk 'print ${1}')
atchts和xproxyh查看网络的负载情况，主要查询cpu已经磁盘的占用情况。

set是单向链表，list是双向链表，采用先进先出的方式，。类似队列 ,一般用于做消息队列；而set主要对不同的重复的数据做复杂的计算，一般用于做数据的奇差集、交集、并集操作

购物车一般采用hashMap来处理，使用user_id设置对应的hash，product_id用集合处理对应的属性。

hash和btruee_+的区别和用法：

hash一般采用顺序索引，等值查询，。
btrr+Tahiti根据数据所在的内存，从内存够中索引到页。从页查数据 适合范围查找。\

redis的主要参数配置：
requirepass timeout logfilge maxclinets

offset定位。

常见的魔术方法：  _-set__ __get__ __call__

双端队列数组  array_push array_pop  array_shift删除一个元素 array_unshift 插入一个元素
 */

/*
设计模式：单例模式 工厂模式 观察者模式 注册模式 

 */

/*
mysql锁的类型：

乐观锁 ：在数据表中追加一列。记录为version,在数据进行提交的时候，进行判断是否存在冲突，如果冲突，就会返回错误信息。如果没有冲突，取消这次操作；
悲观锁： 利用事务来进行绑定判断，使用航亲所会让真个表锁住，其他人无权操作，主要是对表进行锁定。

注意：整个锁的过程都是在发生数据操作之前上锁。事后解锁。



前端主要知识点：
html5的用法 容器contains email check 
ajax做异步通信用，发送http请求，

$("#tstt").unbind('click').click('click',function(){
		$.ajax({
			type:"post",
			"datattype",
			url:
			anync:
			formData:app,
			success:function(data){
				//异步进行切换登录
			}

		})
});

前端跨越解决方案L:
jsonp数据传输 跨域资源共享  反向代理
access-controller-allow-origin 设置http跨越规则处理

*/

/*
redis的事务用法 exec multi discard watch multi set namelu se exec 
*/

/*
查询mysql第二个高的工资：
在内部做一个符合索引
SELECT IFNULL((select distinct salary from employee order by salary DESC limit 1,1),null) as secodeHighSalary

select ifnull((select distinct salary from employee order by salary desc limit 1,1),null) as secondHighSalary
  */


/**./

//跟踪DB的日志
DB::connection->enableQueryLog();

/*
删除重复的邮箱保留最小的那个
delete from persion where id not in(
	select id from 
		(
			select min(id) from persion Group by email
		) t
)

logfile = /tmp/`date+%H-%F`.log
n=`date +%H`
if [$n -eq 00]

 */


/*
docker的安装环境,直接使用在线安装包编译安装 ，常用命令：

docker pull nginx:latest

docker status 查看当前的docker状态

docker pull ubuntu

//dockeer容器的运行：

#使用docker运行
docker run -it ubuntu /bin/bash

docker ps -a \查看正常运行的容器的软件

dociker stop 容器编号

docker ps -a 

docker run -it ubuntu /bin/bash 
docker run -itd -name ubuntu /bin/bash

docker restart 容器id 重启docker的容器

docker pull php:5.6-fpm
 */
?>