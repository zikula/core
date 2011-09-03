<?php

namespace Zikula\ModulesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class RuntimeBundlesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zikula.runtimebundleproviders')) {
            $definition = $container->getDefinition('zikula.runtimebundleproviders');
            
            foreach ($container->findTaggedServiceIds('zikula.kernel.dynamicbundles') as $id => $attributes) {
                $definition->addMethodCall('addProvider', array(new Reference($id)));
            }
        }
    }
}
