<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;

class HookListenerBuilderListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var HookRuntimeRepositoryInterface
     */
    private $hookRuntimeRepository;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(ContainerInterface $container, HookRuntimeRepositoryInterface $hookRuntimeRepository, $installed)
    {
        $this->container = $container;
        $this->hookRuntimeRepository = $hookRuntimeRepository;
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

        $handlers = $this->hookRuntimeRepository->findAll();
        foreach ($handlers as $handler) {
            if (!$this->container->get('kernel')->isBundle($handler['powner'])) {
                continue;
            }

            $callable = [$handler['classname'], $handler['method']];
            if (!is_callable($callable)) {
                continue;
            }

            $callSubject = $this->container->has($handler['classname']) ? $handler['classname'] : $handler['serviceid'];
            $this->container->get('event_dispatcher')->addListenerService($handler['eventname'], [$callSubject, $handler['method']]);
        }
    }
}
