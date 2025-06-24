<?php

// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :ossStore.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:oss存储类112
// ///////////////////////////////////////////////////

// use Aws\S3\S3Client;
// use Aws\Exception\AwsException;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Promise\PromiseInterface;

use GuzzleHttp\Promise\Utils;

// //利用协程来进行资源的调度，提高效率和同步机制
// use Swoole\Coroutine\Barrier;
// use Swoole\Coroutine\System;
// use function Swoole\Coroutine\run;
// use Swoole\Coroutine;
// use Swoole\Coroutine\WaitGroup;

class R3ClientObject
{


    private $maxThread = 50; //定义最大的线程数
    private $endpoint = null; //终结点
    private $appkey = null; //对应的key
    private $appsecret = null; //秘钥
    private $region = ''; //区域
    private $version = null; //版本
    public   $bucket = null; //桶的名称
    public  $s3Client = null; //实例化的链接对象
    public function __construct()
    {
        $this->appkey = Env::get('STORE.APPKEY'); //appkey
        $this->appsecret = Env::get('STORE.APPSECRET'); //appsecret
        $this->endpoint = Env::get('STORE.ENDPOINT'); //终结点地址
        $this->bucket  = Env::get('STORE.BUCKET'); //桶名称
        $this->region = Env::get('STORE.REGION'); //区域
        $this->version = Env::get('STORE.VERSION'); //版本号  
        $this->s3Client = new S3Client([
            'version' => $this->version,
            'region'  => $this->region, // 替换为您的区域
            'endpoint' => $this->endpoint,
            'use_path_style_endpoint' => false, // 如果需要路径样式的 endpoint，可以设置为 true
            'credentials' => [
                'key'    => $this->appkey,
                'secret' => $this->appsecret,
            ],
        ]);
    }


    /**
     * @note 检查OOS中的远程文件是否存在
     *
     * @param $key string key标识
     * @return unknow
     */
    public function checkRemoteFiles($key = "")
    {
        if (!$key) {
            return false;
        }
        try {
            // 检查文件是否存在
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);
            // 文件存在
            echo "File exists!";
        } catch (S3Exception $e) {
            // 文件不存在
            echo "File does not exist.";
        }
        return true;
    }




      /**
     * @note 定义上传的文件路径信息
     *
     * @param $s3 object 存储R2对象信息
     * @param $file string 文件储存路径
     * @param  $bucket string 桶信息
     * @return unknow
     */
    public function uploadFileAsync($s3, $file, $bucket): PromiseInterface
    {
        return $s3->putObjectAsync([
            'Bucket' => $bucket,
            'Key'    => $file['key'],
            'SourceFile' => $file['path']
        ]);
    }

    /**
     * @note 单个文件进行同步
     *
     * @param $filepath string 文件路径
     * @return knowner
     */
    public function handlePutFiles($filepath = "")
    {
        if (!$filepath || !file_exists($filepath)) {
            return false;
        }
        $folderPath = dirname(substr($filepath, 1)); //不要斜杠
        $keyname = $folderPath . DS . basename($filepath); //上传的指定的keyname信息


        $file = [
            'path' => $filepath,
            'key'  => $keyname
        ];
            
        // 定义异步上传文件的函数
        $s3 = $this->s3Client;
        $bucket = $this->bucket;

        // 异步上传文件
        $promise = $this->uploadFileAsync($s3, $file, $bucket);
        // 等待上传任务完成
        try {
            $result = $promise->wait();
            // $response = Utils::settle($promise)->wait();
            // $result = $response[0]['value'] ?? [];
            echo "Upload success: {$result['ObjectURL']}\n";
        } catch (AwsException $e) {
            echo "Upload failed: {$e->getMessage()}\n";
        }
    }

    /**
     * @note 批量上传文件
     *
     * @param $fileList array 批量上传文件信息
     * @return knowner
     */
    public function multiPutFiles($fileList = [])
    {
        if (!$fileList) {
            return false;
        }


        // 准备上传命令
        $commands = [];
        // $folderPath = 'apk/'; ##的上传的路径
        foreach ($fileList as $file) {
            //读取文件判断是否为空
            $content = readFileData($file);
            if (!$file || !file_exists($file) || !$content) {
                continue;
            }
            //设置存储的指定路径，根据上传来进行定义
            $keyname = substr($file, 1); //不要最开头的/存储的话
            $commands[] = $this->s3Client->getCommand('PutObject', [
                'Bucket' => $this->bucket,
                'Key' => $keyname,
                // 'Body' => fopen($file, 'rb'),
                'SourceFile' => $file,
            ]);
        }

        $count = count($fileList);
        // 创建并执行命令池
        $commandPool = new CommandPool($this->s3Client, $commands, [
            'concurrency' => $this->maxThread, // 同时执行的命令数量,到时候动态控制下
            'fulfilled' => function (Result $result, $key) {
                // 处理成功的请求
                echo date('Y-m-d H:i:s') . "   File '{$key}' Upload success: " . $result['ObjectURL'] . "\n";
            },
            // 处理被拒绝的请求
            'rejected' => function (AwsException $reason, $key) {
                echo date('Y-m-d H:i:s') . "\tFailed to upload file '{$key}': " . $reason->getMessage() . "\n";
            },
            'before'      => function (\Aws\CommandInterface $command) {
                // 可以在此处添加一些请求前的处理逻辑
            },
        ]);
        //监听状态
        try {
            $commandPool->promise()->wait();
        } catch (AwsException $e) {
            echo "Error occurred during batch upload: " . $e->getMessage() . "\n";
        }
        echo "All files have been processed.\r\n";
        echo "\r\n";
        return true;
    }
}
