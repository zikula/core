<?php
/**
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
        $this->pageVars->set('lang', \ZLanguage::getLanguageCode());
        $this->pageVars->set('langdirection', \ZLanguage::getDirection());
        $this->pageVars->set('title', $this->variableApi->get(VariableApi::CONFIG, 'defaultpagetitle'));
        $this->pageVars->set('meta.charset', \ZLanguage::getDBCharset());
        $this->pageVars->set('meta.description', $this->variableApi->get(VariableApi::CONFIG, 'defaultmetadescription'));
        $this->pageVars->set('meta.keywords', $this->variableApi->get(VariableApi::CONFIG, 'metakeywords'));
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
