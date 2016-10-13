<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\SettingsModule\Api\LocaleApi;

class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LocaleApi
     */
    private $localeApi;

    /**
     * LocaleListener constructor.
     * @param KernelInterface $kernel
     * @param LocaleApi $localeApi
     */
    public function __construct(KernelInterface $kernel, LocaleApi $localeApi)
    {
        $this->kernel = $kernel;
        $this->localeApi = $localeApi;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $locale = $event->getRequest()->getLocale();
        if (!isset($locale)) {
            $locale = $event->getRequest()->getDefaultLocale();
        }
        $this->localeApi->load($locale, $this->kernel->getRootDir());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 202]
            ]
        ];
    }
}
