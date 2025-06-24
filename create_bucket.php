<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$bucket = 'testarr';
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

try {
    // 创建存储桶
    $result = $s3Client->createBucket([
        'Bucket' => $bucket,
    ]);
    // 等待存储桶被创建
    $s3Client->waitUntil('BucketExists', [
        'Bucket' => $bucket,
    ]);
    echo "Bucket created successfully: " . $bucket . "\n";

} catch (AwsException $e) {
    echo "Error creating bucket: " . $e->getMessage() . "\n";
}
?>
