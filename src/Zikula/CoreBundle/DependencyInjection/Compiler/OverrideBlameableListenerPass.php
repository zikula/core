<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Zikula\Bundle\CoreBundle\EventListener\BlameListener;

class OverrideBlameableListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('stof_doctrine_extensions.event_listener.blame');
        $definition->setClass(BlameListener::class)
            ->setArguments([
                new Reference('stof_doctrine_extensions.listener.blameable'),
                new Reference('doctrine.orm.default_entity_manager'),
                new Reference('session')
            ]);
    }
}
