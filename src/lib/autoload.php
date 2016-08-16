<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

require __DIR__.'/../app/ZikulaKernel.php';
