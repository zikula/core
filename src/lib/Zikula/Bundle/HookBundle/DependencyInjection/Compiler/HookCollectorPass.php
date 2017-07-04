<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HookCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zikula_hook_bundle.collector.hook_collector')) {
            return;
        }

        $definition = $container->getDefinition('zikula_hook_bundle.collector.hook_collector');

        foreach ($container->findTaggedServiceIds('zikula.hook_provider') as $id => $tagParameters) {
            foreach ($tagParameters as $tagParameter) {
                if (!isset($tagParameter['areaName'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "areaName" attribute on "zikula.hook_provider" tags.', $id));
                }
                $areaName = $tagParameter['areaName'];
            }

            $definition->addMethodCall('addProvider', [$areaName, $id, new Reference($id)]);
        }

        foreach ($container->findTaggedServiceIds('zikula.hook_subscriber') as $id => $tagParameters) {
            foreach ($tagParameters as $tagParameter) {
                if (!isset($tagParameter['areaName'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "areaName" attribute on "zikula.hook_subscriber" tags.', $id));
                }
                $areaName = $tagParameter['areaName'];
            }

            $definition->addMethodCall('addSubscriber', [$areaName, new Reference($id)]);
        }
    }
}
