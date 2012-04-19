<?php

namespace Zikula\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class ControllerResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /* @var Definition $httpKernelDef */
        $httpKernelDef = $container->getDefinition('http_kernel');
        foreach ($httpKernelDef->getArguments() as $key => $argument) {
            if ($argument instanceof Reference && $argument == 'controller_resolver') {
                $httpKernelDef->replaceArgument($key, new Reference('controller_resolver_ajax'));
            }
        }
    }
}
