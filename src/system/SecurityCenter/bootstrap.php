<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

if (!defined('HTMLPURIFIER_PREFIX')) {
    define('HTMLPURIFIER_PREFIX', realpath(dirname(__FILE__) . '/vendor/htmlpurifier'));
}

// include the PHPIDS and get access to the result object
set_include_path(get_include_path() . PATH_SEPARATOR . 'system/SecurityCenter/vendor');
ZLoader::addAutoloader('HTMLPurifier', dirname(__FILE__) . '/vendor/htmlpurifier');
ZLoader::addAutoloader('IDS', realpath(dirname(__FILE__) . '/vendor'));
