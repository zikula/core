<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\HookBundle\RepositoryInterface\HookRuntimeRepositoryInterface;

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

    public function __construct(
        ContainerInterface $container,
        HookRuntimeRepositoryInterface $hookRuntimeRepository,
        string $installed
    ) {
        $this->container = $container;
        $this->hookRuntimeRepository = $hookRuntimeRepository;
        $this->installed = '0.0.0' !== $installed;
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
     */
    public function addListeners(RequestEvent $event): void
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
            $this->container->get('event_dispatcher')->addListener($handler['eventname'], [$callSubject, $handler['method']]);
        }
    }
}
