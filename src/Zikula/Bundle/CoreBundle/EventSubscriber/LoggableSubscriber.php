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

namespace Zikula\Bundle\CoreBundle\EventSubscriber;

use Gedmo\Loggable\LoggableListener as Loggable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Loggable subscriber to provide the current user. Note we use the ID to avoid storing user names.
 */
class LoggableSubscriber
{
    public function __construct(
        #[Autowire(service: 'stof_doctrine_extensions.listener.loggable')]
        private readonly Loggable $loggableListener,
        private readonly TranslatorInterface $translator,
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
     * Set the username from the current user api.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $this->loggableListener->setUsername($this->security->getUser()?->getId());
    }
}
