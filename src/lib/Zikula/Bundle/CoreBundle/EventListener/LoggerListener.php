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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Gedmo\Loggable\LoggableListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * Loggable listener to provide the current user name
 */
class LoggerListener
{
    /**
     * @var LoggableListener
     */
    private $loggableListener;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        LoggableListener $loggableListener,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi,
        bool $installed
    ) {
        $this->loggableListener = $loggableListener;
        $this->translator = $translator;
        $this->currentUserApi = $currentUserApi;
        $this->installed = $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    /**
     * Set the username from the current user api.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->installed) {
            return;
        }

        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $userName = $this->currentUserApi->isLoggedIn() ? $this->currentUserApi->get('uname') : $this->translator->__('Guest');

        $this->loggableListener->setUsername($userName);
    }
}
