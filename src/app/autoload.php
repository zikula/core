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

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * @var ClassLoader
 */
$loader = require __DIR__ . '/../vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('type');

return $loader;
