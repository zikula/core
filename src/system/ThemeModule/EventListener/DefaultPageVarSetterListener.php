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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\ParameterBag;

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
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var bool
     */
    private $isInstalled;

    /**
     * DefaultPageVarSetterListener constructor.
     * @param ParameterBag $pageVars
     * @param RouterInterface $routerInterface
     * @param VariableApiInterface $variableApi
     * @param ZikulaHttpKernelInterface $kernel
     * @param bool $isInstalled
     */
    public function __construct(
        ParameterBag $pageVars,
        RouterInterface $routerInterface,
        VariableApiInterface $variableApi,
        ZikulaHttpKernelInterface $kernel,
        $isInstalled
    ) {
        $this->pageVars = $pageVars;
        $this->router = $routerInterface;
        $this->variableApi = $variableApi;
        $this->kernel = $kernel;
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
        $this->pageVars->set('title', $this->variableApi->getSystemVar('defaultpagetitle'));
        $this->pageVars->set('meta.charset', $this->kernel->getCharset());
        $this->pageVars->set('meta.description', $this->variableApi->getSystemVar('defaultmetadescription'));
        $this->pageVars->set('homepath', $this->router->generate('home'));
        $this->pageVars->set('coredata', ['version' => ZikulaKernel::VERSION]);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setDefaultPageVars', 1]
            ]
        ];
    }
}
