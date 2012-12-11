<?php
error_reporting(E_ALL | E_STRICT);
require_once __DIR__.'/../src/vendor/autoload.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// Manually load the autoloader
require_once __DIR__ . '/ClassLoader.php';

// Loader for all Zikula namespace
$classLoader = new ClassLoader('Zikula', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'lib', '_');
$classLoader->register();

// Loader for all Zikula namespace
$classLoader = new ClassLoader('Doctrine', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Doctrine', '_');
$classLoader->register();

$classLoader = new ClassLoader('Tests', __DIR__ . DIRECTORY_SEPARATOR . 'lib', '_');
$classLoader->register();

// Set include path to load the actual source libraries, this will be used by the autoloader when it resolve relative paths
//set_include_path(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path());

