<?php

namespace Zikula\Bundle\ModuleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Zikula\ModuleBundle\DependencyInjection\Compiler\ControllerResolverCompilerPass;

class ZikulaModuleBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ControllerResolverCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
