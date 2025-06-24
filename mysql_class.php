<?php
$mysql_server= "127.0.0.1";
$mysql_user = "root";
$mysql_password="root";
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