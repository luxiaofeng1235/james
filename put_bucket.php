<?php
//上传文件
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$bucket = 'kuaiyan';
$endpoint = 'https://762f1098cb9341cd232a2e44bb10c0b8.r2.cloudflarestorage.com'; // 替换为您的自定义 endpoint URL

$filepath = '/www/wwwroot/work_project/novelProject/ccc.txt'; // 本地文件路径
if(!file_exists($filepath)){
    exit("no this pic files\r\n");
}


// 创建 S3 客户端
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'auto', // 替换为您的区域
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => false, // 如果需要路径样式的 endpoint，可以设置为 true
    'credentials' => [
        'key'    => '44d7c6aeff8182cb45ac644fbc9f1be6',
        'secret' => '32b174fe99aa40bc45d47c2118192bfc002d2a29997f18580a27bab249a28fca',
    ],
]);
$folderPath = 'apk/'; ##的上传的路径
$keyname =$folderPath. basename($filepath); //上传的指定的keyname信息
//分三个目录/pic chapter txt
try {
    $result = $s3Client->putObject([
        'Bucket' => $bucket,
        'Key'    => $keyname,
        'SourceFile' => $filepath,
        'ACL'    => 'public-read' //设置公用读取
    ]);
    echo '<pre>';
    var_dump($result);
    echo '</pre>';
    echo "File uploaded successfully. Access the file at: " . $result['ObjectURL'];
} catch (AwsException $e) {
     echo "Error uploading file: " . $e->getMessage();
}
?>
