<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BlockCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zikula_blocks_module.internal.block_collector')) {
            return;
        }

        $definition = $container->getDefinition('zikula_blocks_module.internal.block_collector');

        $module = '';
        foreach ($container->findTaggedServiceIds('zikula.block_handler') as $id => $tagParameters) {
            foreach ($tagParameters as $tagParameter) {
                if (!isset($tagParameter['module'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "module" attribute on "zikula.block_handler" tags.', $id));
                }
                $module = $tagParameter['module'];
            }

            $definition->addMethodCall('add', [$module . ':' . $id, new Reference($id)]);
        }
    }
}
