<?php
//1
//定义输出为图像类型
header("content-type:image/gif");
//新建图象
$pic=imagecreate(500,40);
//定义黑白颜色
$black=imagecolorallocate($pic,0,0,0);
$white=imagecolorallocate($pic,255,255,255);
//定义字体
$font="c://WINDOWS//fonts//simhei.ttf";
//定义输出字体串
$str = "脚本支架哈哈哈";
//写 TTF 文字到图中
imagettftext($pic,20,0,10,30,$white,$font,$str);
//建立 GIF 图型
imagegif($pic);
//结束图形，释放内存空间
imagedestroy($pic);
?>