<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
