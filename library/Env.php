<?php
/*
 * 加载配置文件，初始化线上和线下配置信息 
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    
class Env
{
    private static $loaded = false;
    private static $loadedFiles = [];
    private const ENV_PREFIX = 'PHP_';
    
    // 配置文件映射
    private const CONFIG_FILES = [
        'env' => [
            'prod' => '/.env_prod',//线上配置
            'dev' => '/.env_dev', //线下配置
            'test' => '/.env_test', //测试配置
        ],
        'business' => '/.business_config', //通用业务配置
    ];

    public static function get($name, $default = null)
    {
        if (!self::$loaded) {
            self::initializeConfig();
        }

        $result = self::getEnvironmentVariable($name);
    
        return $result !== false ? self::convertValue($result) : $default;
    }

    /**
     * 初始化配置加载
     */
    private static function initializeConfig()
    {
        try {
            $environment = self::getCurrentEnvironment();
            
            // 加载环境特定的配置文件
            self::loadEnvironmentConfig($environment);
            
            // 加载业务配置文件
            self::loadBusinessConfig();
            
            self::$loaded = true;
            
        } catch (\Exception $e) {
            // 记录错误但不阻断程序执行
            error_log("Failed to load environment config: " . $e->getMessage());
            self::$loaded = true; // 防止重复尝试加载
        }
    }

    /**
     * 加载环境特定的配置文件
     */
    private static function loadEnvironmentConfig($environment)
    {
        $configFiles = self::CONFIG_FILES['env'];
        
        if (!isset($configFiles[$environment])) {
            throw new \InvalidArgumentException("Unsupported environment: {$environment}");
        }
        
        $envFile = dirname(__DIR__) . $configFiles[$environment];
        self::loadConfigFile($envFile, "environment config ({$environment})");
    }

    /**
     * 加载业务配置文件
     */
    private static function loadBusinessConfig()
    {
        $businessFile = dirname(__DIR__) . self::CONFIG_FILES['business'];
        self::loadConfigFile($businessFile, 'business config', false);
    }

    /**
     * 加载配置文件的通用方法
     */
    private static function loadConfigFile($filePath, $description, $required = true)
    {
  
            
        // 避免重复加载同一文件
        if (in_array($filePath, self::$loadedFiles, true)) {
            return;
        }

        if (!file_exists($filePath)) {
            if ($required) {
                throw new \RuntimeException("Required {$description} file not found: {$filePath}");
            }
            
            error_log("Optional {$description} file not found: {$filePath}");
            return;
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException("Cannot read {$description} file: {$filePath}");
        }

        self::loadFile($filePath);
        self::$loadedFiles[] = $filePath;
    }

    /**
     * 获取环境变量值
     * @param string $name
     * @return string|false
     */
    private static function getEnvironmentVariable($name,$default = null)
    {
        $aa =static::ENV_PREFIX . strtoupper(str_replace('.', '_', $name));
        

        // if(strpos($name, '.')){
            
        // }else{
        //     $result =getenv($name);
        // }
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

    /**
     * 转换配置值类型
     * @param string $value
     * @return mixed
     */
    private static function convertValue($value)
    {
        $lowerValue = strtolower($value);
        
        switch ($lowerValue) {
            case 'true':
            case '1':
            case 'yes':
            case 'on':
                return true;
                
            case 'false':
            case '0':
            case 'no':
            case 'off':
            case '':
                return false;
                
            case 'null':
                return null;
                
            default:
                // 处理数字类型
                if (is_numeric($value)) {
                    return strpos($value, '.') !== false ? (float)$value : (int)$value;
                }
                return $value;
        }
    }

    /**
     * 获取当前环境
     */
    private static function getCurrentEnvironment()
    {
        if (isset($_ENV['APP_ENV'])) {
            return $_ENV['APP_ENV'];
        }
        
        if (isset($_SERVER['APP_ENV'])) {
            return $_SERVER['APP_ENV'];
        }
        
        $env = getenv('RUN_ENV');
        return $env !== false ? $env : 'prod';
    }

    /**
     * 实际加载.env文件的方法 (需要根据你的实际实现调整)
     */
    private static function loadFile($filePath)
    {
        // 这里应该是你现有的loadFile实现
        // 例如使用 Dotenv 库或自定义解析逻辑
        // $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if (!file_exists($filePath)) {
            throw new \Exception('配置文件' . $filePath . '不存在');
        }     
        $env = parse_ini_file($filePath, true);   
         foreach ($env as $key => $val) {
            $prefix = static::ENV_PREFIX . strtoupper($key);
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $item = $prefix . '_' . strtoupper($k);
                    putenv("$item=$v");
                }
            }else{
                putenv("$prefix=$val");
            }
        }
        if (!is_readable($filePath)) {
            throw new \RuntimeException("Cannot read file: {$filePath}");
        }
    }

    /**
     * 重置配置状态 (主要用于测试)
     */
    public static function reset()
    {
        self::$loaded = false;
        self::$loadedFiles = [];
    }

    /**
     * 获取所有已加载的配置文件
     */
    public static function getLoadedFiles()
    {
        return self::$loadedFiles;
    }
}