<?php
/*
 * 加载配置文件 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */

class Env
{
    static $loaded = false;
    const ENV_PREFIX = 'PHP_';

    /**
     * 加载配置文件
     * @access public
     * @param string $filePath 配置文件路径
     * @return void
     * @throws \Exception
     */
    public static function loadFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception('配置文件' . $filePath . '不存在');
        }
        self::$loaded = true;
        //返回二位数组
        $env = parse_ini_file($filePath, true);
        foreach ($env as $key => $val) {
            $prefix = static::ENV_PREFIX . strtoupper($key);
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $item = $prefix . '_' . strtoupper($k);
                    putenv("$item=$v");
                }
            } else {
                putenv("$prefix=$val");
            }
        }
    }

    /**
    * 获取当前运行环境
    * @return string
    */
    private static function getCurrentEnvironment(): string
    {
        // 优先从 getenv 获取
        $env = getenv('RUN_ENV');
        if ($env !== false && !empty($env)) {
            return strtolower(trim($env));
        }
        
        // 从 $_ENV 获取
        if (isset($_ENV['RUN_ENV']) && !empty($_ENV['RUN_ENV'])) {
            return strtolower(trim($_ENV['RUN_ENV']));
        }
        
        // 从 $_SERVER 获取
        if (isset($_SERVER['RUN_ENV']) && !empty($_SERVER['RUN_ENV'])) {
            return strtolower(trim($_SERVER['RUN_ENV']));
        }
        
        // 默认为开发环境
        return 'dev';
    }

    /**
     * 获取环境变量值
     * @access public
     * @param string $name 环境变量名（支持二级 . 号分割）
     * @param string $default 默认值
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        if (!self::$loaded) {
            try {
                $environment = self::getCurrentEnvironment();
                if( $environment !='prod'){//加载开发环境测试
                    self::loadFile(dirname(__DIR__) . '/.env_dev');
                }else{//下载线上环境配置
                    self::loadFile(dirname(__DIR__) . '/.env_prod');
                }
                //这里统一加载业务端的配置
                self::loadFile(dirname(__DIR__) . '/.env_config');
            } catch (\Exception $e) {
                return $default;
            }
        } 
        $result = getenv(static::ENV_PREFIX . strtoupper(str_replace('.', '_', $name)));
        if (false !== $result) {
            if ('false' === $result) {
                $result = false;
            } elseif ('true' === $result) {
                $result = true;
            }
            return $result;
        }
        return $default;
    }
}
?>