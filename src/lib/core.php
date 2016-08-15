<?php

use Zikula\Core\Event\GenericEvent;

// legacy handling
$core = new Zikula_Core();
$core->setKernel($kernel);
$core->boot();

// these two events are called for BC only. remove in 2.0.0
$core->getDispatcher()->dispatch('bootstrap.getconfig', new GenericEvent($core));
$core->getDispatcher()->dispatch('bootstrap.custom', new GenericEvent($core));

foreach ($GLOBALS['ZConfig'] as $config) {
    $core->getContainer()->loadArguments($config);
}
$GLOBALS['ZConfig']['System']['temp'] = $core->getContainer()->getParameter('temp_dir');
$GLOBALS['ZConfig']['System']['datadir'] = $core->getContainer()->getParameter('datadir');
$GLOBALS['ZConfig']['System']['system.chmod_dir'] = $core->getContainer()->getParameter('system.chmod_dir');

ServiceUtil::getManager($core);
EventUtil::getManager($core);
$core->attachHandlers('config/EventHandlers');
