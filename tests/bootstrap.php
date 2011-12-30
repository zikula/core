<?php
error_reporting(E_ALL | E_STRICT);
require_once 'PHPUnit/TextUI/TestRunner.php';

// Manually load the autoloader
require_once __DIR__ . '/ClassLoader.php';

// Loader for all Zikula namespace
$classLoader = new ClassLoader('Zikula', __DIR__.'/../src/lib', '\\');
$classLoader->register();

$classLoader = new ClassLoader('Zikula', __DIR__.'/../src/lib/legacy', '_');
$classLoader->register();

// Loader for all Doctrine 1 namespace
$classLoader = new ClassLoader('Doctrine', __DIR__.'/../src/lib/vendor/Doctrine1', '_');
$classLoader->register();

$classLoader = new ClassLoader('Tests', __DIR__.'/lib', '_');
$classLoader->register();

$classLoader = new ClassLoader('Zikula\\Tests', __DIR__.'/lib', '\\');
$classLoader->register();

$classLoader = new ClassLoader('Symfony', __DIR__.'/../src/vendor/symfony', '\\');
$classLoader->register();

// Set include path to load the actual source libraries, this will be used by the autoloader when it resolve relative paths
//set_include_path(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path());

