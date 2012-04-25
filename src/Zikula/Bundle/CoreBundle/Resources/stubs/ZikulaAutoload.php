<?php

use Composer\Autoload\ClassLoader;

class ZikulaAutoload
{
    /**
     * @var ClassLoader
     */
    private static $autoloader;

    public static function initialize(ClassLoader $autoloader = null)
    {
        if (null === self::$autoloader) {
            if (null === $autoloader) {
                $autoloader = new ClassLoader();
                $autoloader->register();
            }

            return self::$autoloader = $autoloader;
        }

        throw new \RuntimeException('Already initialized');
    }

    public static function add($namespace, $dir)
    {
        if (!in_array($namespace, self::$autoloader->getPrefixes())) {
            self::$autoloader->add($namespace, dirname($dir));
        }
    }
}

