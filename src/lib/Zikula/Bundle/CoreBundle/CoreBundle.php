<?php

namespace Zikula\Bundle\CoreBundle;

use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\FixDoctrineDbalConnectionDefinitionPass;
use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\RegisterCoreListenersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;

class CoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterCoreListenersPass(), PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new FixDoctrineDbalConnectionDefinitionPass());

        // todo - see if we can do this only on module install/upgrade - drak
        $container->addCompilerPass(new ValidateServiceDefinitionsPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
