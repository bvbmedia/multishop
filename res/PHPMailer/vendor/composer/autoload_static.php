<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit18d0ea22c8901f53dfd43c66aab93d64
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit18d0ea22c8901f53dfd43c66aab93d64::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit18d0ea22c8901f53dfd43c66aab93d64::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
