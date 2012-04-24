<?php

if (!$loader = include __DIR__.'/../vendor/autoload.php') {
    $nl = PHP_SAPI === 'cli' ? PHP_EOL : '<br />';
    echo "$nl$nl";
    if (is_writable(dirname(__DIR__)) && $installer = @file_get_contents('http://getcomposer.org/installer')) {
        echo 'You must set up the project dependencies.'.$nl;
        $installerPath = dirname(__DIR__).'/install-composer.php';
        file_put_contents($installerPath, $installer);
        echo 'The composer installer has been downloaded in '.$installerPath.$nl;
        die('Run the following commands in '.dirname(__DIR__).':'.$nl.$nl.
            'php install-composer.php'.$nl.
            'php composer.phar install'.$nl);
    }
    die('You must set up the project dependencies.'.$nl.
        'Run the following commands in '.dirname(__DIR__).':'.$nl.$nl.
        'curl -s http://getcomposer.org/installer | php'.$nl.
        'php composer.phar install'.$nl);
}

use Doctrine\Common\Annotations\AnnotationRegistry;

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// Swiftmailer needs a special autoloader to allow
// the lazy loading of the init file (which is expensive)
//require_once __DIR__.'/../vendor/swiftmailer/swiftmailer/lib/classes/Swift.php';
//Swift::registerAutoload(__DIR__.'/../vendor/swiftmailer/swiftmailer/lib/swift_init.php');

require_once __DIR__.'/../vendor/hard/Smarty/Smarty.class.php';
require_once __DIR__.'/../src/legacy/i18n/ZGettextFunctions.php';

$autoloader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$autoloader->register();
$autoloader->registerNamespace('Zikula', __DIR__.'/../src');
$autoloader->registerPrefix('Zikula_', __DIR__.'/../src/legacy');
$autoloader->registerPrefix('Categories_', __DIR__.'/../web/system/Categories/lib');
$autoloader->registerPrefix('Zend_', __DIR__. '/../vendor/hard');

if (file_exists(__DIR__.'bootstrap.php.cache')) {
    include __DIR__.'bootstrap.php.cache';
}