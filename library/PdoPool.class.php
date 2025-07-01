<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :pdopool.class.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:PDO连接池类
// ///////////////////////////////////////////////////

class ConnectionPool {
    private static $instance = null;
    private $connections = [];
    private $maxConnections = 10;
    private static $hahh = [];
    public static $use_db_name = null;
    public $db_slave='db_slave'; //从库
    public $db_master='db_master'; //主库


    //连接mysql的列表
    private static  function myPdoList()
    {
        $list=array();
        //从Env的环境变量中加载配置文件方便管理
        $config = [
            'host'  =>Env::get('DATABASE.HOST_NAME'),//数据库的主机地址
            'username'  =>Env::get('DATABASE.USERNAME'), //数据库的用户
            'password'  =>Env::get('DATABASE.PASSWORD'),//数据库的密码
            'db_name'   =>Env::get('DATABASE.DBNAME'),//数据库名称
            'port'  =>  Env::get('DATABASE.PORT')//数据库的端口
        ];
        //线上小说配置
        $config_pro = [
            'host'  =>Env::get('DATABASE_PRO.HOST_NAME'),//数据库的主机地址
            'username'  =>Env::get('DATABASE_PRO.USERNAME'), //数据库的用户
            'password'  =>Env::get('DATABASE_PRO.PASSWORD'),//数据库的密码
            'db_name'   =>Env::get('DATABASE_PRO.DBNAME'),//数据库名称
            'port'  =>  Env::get('DATABASE_PRO.PORT')//数据库的端口
        ]; 
        //本地localhost的master
        $list['db_slave']=array('dsn'=>'mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['db_name'],'user'=> $config['username'],'password'=> $config['password']);
        //本地本地localhost的slave库
        $list['db_master']=array('dsn'=>'mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['db_name'],'user'=> $config['username'],'password'=> $config['password']);
        //小说库
        $list['db_novel_pro']=array('dsn'=>'mysql:host='.$config_pro['host'].';port='.$config_pro['port'].';dbname='.$config_pro['db_name'],'user'=> $config_pro['username'],'password'=> $config_pro['password']);
        return $list;
    }

    /**
    * @note 返回mysql的句柄并进行连接PDO操作
    *
    * @param [str] $[$db_name] [<连接的句柄>]
    * @author [xiaofeng] <[<luxiaofneg.200@163.com>]>
    * @Date 2020-12-21
    * @return object|bool
    */
    public static function myProConnectionInfo($db_name)
    {
        if($db_name)
        {
            $info=self::myPdoList();
            if($info[$db_name])
            {
                //測試連接應用
                try {
                    //根据PDO来进行连接，主要用于进行PDO连接
                    $db = new PDO($info[$db_name]['dsn'],$info[$db_name]['user'],$info[$db_name]['password'],array(PDO::ATTR_PERSISTENT => true));
                    //主要用来设置POD链接失败会抛出来一个异常
                    $db ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $db ->query("set names utf8");
                    return $db ;
                } catch (\PDOException  $e) {
                     throw new \PDOException($e->getMessage(), (int)$e->getCode());
                }
            }
        }
        return null;
    }



    //获取链接
    public static function getProRes($db_name){
        if($db_name){
            $GLOBALS['db_name'] = $db_name; //存储全局变量
            // echo "gggggggggggggggggggggggggggggggggggggggg*".$GLOBALS['db_name']."\r\n";
            // 在这里设置数据库连接参数
            $dsn = self::myPdoList()[$db_name];
             // 创建初始连接
            for ($i = 0; $i < 10; $i++) {
                //初始化实例化PDO连接类
                 self::$hahh[] = self::myProConnectionInfo($db_name);
            }
            return self::$hahh;
        }else{
            return null;
        }
    }

    //实例化并获取请求连接
    public static function getInstance($db_name="")
    {

        // echo "iiiiiiiiiiiiiiiiiiiiiiiiiii -{$db_name}\r\n";
        self::$instance = self::getProRes($db_name);
        return self::$instance;
    }

    //获取链接
    public function getConnection()
    {
        if (count(self::$hahh) > 0) {
            // echo "create connection ! \r\n";
            return array_pop(self::$hahh);
        } else {
            // 如果连接池已经用完,创建一个新的连接
            $conn = $this->myProConnectionInfo($GLOBALS['db_name']);
            return $conn;
        }
    }

    //释放连接
    public function releaseConnection($connection)
    {
        if (count(self::$hahh) < $this->maxConnections) {
            self::$hahh[] = $connection;
        } else {
            // 如果连接池已经满了,关闭连接
            $connection = null;
        }
    }

    //关闭连接
    public function closePdoConnection($connection){
        $db_name = $GLOBALS['db_name'];
        // echo "close connection ~\r\n";
        self::getInstance($db_name);
        $this->releaseConnection($connection);
    }


/**
 * 获取结果集，返回tables
 * @param string $sql
 * @param string $db_name
 * @return array()
 */
    public  function fetchAll($sql,$db_name)

    {

        // echo "*************************************{$db_name}\r\n";
        //获取连接
        self::getInstance($db_name);
        $connection = $this->getConnection();

        $date=$connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $list = [];
        if(isset($date) && !empty($date))
        {
            $list = $date;
        }
        $this->closePdoConnection($connection); //关闭连接
        return $list;
    }

    /**
     * 获取条记录，并返回数组
     * @param string $sql
     * @param string $db_name
     * @return array()
     */
    public  function fetch($sql,$db_name)
    {
         //获取连接
        self::getInstance($db_name);
        $connection = $this->getConnection();
        $date=$connection->query($sql)->fetch();
        $ret = [];
        if(isset($date) && !empty($date))
        {
            $ret = $date;
        }
        $this->closePdoConnection($connection); //关闭连接
        return $ret;
    }

        /**
    * @note 批量生成更新语句
    *
    * @param [object] $[data] [<待添加的数据>]
    * @param [where] $[str] [<更新条件>]
    * @param [string] $[table_name] [<表名称>]
    * @return bool
    */
    function update_data($data =[],$where='',$table_name='',$limit = false,$limit_size=0,$db_name_conn=''){
        if(!$data || !$where){
            return false;
        }
        $sp_where ='';
        //转义特殊字符
        if(get_magic_quotes_gpc()){

            $sp_where = is_array($where) ?
                    array_map('stripslashes_deep', $where) :
                    stripslashes($where);
        }else{
            $sp_where = $where;
        }

        $data = array_filter($data);
        // $data = array_unique($data);

        //拼装需要更新的语句
        $sql ="UPDATE `{$table_name}` SET ";
        if($data && is_array($data)){
            foreach($data as $k=> $v){
                $tval[]= "`$k`='{$v}'";
            }
        }
        $update_data = join(',',$tval);
        if(isset($tval))
            unset($tval);
        if(!empty($update_data)){
            $sql .=$update_data;
        }
        $sql .= ' WHERE '.$where;
        if($limit && $limit_size){
            $sql .=" LIMIT ".$limit_size;
        }
        echo $sql."\r\n";
        //exec mysql
        if($sql){
            if(!empty($db_name_conn)){
                $db_name = $db_name_conn;
            }else{
                $db_name = $this->db_master;
            }
            self::getInstance($db_name);
            $connection = $this->getConnection();
            // $db_obj = $this->myProConnectionInfo($db_name);
            if($connection->query($sql)){
                $this->closePdoConnection($connection);
                return 1;
            }
            return false;
        }else{
            return false;
        }
    }

        /**
     * 执行一个sql,成功返回sql语句,失败返回空
     * @param string $sql
     * @param string $db_name
     * @return string or  false
     */
    public  function query($sql,$db_name)
    {
        //获取连接
        self::getInstance($db_name);
        $connection = $this->getConnection();
        //执行
        $date=$connection->query($sql);
        //关闭
        $this->closePdoConnection($connection); //关闭连接
        if(isset($date) && !empty($date))
        {
            return $date;
        }
        return false;
    }

        /**
    * @note 生成批量添加sql语句
    *
    * @param [object] $[data] [<待添加的数据>]
    * @param [string] $[table_name] [<表名称>]
    * @param [string] $[db_conif] <数据库句柄>
    * @return
    */
    public function add_data($data,$table_name='',$db_conf=''){
        if(!$data || !$table_name){
            return false;
        }
        if(empty($db_conf)){
            $db_name = $this->db_master;
        }else{//配置启用配置里的mysql
            $db_name = $db_conf;
        }
        $sql ="insert into `{$table_name}`";

        if(!isset($data[0])){
            $item[]=  $data;
        }else{
            $item = $data;
        }
        //取出来第一个的键值来处理

        $push_key  =isset($item[0]) ? implode(',', array_keys($item[0])) : '';
        // $order_sns=array_map('trim',explode("\n",$order_sns));
        $sql .=" (".$push_key.") values ";
        $inner_sql='';
        foreach($item as $key =>$val){
            $tkey = [];
            foreach($val as $k =>$v){
                $v= @addslashes($v); //有必要进行转义下
                if(!is_array($v)){
                    $tkey[]="'".$v."'";
                }
            }
            $dk =array_keys($val);
            $dd = array_values($val);
            $inner_sql.="(".implode(',', array_values($tkey))."),";
        }
        if($inner_sql){
            $inner_sql = rtrim($inner_sql,',');
            $sql .=$inner_sql;
        }
        echo "{$sql}\r\n";
        if($sql){//开始添加数据
            self::getInstance($db_name);
            $connection = $this->getConnection();
            $ret = $connection->query($sql);
            if($ret){
                $insert_id =  $connection->lastInsertId(); //返回成功插入的id
                $this->closePdoConnection($connection);//关闭连接
                return $insert_id;
                // return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

        /**
     * 封装删除操作
     * @date 2020.12.23
     * @param string $sql
     * @param string $db_name
     * @return string or  false
     * @author [xiaofeng] <[<luxiaofeng.200@163.com>]>
     *
     */
    public function delete($where='',$table_name=''){
        if(!$where)
            return false;
        $sql ="delete from ".$table_name." where ".$where;
        //删除数据
        $res = $this->query($sql , $this->db_master);
        if($res){
            return 1;
        }else{
            return 0;
        }
    }


    /**
     * 根据条件进行查询
     * @param arr $where
     *        condition =>[] 查询的关联数组
     *        sort_set => [] 排序依据 取决于是否需要排序 输入类型：数组或者单个字符
     *        sort_range =>[] 排序的降序或者升序 desc:降序 asc:升序 输入类型：数组或者单个字符
     *        group_by => '' 按照某个字段来进行排序
     *        limit => ''   查询限制个数，主要做分页会用到 可以是数组或者具体的字符 如 5 |[5,10]
     * @param string $filed 查询字段
     * @param string $db_name 数据库连接句柄
     * @return object|unknow
     */
    function get_data_by_condition($where ='',$table_name='',$field='*',$debug = false,$db_name='db_slave'){
        //插入的参数字段检测==只有判断为数组的时候才进行插入
        if($where && is_array($where)){
            $check_key = array_keys($where);
            $flag = true;
            $error_msg = [
                'error_code'=>'300001',
                'error_msg'=>'传入字段参数无效'
            ];
            //默认的字段类型
            $input_param = [
                'condition','
                sort_set',
                'sort_range',
                'group_by',
                'limit',
            ];
            foreach($check_key as $ckey){
                if(!in_array($ckey, $input_param)){
                    $flag=false;
                }
            }
            if(!$flag){
                return $error_msg;
            }
        }
        $select = $field ? $field :'*';
        $sql = 'SELECT '.$select.' FROM `'.$table_name.'` WHERE ';

        if(!is_array($where)){
            $where = stripslashes($where);
            $sql .=$where;
        }else{
            //按照数组来进行查找
            //按照多维度数组进行排序
            /*
            $arr = array(
                'condition' =>array('upid'=>87,'city_code'=>130800),
                // 'sort_set'   =>'id',
                // 'sort_range' =>'desc',
                'limit'     =>'10,5',
            );
             */
            $condition = '';
            $where_data = isset($where['condition']) ?$where['condition'] : [];
            $sort_set  =isset($where['sort_set']) ?$where['sort_set'] : ''; //排序依据
            $sort_range = isset($where['sort_range']) ?$where['sort_range'] : ''; //排序方式
            $group_by = isset($where['group_by']) ? trim($where['group_by']) : ''; //分组依据
            $limit = isset($where['limit']) ? $where['limit'] :0;

            if($where_data){
                foreach($where_data as $k=> $v){
                if(!$v) continue;
                    $condition .="`{$k}`='{$v}'".(count($where_data)>1 ? ' AND' :'');
                }
                $condition = rtrim($condition , 'AND');
            }
            if($condition){
                $sql .=$condition;
            }else{
                $sql .="true"; //主要为了防止没有条件会报错
            }

            if($group_by!=''){
                $sql .=" GROUP BY ".$group_by;
            }

            if($sort_set){//排序依据处理
                $sort_str= '';
                if(is_array($sort_set)){
                    $sort_item = '';
                    foreach($sort_set as $sk =>$sv){
                        $sort_item.="`{$sv}` ".(isset($sort_range[$sk]) ? $sort_range[$sk] :'').',';
                    }
                    if($sort_item){
                        $sort_item =rtrim($sort_item,',');
                        $sort_str = $sort_item;
                        if(isset($sort_item))
                            unset($sort_item);
                    }
                }else{
                    $sort_str =$sort_set.' '.($sort_range ? $sort_range : '');
                }


                $sql .=" ORDER BY ".$sort_str;
            }
            //分页
            if($limit){
                $sql .=" LIMIT ".(is_array($limit) ? join(',',$limit) : $limit);
            }
        }

        // echo "sql ======{$sql}\r\n";
        // return $sql;
        //查询所有的数据进行解析
        $result = $this->fetchAll($sql,$db_name);
        $this->db = null; //关闭连接及时释放
        if($result && is_array($result)){
            if($debug){
                return [
                    'mysql_query'=>$sql,
                    'list'=>$result
                ];
            }else{
                return $result;
            }
        }
        return [];
    }

}