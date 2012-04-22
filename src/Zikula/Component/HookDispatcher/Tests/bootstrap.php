<?php
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
}

if (!class_exists('Symfony\Component\EventDispatcher\Event')) {
    die('The test environment requires Symfony2 Event Dispatcher Component');
}

if (!class_exists('Symfony\Component\DependencyInjection\Container')) {
    die('The test environment requires Symfony2 Dependency Injection Component');
}

spl_autoload_register(function ($class) {
    if (0 === strpos(ltrim($class, '/'), 'Zikula\Component\HookDispatcher')) {
        if (file_exists($file = __DIR__.'/../'.substr(str_replace('\\', '/', $class), strlen('Zikula\Component\HookDispatcher')).'.php')) {
            require_once $file;
        }
    }
});