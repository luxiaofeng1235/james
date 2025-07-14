<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,LinkTone
// 日 期：2025年07月11日
// 作　者：卢晓峰
// E-mail :xiaofeng.200@163.com
// 文件名 :openc_test.php
// 创建时间:2025-07-11 03:43:13
// 编 码：UTF-8
// 摘 要:opencc本地测试类，方便进行调试
// ///////////////////////////////////////////////////
class OpenCC
{
    private $binPath;
    private $configPath;

    public function __construct(
        $binPath = 'F:\OpenCC\build\bin\opencc.exe', // opencc.exe 路径
        $configPath = 'F:\OpenCC\build\share\opencc\\' // opencc 配置文件路径
    ) {
        //opencc的主路径
        $this->binPath = $binPath;
        //opencc的配置文件路径
        $this->configPath = $configPath;
    }

    public function convert($text, $conversion = 't2s')
    {
        $configFile = $this->configPath . $conversion . '.json';

        if (!file_exists($this->binPath)) {
            throw new Exception("OpenCC binary not found: {$this->binPath}");
        }

        if (!file_exists($configFile)) {
            throw new Exception("Config file not found: {$configFile}");
        }

        // Windows 下使用临时文件更稳定
        $tmpIn = tempnam(sys_get_temp_dir(), 'occ_');
        file_put_contents($tmpIn, $text);

        $cmd = sprintf(
            '"%s" -i "%s" -c "%s" 2>&1',
            $this->binPath,
            $tmpIn,
            $configFile
        );

        $result = shell_exec($cmd);
        unlink($tmpIn);

        return trim($result);
    }

    // 常用转换方法
    public function t2s($text)
    {
        return $this->convert($text, 't2s');
    }
    public function s2t($text)
    {
        return $this->convert($text, 's2t');
    }
    public function tw2sp($text)
    {
        return $this->convert($text, 'tw2sp');
    }
    public function s2twp($text)
    {
        return $this->convert($text, 's2twp');
    }
}
$opencc = new OpenCC();
var_dump($opencc);
echo $opencc->t2s('繁體中文測試');
echo "\r\n";
echo $opencc->s2t('简体中文测试');
?>