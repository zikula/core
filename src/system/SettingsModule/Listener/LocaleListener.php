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

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $defaultLocale;

    public function __construct(
        CurrentUserApiInterface $currentUserApi,
        string $defaultLocale = 'en',
        string $installed = '0.0.0'
    ) {
        // compute default locale considering user preference
        $userSelectedLocale = ('0.0.0' !== $installed) ? $currentUserApi->get('locale') : '';
        $this->defaultLocale = !empty($userSelectedLocale) ? $userSelectedLocale : $defaultLocale;
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => [
                ['onKernelRequest', 15]
            ]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }
        $session = $request->getSession();
        if (null === $session || !$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $session->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session or default
            $request->setLocale($session->get('_locale', $this->defaultLocale));
        }
    }
}
