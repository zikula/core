<?php
include 'lib/ZLoader.php';
ZLoader::register();

$core = new Zikula();
$core->boot();
$eventManager = $core->getEventManager();
$serviceManager = $core->getServiceManager();

// load eventhandlers from config/EventHandlers directory if any.
EventUtil::attachCustomHandlers('config/EventHandlers');

$eventManager->attach('setup.errorreporting', array('SystemListenersUtil', 'defaultErrorReporting'));
$eventManager->attach('core.init', array('SystemListenersUtil', 'setupLoggers'));
$eventManager->attach('log', array('SystemListenersUtil', 'errorLog'));
$eventManager->attach('core.init', array('SystemListenersUtil', 'sessionLogging'));
$eventManager->attach('core.init', array('SystemListenersUtil', 'systemPlugins'));
$eventManager->attach('core.postinit', array('SystemListenersUtil', 'systemHooks'));
$eventManager->attach('core.init', array('SystemListenersUtil', 'setupDebugToolbar'));
$eventManager->attach('log.sql', array('SystemListenersUtil', 'logSqlQueries'));

include 'config/config.php';
global $ZRuntime;
$ZRuntime = array();
$serviceManager->loadArguments($GLOBALS['ZConfig']['Log']);
$serviceManager->loadArguments($GLOBALS['ZConfig']['Debug']);
$serviceManager->loadArguments($GLOBALS['ZConfig']['System']);
$serviceManager->loadArguments($GLOBALS['ZConfig']['Multisites']);

