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
use Symfony\Component\Routing\RouterInterface;
use Zikula\ThemeModule\Engine\ParameterBag;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class DefaultPageVarSetterListener
 *
 * This class sets default pagevars that are available in all Twig templates in a global scope.
 * @todo remove use of legacy Util classes and replace
 */
class DefaultPageVarSetterListener implements EventSubscriberInterface
{
    private $pageVars;

    private $router;

    private $variableApi;

    private $isInstalled;

    public function __construct(ParameterBag $pageVars, RouterInterface $routerInterface, VariableApi $variableApi, $isInstalled)
    {
        $this->pageVars = $pageVars;
        $this->router = $routerInterface;
        $this->variableApi = $variableApi;
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
        $this->pageVars->set('lang', $event->getRequest()->getLocale()); //\ZLanguage::getLanguageCode());
        $this->pageVars->set('langdirection', 'ltr'); //\ZLanguage::getDirection());
        $this->pageVars->set('title', $this->variableApi->getSystemVar('defaultpagetitle_'.\ZLanguage::getLanguageCode()));
        $this->pageVars->set('meta.charset', $this->getCharSet());
        $this->pageVars->set('meta.description', $this->variableApi->getSystemVar('defaultmetadescription_'.\ZLanguage::getLanguageCode()));
        $this->pageVars->set('meta.keywords', $this->variableApi->getSystemVar('metakeywords_'.\ZLanguage::getLanguageCode()));
        $this->pageVars->set('homepath', $this->router->generate('home'));
        $this->pageVars->set('coredata', ['version' => \Zikula_Core::VERSION_NUM]); // @todo
    }

    /**
     * Returns the output charset to be used.
     *
     * @return string The charset
     */
    private function getCharSet()
    {
        $charSet = 'utf-8'; //\ZLanguage::getDBCharset();

        if ($charSet == 'utf8') {
            $charSet = 'utf-8';
        }

        return $charSet;
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
