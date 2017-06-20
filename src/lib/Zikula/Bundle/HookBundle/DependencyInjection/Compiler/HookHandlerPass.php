<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;

class HookHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine') || true !== $container->getParameter('installed')) {
            return;
        }
        $doctrine = $container->get('doctrine');
        $dispatcherDefinition = $container->findDefinition('event_dispatcher');

        $handlers = $doctrine->getManager()->createQueryBuilder()->select('t')
                ->from(HookRuntimeEntity::class, 't')
                ->getQuery()
                ->getArrayResult();
        foreach ($handlers as $handler) {
            $callable = [$handler['classname'], $handler['method']];
            if (is_callable($callable)) {
                if ($handler['serviceid']) {
                    $callable = $this->buildService($container, $handler['serviceid'], $handler['classname'], $handler['method']);
                    $dispatcherDefinition->addMethodCall('addListenerService', [$handler['eventname'], $callable, 0]);
                } else {
                    try {
                        $dispatcherDefinition->addMethodCall('addListener', [$handler['eventname'], $callable, 0]);
                    } catch (\InvalidArgumentException $e) {
                        throw new \RuntimeException("Hook event handler could not be attached because %s", $e->getMessage(), 0, $e);
                    }
                }
            }
        }
    }

    /**
     * Build service.
     *
     * Builds event servicehandlers.  If the service does not exist, it creates it
     * and adds it to the DI container.
     *
     * @param ContainerBuilder $container
     * @param string $id
     * @param string $className
     * @param string $method
     *
     * @return array [$id, $method]
     */
    private function buildService(ContainerBuilder $container, $id, $className, $method)
    {
        if (!$container->has($id)) {
            $definition = new Definition($className, [new Reference('event_dispatcher')]);
            $container->setDefinition($id, $definition);
        }

        return [$id, $method];
    }
}
