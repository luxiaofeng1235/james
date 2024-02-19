<?php
$list =[1,2,3,4,5];
array_push($list, 100);
$c = $list;
if(isset($c) || !empty($c)){
    foreach ($c as $key => $value) {
        echo "this is a test value :" . $value."<br />";
    }
}
$config = [
    'host'   =>     'localhost',
    'port'   =>     '27017',
    'dbname' =>     'dbTest',
];

//转换数组类型
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
     } if(is_array($array)) {
         foreach($array as $key=>$value) {
             $array[$key] = object_array($value);
             }
     }
     return $array;
}

function _parseId($arr){
    if(isset($arr['_id'])){
        $arr['_id'] = new MongoId($arr['_id']);
    }
    return $arr;
 }

$server = sprintf("mongodb://%s:%s/%s", $config['host'],  $config['port'],$config['dbname']);
try {
    $mongodbClient =   new MongoDB\Driver\Manager($server);// 立即连接;|

    $db =$mongodbClient->dbTest;
    $act= isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
    if(!in_array($act,['find','insert','update','delete'])){
        echo "plear input you operation now!";
        echo "<hr/>";
        exit;
    }
    if($act == 'find'){
        $query = new MongoDB\Driver\Query([], []);
        $cursor = $mongodbClient->executeQuery($config['dbname'].'.'.'brand', $query);
        $item = [];
        foreach($cursor as $document){
            if(!$document) continue;
            $item[] = object_array($document);
        }
        $s_sort =$sortArr=[];
        if(count($item)>0){
            foreach($item as &$value){
                if(isset($value['_id'])){
                    unset($value['_id']);
                    $value['cate_id'] = (int) $value['cate_id'];
                    $s_sort[]=$value['cate_id'];
                    // $sortArr[] =$value;
                }
            }
        }
        array_multisort($s_sort , SORT_ASC , $item);
        echo '<pre>';
        var_dump($item);
        echo '</pre>';
        exit;

    }else if($act == 'update'){
        $bulk = new MongoDB\Driver\BulkWrite;

        $query = array('cate_id'   =>  '4567');
        // 自动处理 '_id' 字段
        $query =_parseId($query);
        $newDoc = array('title' =>  '记忆的美好的123');
        $colName = 'brand';

        $bulk->update(
            $query,
            ['$set' => $newDoc],
            ['multi' => false, 'upsert' => false]
        );

        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $result = $mongodbClient->executeBulkWrite($config['dbname'].'.'.$colName, $bulk, $writeConcern);
        echo '<pre>';
        var_dump($result);
        echo '</pre>';
        exit;
    }else if($act == 'delete'){
        $bulk = new MongoDB\Driver\BulkWrite;
        $query=['cate_id' =>   '4567'];
        // $query = $this->parse_query($query);
        // 自动处理 '_id' 字段
        $query = _parseId($query);
        $limit  = 1;
        $bulk->delete($query, ['limit' => $limit]);
        // echo $this->dbname.'.'.$colName;die;
        $colName  = 'brand';
        //
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $result = $mongodbClient->executeBulkWrite($config['dbname'].'.'.$colName, $bulk, $writeConcern);
        echo '<pre>';
        var_dump($result);
        echo '</pre>';
        exit;
    }else if($act == 'insert'){
        // array_map('est', $arraylist);
        $data = [
            'title'     =>  '将军令的传说',
            'cate_id'   =>  '98758',
            'create_time'   =>  time(),
        ];
        $colName =  'brand';//集合名称
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        // echo $this->dbname.'.'.$colName;die;
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $insertRes = $mongodbClient->executeBulkWrite($config['dbname'].'.'.$colName, $bulk,$writeConcern);
        if(!$insertRes){
            echo 'insert error'."<br />";
            die;
        }
        echo '<pre>';
        var_dump($insertRes);
        echo '</pre>';
        exit;
    }


    // $list = object_array($cursor);
    // echo '<pre>';
    // var_dump($list);
    // echo '</pre>';
    // exit;

}catch (MongoConnectionException $e){
        echo $e->getMessage();
    }

// $mongoClient = new MogoDB
?>