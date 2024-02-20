<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6e97d1e6456c5c5ed2f06f48078badf8
{
    public static $files = array (
        'ad155f8f1cf0d418fe49e248db8c661b' => __DIR__ . '/..' . '/react/promise/src/functions_include.php',
        '8592c7b0947d8a0965a9e8c3d16f9c24' => __DIR__ . '/..' . '/elasticsearch/elasticsearch/src/autoload.php',
    );

    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\view\\driver\\' => 18,
            'think\\' => 6,
        ),
        'R' => 
        array (
            'React\\Promise\\' => 14,
        ),
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Stream\\' => 18,
            'GuzzleHttp\\Ring\\' => 16,
        ),
        'E' => 
        array (
            'Elasticsearch\\' => 14,
        ),
        'A' => 
        array (
            'Acme\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\view\\driver\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-view/src',
        ),
        'think\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-template/src',
        ),
        'React\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/react/promise/src',
        ),
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'GuzzleHttp\\Stream\\' => 
        array (
            0 => __DIR__ . '/..' . '/ezimuel/guzzlestreams/src',
        ),
        'GuzzleHttp\\Ring\\' => 
        array (
            0 => __DIR__ . '/..' . '/ezimuel/ringphp/src',
        ),
        'Elasticsearch\\' => 
        array (
            0 => __DIR__ . '/..' . '/elasticsearch/elasticsearch/src/Elasticsearch',
        ),
        'Acme\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Monolog' => 
            array (
                0 => __DIR__ . '/..' . '/monolog/monolog/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6e97d1e6456c5c5ed2f06f48078badf8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6e97d1e6456c5c5ed2f06f48078badf8::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit6e97d1e6456c5c5ed2f06f48078badf8::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit6e97d1e6456c5c5ed2f06f48078badf8::$classMap;

        }, null, ClassLoader::class);
    }
}
