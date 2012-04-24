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

$bootstrap = file_exists(__DIR__.'/bootstrap.php.cache') ? __DIR__.'/bootstrap.php.cache' : __DIR__.'/autoload.php';
require_once $bootstrap;

require_once __DIR__.'/AppKernel.php';
require_once __DIR__.'/AppCache.php';

require_once __DIR__.'/../src/legacy/ZLoader.php';
ZLoader::register();
