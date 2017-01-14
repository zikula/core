<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProfileModuleCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zikula_users_module.internal.profile_module_collector')) {
            return;
        }

        $definition = $container->getDefinition('zikula_users_module.internal.profile_module_collector');

        foreach ($container->findTaggedServiceIds('zikula.profile_module') as $id => $tagParameters) {
            foreach ($tagParameters as $tagParameter) {
                if (!isset($tagParameter['bundleName'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "bundleName" attribute on "zikula.profile_module" tags.', $id));
                }
                $bundleName = $tagParameter['bundleName'];
            }

            $definition->addMethodCall('add', [$bundleName, new Reference($id)]);
        }
    }
}
