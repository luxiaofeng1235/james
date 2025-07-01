<?php
$mysql_server= "192.168.0.207";
$mysql_user = "wsl_user";
$mysql_password="123456";
$mysql_database= "book_center";
$conn = mysqli_connect($mysql_server,$mysql_user , $mysql_password,$mysql_database);
mysqli_set_charset($conn,"utf-8");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    print_r($conf);
    exit();
}
echo "连接成功";
?>