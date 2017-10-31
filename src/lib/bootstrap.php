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

require __DIR__ . '/../app/autoload.php';
require __DIR__ . '/../lib/RequirementChecker.php';

$kernelConfig = Yaml::parse(file_get_contents(__DIR__ . '/../app/config/parameters.yml'));
if (is_readable($file = __DIR__ . '/../app/config/custom_parameters.yml')) {
    $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
}
$parameters = $kernelConfig['parameters'];
if (true === $parameters['debug']) {
    Debug::enable();
}
if ('prod' == $parameters['env']) {
    // improves performance for prod env
    include_once __DIR__ . '/../var/bootstrap.php.cache';
}

if ((isset($parameters['umask'])) && (!is_null($parameters['umask']))) {
    umask($parameters['umask']);
}

// set default locale for Intl classes
\Locale::setDefault($parameters['locale']);

// on install or upgrade, check if system requirements are met.
$requirementChecker = new RequirementChecker();
$requirementChecker->verify($parameters);

$kernel = new ZikulaKernel($parameters['env'], $parameters['debug']);
$kernel->boot();
