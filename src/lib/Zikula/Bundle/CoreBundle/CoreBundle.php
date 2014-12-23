<?php

namespace Zikula\Bundle\CoreBundle;

use Matthias\SymfonyServiceDefinitionValidator\Compiler\Compatibility\FixSymfonyValidatorDefinitionPass;
use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrinePass;
use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\RegisterCoreListenersPass;

class CoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrinePass(), PassConfig::TYPE_OPTIMIZE);

        $container->addCompilerPass(new RegisterCoreListenersPass(), PassConfig::TYPE_AFTER_REMOVING);

        // see https://github.com/symfony/symfony/issues/11909
        $container->addCompilerPass(new FixSymfonyValidatorDefinitionPass());

        // todo - see if we can do this only on module install/upgrade - drak
        $container->addCompilerPass(new ValidateServiceDefinitionsPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
