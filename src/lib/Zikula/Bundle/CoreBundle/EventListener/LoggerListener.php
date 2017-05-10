<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Gedmo\Loggable\LoggableListener;
use Stof\DoctrineExtensionsBundle\EventListener\LoggerListener as BaseListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * Loggable listener subclass to provide the current user name
 */
class LoggerListener extends BaseListener
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
     * @var boolean
     */
    private $installed;

    /**
     * @param LoggableListener        $loggableListener
     * @param TranslatorInterface     $translator
     * @param CurrentUserApiInterface $currentUserApi
     * @param boolean                 $installed
     */
    public function __construct(
        LoggableListener $loggableListener,
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi,
        $installed
    ) {
        $this->loggableListener = $loggableListener;
        $this->translator = $translator;
        $this->currentUserApi = $currentUserApi;
        $this->installed = $installed;
    }

    /**
     * Set the username from the current user api by listening on core.request.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
