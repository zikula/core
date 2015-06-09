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

use Symfony\Component\Debug\Debug;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\Event\GenericEvent;

$loader = require __DIR__.'/../app/autoload.php';
ZLoader::register($loader);

// TODO remove for Zikula 2.0 / migrating to Symfony 3.0
$errorReporting = error_reporting();
$errorReporting &= ~E_USER_DEPRECATED;
error_reporting($errorReporting);

$kernelConfig = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));
if (is_readable($file = __DIR__.'/../app/config/custom_parameters.yml')) {
    $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
}
$kernelConfig = $kernelConfig['parameters'];
if ($kernelConfig['env'] !== 'prod') {
    Debug::enable();
}

require __DIR__.'/../app/ZikulaKernel.php';

$kernel = new ZikulaKernel($kernelConfig['env'], $kernelConfig['debug']);
$kernel->boot();

// legacy handling
$core = new Zikula_Core();
$core->setKernel($kernel);
$core->boot();

// these two events are called for BC only. remove in 2.0.0
$core->getDispatcher()->dispatch('bootstrap.getconfig', new GenericEvent($core));
$core->getDispatcher()->dispatch('bootstrap.custom', new GenericEvent($core));

foreach ($GLOBALS['ZConfig'] as $config) {
    $core->getContainer()->loadArguments($config);
}
$GLOBALS['ZConfig']['System']['temp'] = $core->getContainer()->getParameter('temp_dir');
$GLOBALS['ZConfig']['System']['datadir'] = $core->getContainer()->getParameter('datadir');
$GLOBALS['ZConfig']['System']['system.chmod_dir'] = $core->getContainer()->getParameter('system.chmod_dir');

ServiceUtil::getManager($core);
EventUtil::getManager($core);
$core->attachHandlers('config/EventHandlers');

return $core;
