<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/RequirementChecker.php';

$kernelConfig = Yaml::parse(file_get_contents(realpath(__DIR__ . '/services.yaml')));
if (is_readable($file = __DIR__ . '/services_custom.yaml')) {
    $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
}
$parameters = $kernelConfig['parameters'];
if (true === $parameters['debug']) {
    Debug::enable();
}

if (isset($parameters['umask']) && null !== $parameters['umask']) {
    umask($parameters['umask']);
}

// set default locale for Intl classes
if (extension_loaded('intl')) {
    Locale::setDefault($parameters['locale']);
}

// Globally ignore @type annotation. Necessary to be able to use the extended array documentation syntax.
AnnotationReader::addGlobalIgnoredName('type');

// on install or upgrade, check if system requirements are met.
$requirementChecker = new RequirementChecker();
$requirementChecker->verify($parameters);

$kernel = new Kernel($parameters['env'], $parameters['debug']);
$kernel->boot();
