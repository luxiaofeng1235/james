<?php

$url = 'https://www.x33xs6.com/33xs/469/469823/108495.html';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22");
curl_setopt($ch, CURLOPT_ENCODING ,'gzip'); //加入gzip解析
curl_setopt($ch, CURLOPT_HEADER, 0);

//tcp设置相关--主要设置Keep-alive心跳
curl_setopt($ch,CURLOPT_TCP_KEEPALIVE,1);   // 开启
curl_setopt($ch,CURLOPT_TCP_KEEPIDLE,10);   // 空闲10秒问一次
curl_setopt($ch,CURLOPT_TCP_KEEPINTVL,10);  // 每10秒问一次
curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);//TRUE 时禁用 TCP 的 Nagle 算法，就是减少网络上的小包数量。
curl_setopt($ch, CURLOPT_NOSIGNAL, 1); //TRUE 时忽略所有的 cURL 传递给 PHP 进行的信号。在 SAPI 多线程传输时此项被默认启用，所以超时选项仍能使用。


//设置版本号和启用ipv4
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);    // 强制使用 HTTP/1.0
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch,CURLOPT_MAXREDIRS, 5) ; //定向的最大数量，这个选项是和CURLOPT_FOLLOWLOCATION一起用的
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPv4

$curl_scraped_page = curl_exec($ch);
$httpcode = curl_getinfo($ch);
curl_close($ch);

echo '<pre>';
print_R($curl_scraped_page);
echo '</pre>';
exit;
?>