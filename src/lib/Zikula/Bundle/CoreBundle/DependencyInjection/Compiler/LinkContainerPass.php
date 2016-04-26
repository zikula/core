<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LinkContainerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zikula.link_container_collector')) {
            return;
        }

        $definition = $container->getDefinition('zikula.link_container_collector');

        foreach ($container->findTaggedServiceIds('zikula.link_container') as $id => $linkContainer) {
            $definition->addMethodCall('addContainer', [new Reference($id)]);
        }
    }
}
