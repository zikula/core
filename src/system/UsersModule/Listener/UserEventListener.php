<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Constant as UsersConstant;

class UserEventListener implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    public static function getSubscribedEvents()
    {
        return [
            AccessEvents::LOGOUT_SUCCESS => ['clearUsersNamespace'],
            KernelEvents::EXCEPTION => ['clearUsersNamespace'],
        ];
    }

    public function __construct(SessionInterface $session, RequestStack $requestStack, RouterInterface $router)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     * Clears the session variable namespace used by the Users module.
     * Triggered by the 'user.logout.succeeded' and Kernel::EXCEPTION events.
     * This is to ensure no leakage of authentication information across sessions or between critical
     * errors. This prevents, for example, the login process from becoming confused about its state
     * if it detects session variables containing authentication information which might make it think
     * that a re-attempt is in progress.
     *
     * @param GenericEvent $event The event that triggered this handler
     *
     * @return void
     */
    public function clearUsersNamespace($event, $eventName)
    {
        $doClear = false;
        if ($eventName == KernelEvents::EXCEPTION) {
            $request = $this->requestStack->getCurrentRequest();
            if (!is_null($request)) {
                $doClear = $request->attributes->has('_zkModule') && $request->attributes->get('_zkModule') == UsersConstant::MODNAME;
            }
        } else {
            // Logout
            $doClear = true;
        }

        if ($doClear) {
            $this->session->clear();
        }
    }
}
