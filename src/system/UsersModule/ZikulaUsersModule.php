<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;
use Zikula\UsersModule\DependencyInjection\Compiler\AuthenticationMethodCollectorPass;
use Zikula\UsersModule\DependencyInjection\Compiler\MessageModuleCollectorPass;
use Zikula\UsersModule\DependencyInjection\Compiler\ProfileModuleCollectorPass;

/**
 * Base module definition for the users module.
 */
class ZikulaUsersModule extends AbstractCoreModule
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AuthenticationMethodCollectorPass());
        $container->addCompilerPass(new ProfileModuleCollectorPass());
        $container->addCompilerPass(new MessageModuleCollectorPass());
    }
}
