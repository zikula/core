<?php

namespace Zikula\ModuleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 */
class ControllerResolverCompilerPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('http_kernel')->replaceArgument(2, new \Symfony\Component\DependencyInjection\Reference('zikula.controller.resolver'));
    }
}
