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

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\UserPostSuccessLoginEvent;

class UserEventListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            AccessEvents::LOGOUT_SUCCESS => ['clearUsersNamespace'],
            UserPostSuccessLoginEvent::class => ['setLocale'],
            KernelEvents::EXCEPTION => ['clearUsersNamespace']
        ];
    }

    /**
     * Set the locale in the session based on previous user selection after successful login.
     */
    public function setLocale(UserPostSuccessLoginEvent $event): void
    {
        /** @var UserEntity $userEntity */
        $userEntity = $event->getUser();
        $locale = $userEntity->getLocale();
        if (empty($locale)) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $url = $event->getRedirectUrl();
        if ('' !== $url) {
            $httpRoot = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
            if (0 === mb_strpos($url, $httpRoot)) {
                $url = str_replace($httpRoot, '', $url);
            }
            try {
                $pathInfo = $this->router->match($url);
                if ($pathInfo['_route']) {
                    $event->setRedirectUrl($this->router->generate($pathInfo['_route'], ['_locale' => $locale]));
                }
            } catch (\Exception $exception) {
                // ignore
            }
        }
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set('_locale', $locale);
        }
    }

    /**
     * Clears the session variable namespace used by the Users module.
     * Triggered by the 'user.logout.succeeded' and Kernel::EXCEPTION events.
     * This is to ensure no leakage of authentication information across sessions or between critical
     * errors. This prevents, for example, the login process from becoming confused about its state
     * if it detects session variables containing authentication information which might make it think
     * that a re-attempt is in progress.
     */
    public function clearUsersNamespace(Event $event, string $eventName): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $doClear = false;
        if ($event instanceof ExceptionEvent || KernelEvents::EXCEPTION === $eventName) {
            if (null !== $request) {
                $doClear = $request->attributes->has('_zkModule') && UsersConstant::MODNAME === $request->attributes->get('_zkModule');
            }
        } else {
            // Logout
            $doClear = true;
        }

        if ($doClear && $request->hasSession() && ($session = $request->getSession())) {
            $session->clear();
        }
    }
}
