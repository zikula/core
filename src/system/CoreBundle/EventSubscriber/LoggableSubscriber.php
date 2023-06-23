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

namespace Zikula\CoreBundle\EventSubscriber;

use Gedmo\Loggable\LoggableListener as Loggable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Loggable subscriber to provide the current user. Note we use the ID to avoid storing usernames.
 */
class LoggableSubscriber
{
    public function __construct(
        #[Autowire(service: 'stof_doctrine_extensions.listener.loggable')]
        private readonly Loggable $loggableListener,
        private readonly Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * Set the current users identifier.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->loggableListener->setUsername($this->security->getUser()?->getId());
    }
}
