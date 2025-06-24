<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :luxiaofeng.200@163.com
// 文件名 :auto_update_book.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:自动更新书籍上架
// ///////////////////////////////////////////////////
// 确保COM组件功能已开启
require_once 'vendor/autoload.php';
require_once 'library/rndChinaName.class.php';

// 示例用法
// $numbers = array(0, 1, 12, 345, 6789, 123456, 1234567, 12345678, 123456789, 1234567890);
$numbers = range(0, 1501);
foreach ($numbers as $number) {
    echo $number . " => " . convert_to_chinese_uppercase($number) . "\n";
}
exit;



$file_path = "./demo.txt";
$res =file_get_contents($file_path);

$chunk_size = 3000;
$result = mbStrSplit($res, $chunk_size);
echo "<pre>";

var_dump($result);
exit;


$name_obj = new rndChinaName();


$string = "这是一个长的中文字符串,需要分割成数组。";
$chunks = mb_str_split($string,5,'UTF-8');
echo '<pre>';
var_dump($chunks);
echo '</pre>';
exit;

$chunk_size = 6;

$chunks = [];
for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i += $chunk_size) {
    $chunks[] = mb_strcut($string, $i, $chunk_size, 'UTF-8');
}
echo "<pre>";
print_r($chunks);
exit;

$name = $name_obj->getName(2);
dd($name);exit;

function docx2html($source)
{
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($source);
    $html = '';
    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $ele1) {
            $paragraphStyle = $ele1->getParagraphStyle();
            if ($paragraphStyle) {
                $html .= '<p style="text-align:'. $paragraphStyle->getAlignment() .';text-indent:20px;">';
            } else {
                $html .= '<p>';
            }
            if ($ele1 instanceof \PhpOffice\PhpWord\Element\TextRun) {
                foreach ($ele1->getElements() as $ele2) {
                    if ($ele2 instanceof \PhpOffice\PhpWord\Element\Text) {
                        $style = $ele2->getFontStyle();
                        $fontFamily = mb_convert_encoding($style->getName(), 'GBK', 'UTF-8');
                        $fontSize = $style->getSize();
                        $isBold = $style->isBold();
                        $styleString = '';
                        $fontFamily && $styleString .= "font-family:{$fontFamily};";
                        $fontSize && $styleString .= "font-size:{$fontSize}px;";
                        $isBold && $styleString .= "font-weight:bold;";
                        $html .= sprintf('<span style="%s">%s</span>',
                            $styleString,
                            mb_convert_encoding($ele2->getText(), 'GBK', 'UTF-8')
                        );
                    } elseif ($ele2 instanceof \PhpOffice\PhpWord\Element\Image) {
                        $imageSrc = 'images/' . md5($ele2->getSource()) . '.' . $ele2->getImageExtension();
                        $imageData = $ele2->getImageStringData(true);
                        // $imageData = 'data:' . $ele2->getImageType() . ';base64,' . $imageData;
                        file_put_contents($imageSrc, base64_decode($imageData));
                        $html .= '<img src="'. $imageSrc .'" style="width:100%;height:auto">';
                    }
                }
            }
            $html .= '</p>';
        }
    }

    return mb_convert_encoding($html, 'UTF-8', 'GBK');
}
$dir = str_replace('\\', '/', __DIR__) . '/';
$source = $dir . '123.docx';
echo docx2html($source);
?>