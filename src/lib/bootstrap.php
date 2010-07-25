<?php
include 'lib/ZLoader.php';
ZLoader::register();
$serviceManager = ServiceUtil::getManager();
$eventManager = EventUtil::getManager();

include 'config/config.php';
$serviceManager->loadArguments($GLOBALS['ZConfig']['Log']);
$serviceManager->loadArguments($GLOBALS['ZConfig']['Debug']);
$serviceManager->loadArguments($GLOBALS['ZConfig']['System']);
$serviceManager->loadArguments($GLOBALS['ZConfig']['Multisites']);

