<?php

namespace Zikula\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class FixDoctrineDbalConnectionDefinitionPass
 *
 * @todo remove when fixed upstream https://github.com/doctrine/DoctrineBundle/pull/219
 */
class FixDoctrineDbalConnectionDefinitionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container
            ->findDefinition('doctrine.dbal.connection')
            ->setClass('Doctrine\DBAL\Connection');
    }
}