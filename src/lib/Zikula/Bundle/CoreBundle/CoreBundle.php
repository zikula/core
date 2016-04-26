<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle;

use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrinePass;
use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\RegisterCoreListenersPass;
use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\LinkContainerPass;

class CoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrinePass(), PassConfig::TYPE_OPTIMIZE);

        $container->addCompilerPass(new RegisterCoreListenersPass(), PassConfig::TYPE_AFTER_REMOVING);

        $container->addCompilerPass(new LinkContainerPass());

        // todo - see if we can do this only on module install/upgrade - drak
        $container->addCompilerPass(new ValidateServiceDefinitionsPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
