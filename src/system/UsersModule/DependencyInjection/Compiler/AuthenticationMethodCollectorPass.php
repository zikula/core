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

class AuthenticationMethodCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zikula_users_module.internal.authentication_method_collector')) {
            return;
        }

        $definition = $container->getDefinition('zikula_users_module.internal.authentication_method_collector');

        foreach ($container->findTaggedServiceIds('zikula.authentication_method') as $id => $tagParameters) {
            foreach ($tagParameters as $tagParameter) {
                if (!isset($tagParameter['alias'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "alias" attribute on "zikula.authentication_method" tags.', $id));
                }
                $alias = $tagParameter['alias'];
            }

            $definition->addMethodCall('add', [$alias, new Reference($id)]);
        }
    }
}
