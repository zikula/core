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

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Debug\Debug;

$loader = require __DIR__.'/../app/autoload.php';
ZLoader::register($loader);

$kernelConfig = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));
if (is_readable($file = __DIR__.'/../app/config/custom_parameters.yml')) {
    $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
}
$kernelConfig = $kernelConfig['parameters'];
$kernelConfig['debug'] === 'prod' ?: Debug::enable();

require __DIR__.'/../app/ZikulaKernel.php';

$kernel = new ZikulaKernel($kernelConfig['env'], $kernelConfig['debug']);
$kernel->boot();

// legacy handling
$core = new Zikula_Core();
$core->setKernel($kernel);
$core->boot();

foreach ($GLOBALS['ZConfig'] as $config) {
    $core->getContainer()->loadArguments($config);
}

ServiceUtil::getManager($core);
EventUtil::getManager($core);
$core->attachHandlers('config/EventHandlers');

return $core;
