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

include 'lib/ZLoader.php';
ZLoader::register();

$core = new Zikula_Core();
$core->boot();
$eventManager = $core->getEventManager();
$serviceManager = $core->getServiceManager();

// Load system configuration
include 'config/config.php';
foreach ($GLOBALS['ZConfig'] as $config) {
    $serviceManager->loadArguments($config);
}

// load eventhandlers from config/EventHandlers directory if any.
EventUtil::attachCustomHandlers('config/EventHandlers');
EventUtil::attachCustomHandlers('lib/EventHandlers');

$eventManager->attach('setup.errorreporting', array('SystemListeners', 'defaultErrorReporting'));
$eventManager->attach('core.init', array('SystemListeners', 'setupLoggers'));
$eventManager->attach('log', array('SystemListeners', 'errorLog'));
$eventManager->attach('core.init', array('SystemListeners', 'sessionLogging'));
$eventManager->attach('core.init', array('SystemListeners', 'systemPlugins'));
$eventManager->attach('core.postinit', array('SystemListeners', 'systemHooks'));
$eventManager->attach('core.init', array('SystemListeners', 'setupDebugToolbar'));
$eventManager->attach('log.sql', array('SystemListeners', 'logSqlQueries'));
$eventManager->attach('core.init', array('SystemListeners', 'setupAutoloaderForGeneratedCategoryModels'));
$eventManager->attach('installer.module.uninstalled', array('SystemListeners', 'deleteGeneratedCategoryModelsOnModuleRemove'));
$eventManager->attach('pageutil.addvar_filter', array('SystemListeners', 'coreStylesheetOverride'));
$eventManager->attach('module_dispatch.postexecute', array('SystemListeners', 'addHooksLink'));
$eventManager->attach('module_dispatch.postexecute', array('SystemListeners', 'addServiceLink'));
$eventManager->attach('core.init', array('SystemListeners', 'initDB'));
