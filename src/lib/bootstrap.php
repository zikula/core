<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Debug\Debug;
use Symfony\Component\Yaml\Yaml;

require __DIR__.'/../app/autoload.php';
require __DIR__.'/../lib/requirementCheck.php';

$kernelConfig = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));
if (is_readable($file = __DIR__.'/../app/config/custom_parameters.yml')) {
    $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
}
$kernelConfig = $kernelConfig['parameters'];
if ($kernelConfig['debug'] == true) {
    Debug::enable();
}
if ($kernelConfig['env'] == 'prod') {
    // improves performance for prod env
    include_once __DIR__.'/../var/bootstrap.php.cache';
}

if ((isset($kernelConfig['umask'])) && (!is_null($kernelConfig['umask']))) {
    umask($kernelConfig['umask']);
}

// set default locale for Intl classes
\Locale::setDefault($kernelConfig['locale']);

// on install or upgrade, check if system requirements are met.
requirementCheck($kernelConfig);

$kernel = new ZikulaKernel($kernelConfig['env'], $kernelConfig['debug']);
$kernel->boot();
