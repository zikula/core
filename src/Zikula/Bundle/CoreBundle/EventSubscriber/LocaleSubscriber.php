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

use Nucleos\UserBundle\Model\LocaleAwareUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly string $defaultLocale = 'en'
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => ['onKernelRequest', 15],
        ];
    }

    /**
     * @see \Zikula\UsersBundle\EventListener\UserEventSubscriber
     */
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

            // compute default locale considering user preference
            $userSelectedLocale = '';
            $user = $this->security->getUser();
            if ($user instanceof LocaleAwareUser) {
                $userSelectedLocale = $user->getLocale();
            }
            $defaultLocale = $userSelectedLocale ?? $this->defaultLocale;

            $request->setLocale($session->get('_locale', $this->defaultLocale));
        }
    }
}
