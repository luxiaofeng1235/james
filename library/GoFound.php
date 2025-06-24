<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2013,真一网络
// 日 期：2024-03-06
// 作　者：卢晓峰
// E-mail :luxiaofeng@linktone.com
// 文件名：file_factory.php
// 创建时间:下午2:01:55
// 编 码：UTF-8
// 摘 要: 全文检索工具类，支持更新索引优、批量添加、全文检索模糊搜索
// ///////////////////////////////////////////////////
class GoFound
{
   public $host; // 服务器地址
   public $database; // 数据库
   public $username; // 账号
   public $password; // 密码

   public function __construct()
   {
      $this->host = Env::get('GOFOUND.HOST_NAME');
      $this->database = Env::get('GOFOUND.DATABASAE');
      $this->username = '';
      $this->password = '';
   }

   /**
    * 增加 / 修改索引
    * @param $id int 文档的主键 id，需要保持唯一性，如果 id 重复，将会覆盖直接的文档
    * @param $text string 需要索引的文本块
    * @param $document array 附带的文档数据，json 格式，搜索的时候原样返回
    */
   public function add($id, $text, $document)
   {
      $method = 'POST';
      $api = '/api/index?database=' . $this->database;
      $data = [
         'id' => $id,
         'text' => $text,
         'document' => $document
      ];
      return $this->curl($api, $method, $data);
   }

   /**
    * 批量增加 / 修改索引
    * @param mixed ...$data  数据格式，例 ['id'=> int, 'text'=> string,  'document'=> array]
    */
   public function batchAdd(...$data)
   {
      $method = 'POST';
      $api = '/api/index/batch?database=' . $this->database;
      return $this->curl($api, $method, $data);
   }

   /**
    * 删除索引
    * @param $id int  档的主键 id
    */
   public function remove($id)
   {
      $method = 'POST';
      $api = '/api/index/remove?database=' . $this->database;
      return $this->curl($api, $method, $id);
   }


   /**
    * 查询索引
    * @param $query string     查询的关键词，都是 or 匹配
    * @param $page  int   页码，默认为 1
    * @param $limit  int  返回的文档数量，默认为 100，没有最大限制，最好不要超过 1000，超过之后速度会比较慢，内存占用会比较多
    * @param $order  string    排序方式，取值 asc 和 desc，默认为 desc，按 id 排序，然后根据结果得分排序
    * @param $highlight  array    关键字高亮，相对 text 字段中的文本, 例 ["preTag": "<span style='color:red'>",  "postTag": "</span>"]
    * @param $scoreExp  string    根据文档的字段计算分数，然后再进行排序，例如：score+[document.hot]*10，表达式中 score 为关键字的分数,document.hot 为 document 中的 hot 字段
    */
   public function query($query, $page = 1, $limit = 100, $order = 'desc', $scoreExp = '')
   {
      $method = 'POST';
      $api = '/api/query?database=' . $this->database;

      $highlight = ["preTag" => "<span style='color:red'>", "postTag" => "</span>"];
      $data = [
         'query' => $query,
         'page' => $page,
         'limit' => $limit,
         'order' => $order,
         // 'highlight' => $highlight,
         'scoreExp' => $scoreExp
      ];
      return $this->curl($api, $method, $data);
   }

   /**
    * 查询状态
    */
   public function status()
   {
      $method = 'GET';
      $api = '/api/status';
      return $this->curl($api, $method);
   }


   /**
    * 删除数据库
    * @param $database string  数据库
    */
   public function drop($database)
   {
      $method = 'GET';
      $api = '/api/db/drop?database=' . $database;
      return $this->curl($api, $method);
   }

   /**
    * 在线分词
    * @param $query string  需要分词的文本
    */
   public function wordCut($query)
   {
      $method = 'GET';
      $api = '/api/word/cut?q=' . $query;
      return $this->curl($api, $method);
   }


   /**
    * 执行 http 请求
    * @param $api string 请求 url
    * @param $method string  请求方法
    * @param $data  array  请求参数
    * @return bool|array 请求结果
    */
   private function curl($api, $method, $data = [])
   {
      $url = $this->host . $api;
      strtoupper($method) == 'POST' ? $method = 'POST' : $method = 'GET';

      $ch = curl_init();
      $header = ['Content-Type: application/json',];

      if (!empty($this->username)) {
         $header[] =  'Authorization: Basic' . base64_encode($this->username . ':' . $this->password); // 登录密钥
      }

      if (!empty($data)) {
         $header[] = 'Content-Length:' . strlen(json_encode($data));
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      }

      //curl_setopt($ch, CURLOPT_HEADER, 1); // 显示 header 头
      curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000); // 超时时间 3 秒
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      $content = curl_exec($ch);
      curl_close($ch);
      if ($content) {
         return json_decode($content, true);
      }
      return [];
   }
}

// $obj = new GoFound();
// // $r = $obj->status();
// // $result = $obj->add(1,'我的世界',['title'=>'我的世界','content'=>'我的世界，建站笔记，来自 bomx.cn 编写']);
// // echo '<pre>';
// // var_dump($result);
// // echo '</pre>';
// // exit;
// $list = $obj->query('渑池');
// echo '<pre>';
// print_R($list);
// echo '</pre>';
// exit;