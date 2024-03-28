<?php
//基础的队列实现方式，用对应的信息去转载
class Payment{

    protected static  $redis = null; //redis的基础类
    protected static  $mysql_obj = null; //mysql的句柄连接

    //自动加载mysql和redis需要重写类这里面要用
    public static function reloadConfigure(){
        global $redis_data,$mysql_obj;
        self::$redis = $redis_data;
        self::$mysql_obj = $mysql_obj;
    }

    //接收消息实现具体的业务方法和函数，这里面不能用exit只能用return返回
    public static function set_return($msg = ''){
        self::reloadConfigure();//自动加载当前的类
        $sql = "select * from ims_novel_info limit 3";
        $info =self::$mysql_obj->fetchAll($sql, 'db_slave');
        echo '<pre>';
        print_R($info);
        echo '</pre>';
        exit;
    }
}

?>