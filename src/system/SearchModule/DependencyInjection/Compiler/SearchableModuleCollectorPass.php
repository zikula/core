<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SearchableModuleCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zikula_search_module.internal.searchable_module_collector')) {
            return;
        }

        $definition = $container->getDefinition('zikula_search_module.internal.searchable_module_collector');

        $bundleName = '';
        foreach ($container->findTaggedServiceIds('zikula.searchable_module') as $id => $tagParameters) {
            foreach ($tagParameters as $tagParameter) {
                if (!isset($tagParameter['bundleName'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "bundleName" attribute on "zikula.searchable_module" tags.', $id));
                }
                $bundleName = $tagParameter['bundleName'];
            }

            $definition->addMethodCall('add', [$bundleName, new Reference($id)]);
        }
    }
}
