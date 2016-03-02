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

use Symfony\Component\Debug\Debug;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\Event\GenericEvent;

$loader = require __DIR__.'/../app/autoload.php';
ZLoader::register($loader);

/**
 * create class aliases for BC
 */
class_alias('\Zikula\CategoriesModule\Entity\CategoryEntity', '\Zikula\Module\CategoriesModule\Entity\CategoryEntity', true);
class_alias('\Zikula\UsersModule\Entity\UserEntity', '\Zikula\Module\UsersModule\Entity\UserEntity', true);
class_alias('\Zikula\ExtensionsModule\Entity\ExtensionVarEntity', '\Zikula\Core\Doctrine\Entity\ExtensionVarEntity', true);
class_alias('\Zikula\ExtensionsModule\Entity\ExtensionEntity', '\Zikula\Core\Doctrine\Entity\ExtensionEntity', true);
class_alias('\Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity', '\Zikula\Core\Doctrine\Entity\ExtensionDependencyEntity', true);
class_alias('\Zikula\CategoriesModule\Entity\AbstractCategoryAssignment', '\Zikula\Core\Doctrine\Entity\AbstractEntityCategory', true);
class_alias('\Zikula\ThemeModule\Engine\Annotation\Theme', '\Zikula\Core\Theme\Annotation\Theme', true);
class_alias('\Zikula\ThemeModule\AbstractTheme', '\Zikula\Core\AbstractTheme', true);
class_alias('\Zikula\Bundle\HookBundle\Api\HookApi', '\Zikula\ExtensionsModule\Api\HookApi');
class_alias('\Zikula\Bundle\HookBundle\Bundle\ProviderBundle', '\Zikula\Component\HookDispatcher\ProviderBundle');
class_alias('\Zikula\Bundle\HookBundle\Bundle\SubscriberBundle', '\Zikula\Component\HookDispatcher\SubscriberBundle');
class_alias('\Zikula\Bundle\HookBundle\AbstractHookContainer', '\Zikula\Component\HookDispatcher\AbstractContainer');
class_alias('\Zikula\Bundle\HookBundle\Hook\AbstractHookListener', '\Zikula\Core\Hook\AbstractContainer');
class_alias('\Zikula\Bundle\HookBundle\Hook\DisplayHook', '\Zikula\Core\Hook\DisplayHook');
class_alias('\Zikula\Bundle\HookBundle\Hook\DisplayHookResponse', '\Zikula\Core\Hook\DisplayHookResponse');
class_alias('\Zikula\Bundle\HookBundle\Hook\FilterHook', '\Zikula\Core\Hook\FilterHook');
class_alias('\Zikula\Bundle\HookBundle\Hook\Hook', '\Zikula\Component\HookDispatcher\Hook');
class_alias('\Zikula\Bundle\HookBundle\Hook\ProcessHook', '\Zikula\Core\Hook\ProcessHook');
class_alias('\Zikula\Bundle\HookBundle\Hook\ValidationHook', '\Zikula\Core\Hook\ValidationHook');
class_alias('\Zikula\Bundle\HookBundle\Hook\ValidationProviders', '\Zikula\Core\Hook\ValidationProviders');
class_alias('\Zikula\Bundle\HookBundle\Hook\ValidationResponse', '\Zikula\Core\Hook\ValidationResponse');

$kernelConfig = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));
if (is_readable($file = __DIR__.'/../app/config/custom_parameters.yml')) {
    $kernelConfig = array_merge($kernelConfig, Yaml::parse(file_get_contents($file)));
}
$kernelConfig = $kernelConfig['parameters'];
if ($kernelConfig['env'] !== 'prod') {
    // hide deprecation errors
    // @todo remove exclusions for Core-2.0
    Debug::enable(E_ALL & ~E_USER_DEPRECATED);
}

if ((isset($kernelConfig['umask'])) && (!is_null($kernelConfig['umask']))) {
    umask($kernelConfig['umask']);
}

require __DIR__.'/../app/ZikulaKernel.php';

$kernel = new ZikulaKernel($kernelConfig['env'], $kernelConfig['debug']);
$kernel->boot();

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

return $core;
