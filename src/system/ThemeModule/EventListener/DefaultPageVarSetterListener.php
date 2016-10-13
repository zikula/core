<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\SettingsModule\Api\LocaleApi;
use Zikula\ThemeModule\Engine\ParameterBag;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class DefaultPageVarSetterListener
 *
 * This class sets default pagevars that are available in all Twig templates in a global scope.
 */
class DefaultPageVarSetterListener implements EventSubscriberInterface
{
    /**
     * @var ParameterBag
     */
    private $pageVars;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LocaleApi
     */
    private $localeApi;

    /**
     * @var bool
     */
    private $isInstalled;

    public function __construct(
        ParameterBag $pageVars,
        RouterInterface $routerInterface,
        VariableApi $variableApi,
        KernelInterface $kernel,
        LocaleApi $localeApi,
        $isInstalled
    ) {
        $this->pageVars = $pageVars;
        $this->router = $routerInterface;
        $this->variableApi = $variableApi;
        $this->kernel = $kernel;
        $this->localeApi = $localeApi;
        $this->isInstalled = $isInstalled;
    }

    /**
     * Add default pagevar settings to every page
     *
     * @param GetResponseEvent $event
     */
    public function setDefaultPageVars(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->isInstalled) {
            return;
        }

        // set some defaults
        $this->pageVars->set('lang', $event->getRequest()->getLocale()); // @deprecated use app.request.locale
        $this->pageVars->set('langdirection', $this->localeApi->language_direction()); // @deprecated use localeApi.language_direction
        $this->pageVars->set('title', $this->variableApi->getSystemVar('defaultpagetitle'));
        $this->pageVars->set('meta.charset', $this->kernel->getCharset());
        $this->pageVars->set('meta.description', $this->variableApi->getSystemVar('defaultmetadescription'));
        $this->pageVars->set('meta.keywords', $this->variableApi->getSystemVar('metakeywords'));
        $this->pageVars->set('homepath', $this->router->generate('home'));
        $this->pageVars->set('coredata', ['version' => \Zikula_Core::VERSION_NUM]); // @todo
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setDefaultPageVars', 201]
            ]
        ];
    }
}
