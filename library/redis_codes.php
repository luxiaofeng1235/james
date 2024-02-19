<?php

/**
 * Redis队列操作类
 *
 */

class redis_codes {

    public $redis;

    function __construct()
    {
        $host   = 'localhost'; //主机IP
        $port   = '6379'; //端口号
        $pass   = ''; //密码设置
        $this->redis = new Redis();//实例化redis
        $this->redis->connect($host, $port);
        if(!empty($pass)){
            $this->redis->auth($pass);
        }
        $this->redis->select(10);
    }

    /**
     * 封装一个切换数据库的方法
     * @param number $dbindex
     */
    public function select($dbindex=1){
        $this->redis->select($dbindex);
    }

	//设置redis库，为了兼容passport的用这个共有的函数
    public function set_redis_select($data_num){
        $this->redis->select($data_num);
    }

    /**
     * 封住在一个入队列操作
     * @param string $queueName
     * @param string $queueContent
     */
    public function lpush($queueName='',$queueContent=""){
        $this->redis->lPush($queueName,$queueContent);
    }


    /**
     * 从redis库弹出来一个元素
     * @param string $queueName
     * @param string $queueContent
     */
    public function lpop($key)
    {
        return $this->redis->LPOP($key);
    }

    /**
     * 封住在一个入队列操作
     * @param string $queueName
     * @param string $queueContent
     */
    public function rpush($queueName='',$queueContent=""){
        $this->redis->RPUSH($queueName,$queueContent);
    }


    public function LLEN($key){
        return $this->redis->LLEN($key);
    }

    /**
     * 封装一个app消息推送统一发送接口
     * @param array $msgbody
     */
    public function app_msg_push($msgbody=array()){
        $this->redis->select(1);//确保与处理的通道一致
        //消息编码
        $msgcontent=rawurlencode(json_encode($msgbody));
        $this->redis->lpush("msg_queue_list","app_msg-push-".$msgcontent);
    }

    /**
     * 一个统一的推送消息的方法
     * @param string $controller 控制器名
     * @param string $method     方法名
     * @param array $msgbody     消息体
     */
    public function to_push_msg($controller="",$method="",$msgbody=array()){
        $this->redis->select(1);//确保与处理的通道一致
        //消息编码
        $msgcontent=rawurlencode(json_encode($msgbody));
        $this->redis->lpush("msg_queue_list",$controller."-".$method."-".$msgcontent);
    }

    /*
     * 删除查找redis
     *
     */
    public function del_redis($key){
        if($this->redis->exists($key)){
              $result = $this->redis->del($key);
              return $result;
        }else{
              return false;
        }

    }
    /**
     *获取单个
     * @param type $key
     * @return type
     */
     public function get_redis($key){
        $result = $this->redis->get($key);
        return $result;
    }

    public function get_redis_eest($key){
        $result = $this->redis->get($key);
        return $result;
    }

    public function set_redis($key ,$data , $timeout){
        $result = $this->redis->set($key , $data);
        if($timeout>0){
            $result = $this->redis->set($key , $time_out);
        }
        return $result;
    }
    /**
     *设置key和过期时间，单位秒
     * @param type $key
     * @param type $data
     * @param type $time_out
     * @return type
     */
    public function set_redis($key,$data,$time_out) {
        $result = $this->redis->set($key,$data);
        if ($time_out > 0) $this->redis->expire($key, $time_out);
            return $result;

    }

    public function incr_redis($key){
        return $this->redis->incr($key);
    }

    public function decr_key($key=''){
        return $this->redis->decr($key);
    }

    /**
     * 把$key的值增加1
     * @param unknown_type $key
     */
    public function incr_redis($key){
    	return $this->redis->incr($key);
    }

    /**
     * 把$key的值减去1
     * @param unknown_type $key
     */
    public function decr_redis($key){
    	return $this->redis->decr($key);
    }



    /**
     *获取zone下单个$key(hash)
     * @param type $key
     * @return type
     */
    public function hget_redis($zone,$key){
    	$result = $this->redis->hGet($zone,$key);
    	return $result;
    }
    /**
     *设置key和value  (hash)
     * @param type $key
     * @param type $value
     * @return type
     */
    public function hset_redis($zone,$key,$value) {

    	$result = $this->redis->hSet($zone,$key,$value);
    	return $result;

    }

    /**
     * 把$key的值增加1
     * @param unknown_type $key
     */
    public function hincr_redis($zone,$key){
    	return $this->redis->hIncrBy($zone,$key,1);
    }

    /**
     * 获取zone下所key value
     * @param unknown_type $zone
     * @param unknown_type $key
     */
    public function hget_all($zone){
    	return $this->redis->hGetAll($zone);
    }

    /**
     * 获取某个key的过期时间
     * @param unknown_type $key
     * @return unknown_type $time
     */
    public function ttl($key){
        return $this->redis->ttl($key);
    }

}