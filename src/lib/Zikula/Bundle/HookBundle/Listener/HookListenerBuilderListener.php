<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;

class HookListenerBuilderListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(ContainerInterface $container, $installed) {
        $this->container = $container;
        $this->installed = $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['addListeners', 1000]
            ],
        ];
    }

    /**
     * Add dynamically assigned listeners to hookable events at runtime.
     * @param GetResponseEvent $event
     */
    public function addListeners(GetResponseEvent $event)
    {
        if (!$this->installed || !$event->isMasterRequest()) {
            return;
        }

        $doctrineManager = $this->container->get('doctrine')->getManager();
        $handlers = $doctrineManager->createQueryBuilder()->select('hre')
            ->from(HookRuntimeEntity::class, 'hre')
            ->getQuery()
            ->getArrayResult();
        foreach ($handlers as $handler) {
            $callable = [$handler['classname'], $handler['method']];
            if (is_callable($callable)) {
                if (!empty($handler['serviceid'])) {
                    if (!$this->container->has($handler['serviceid']) && $this->container->get('kernel')->isBundle($handler['powner'])) { // @deprecated - in Core-2.0 all services must be pre-registered with the container via DI
                        $this->container->set($handler['serviceid'], new $handler['classname']);
                    }
                    $this->container->get('event_dispatcher')->addListenerService($handler['eventname'], [$handler['serviceid'], $handler['method']]);
                } else {
                    throw new \InvalidArgumentException('Hook definitions must include a valid service ID.'); // add 'that is already registered with the container' at Core-2.0
                }
            }

        }
    }
}
