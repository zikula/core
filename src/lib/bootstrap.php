<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Debug\Debug;

if (isset($_SERVER['HTTP_HOST']) && !extension_loaded('xdebug')) {
    set_exception_handler(function (Exception $e) {
        echo '<pre>Uncaught exception '.$e->getMessage().' in '.$e->getFile().' line, '.$e->getLine()."\n";
        echo $e->getTraceAsString()."</pre>";
    });
}

$loader = require __DIR__.'/../app/autoload.php';
ZLoader::register($loader);

$file = is_readable($file = __DIR__.'/../app/config/custom_kernel.yml') ? $file : __DIR__.'/../app/config/kernel.yml';
$kernelConfig = Yaml::parse(file_get_contents($file));
if (in_array($kernelConfig['debug'], array('dev', 'test'))) {
    Debug::enable();
};

require __DIR__.'/../app/ZikulaKernel.php';

$kernel = new ZikulaKernel($kernelConfig['env'], $kernelConfig['debug']);
$kernel->boot();

$core = new Zikula_Core();
$core->setKernel($kernel);
$core->boot();

// Load system configuration
$event = new \Zikula\Core\Event\GenericEvent($core);
$core->getDispatcher()->dispatch('bootstrap.getconfig', $event);

$event = new \Zikula\Core\Event\GenericEvent($core);
$core->getDispatcher()->dispatch('bootstrap.custom', $event);

return $core;
