<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterCoreListenersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('event_dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('event_dispatcher');

        foreach ($container->findTaggedServiceIds('zikula.event_listener') as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;

                if (!isset($event['event'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "event" attribute on "zikula.event_listener" tags.', $id));
                }

                if (!isset($event['method'])) {
                    $event['method'] = 'on'.preg_replace([
                        '/(?<=\b)[a-z]/ie',
                        '/[^a-z0-9]/i'
                    ], ['strtoupper("\\0")', ''], $event['event']);
                }

                $definition->addMethodCall('addListenerService', [$event['event'], [$id, $event['method']], $priority]);
            }
        }

        foreach ($container->findTaggedServiceIds('zikula.event_subscriber') as $id => $attributes) {
            // We must assume that the class value has been correcly filled, even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new \ReflectionClass($class);
            $interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
            if (!$refClass->implementsInterface($interface)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            $definition->addMethodCall('addSubscriberService', [$id, $class]);
        }
    }
}
