<?php
require 'vendor/autoload.php';



use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\S3Client;

$endpoint = 'https://bd57bf6d3639b79cb8f4457ce879e637.r2.cloudflarestorage.com'; // 替换为您的自定义 endpoint URL

// 创建 S3 客户端
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'auto', // 替换为您的区域
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => false, // 如果需要路径样式的 endpoint，可以设置为 true
    'credentials' => [
        'key'    => 'c3320a2a0c1cfbe46f1d6aef6c90e8de',
        'secret' => 'dc3de3d1e8bf2ae2b9be1e5318fed4e133ba796d6a0b19a512d2546f09c3836b',
    ],
]);


// 设置要上传的文件路径
$fileList = [
    'F:\phpstudy_pro\WWW\caiji\upload\111.txt',
    'F:\phpstudy_pro\WWW\caiji\upload\112.txt',
];

$bucket = 'kuaiyan';
$folderPath = 'apk/'; ##的上传的路径
// 准备上传命令
$commands = [];
foreach ($fileList as $file) {
    $commands[] = $s3Client->getCommand('PutObject', [
        'Bucket' => $bucket,
        'Key' => $folderPath. basename($file),
        'Body' => fopen($file, 'rb'),
    ]);
}
// 创建并执行命令池
$commandPool = new CommandPool($s3Client, $commands, [
    'concurrency' => 5, // 同时执行的命令数量
    'fulfilled' => function (Result $result, $key) {
        echo "File '{$key}' Access the file at: " . $result['ObjectURL']." uploaded successfully.\n";
    },
    'rejected' => function (AwsException $reason, $key) {
        echo "Failed to upload file '{$key}': " . $reason->getMessage() . "\n";
    },
]);

try {
    $commandPool->promise()->wait();
} catch (AwsException $e) {
    echo "Error occurred during batch upload: " . $e->getMessage() . "\n";
}

echo "All files have been processed.";