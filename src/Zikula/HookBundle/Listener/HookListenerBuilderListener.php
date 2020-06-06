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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Repository\RepositoryInterface\HookRuntimeRepositoryInterface;

class HookListenerBuilderListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var HookCollectorInterface
     */
    private $hookCollector;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var HookRuntimeRepositoryInterface
     */
    private $hookRuntimeRepository;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        HookCollectorInterface $hookCollector,
        EventDispatcherInterface $eventDispatcher,
        HookRuntimeRepositoryInterface $hookRuntimeRepository,
        string $installed
    ) {
        $this->kernel = $kernel;
        $this->hookCollector = $hookCollector;
        $this->eventDispatcher = $eventDispatcher;
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
            if (!$this->kernel->isBundle($handler['powner'])) {
                continue;
            }
            if (!$this->hookCollector->hasProvider($handler['pareaid'])) {
                continue;
            }

            $callable = [$this->hookCollector->getProvider($handler['pareaid']), $handler['method']];
            if (!is_callable($callable)) {
                continue;
            }

            $this->eventDispatcher->addListener($handler['eventname'], $callable);
        }
    }
}
