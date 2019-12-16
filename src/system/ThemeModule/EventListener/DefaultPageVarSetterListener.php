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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\ParameterBag;

/**
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
    private $installed;

    public function __construct(
        ParameterBag $pageVars,
        RouterInterface $routerInterface,
        VariableApiInterface $variableApi,
        ZikulaHttpKernelInterface $kernel,
        bool $installed
    ) {
        $this->pageVars = $pageVars;
        $this->router = $routerInterface;
        $this->variableApi = $variableApi;
        $this->kernel = $kernel;
        $this->installed = $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setDefaultPageVars', 1]
            ]
        ];
    }

    /**
     * Add default pagevar settings to every page.
     */
    public function setDefaultPageVars(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        // set some defaults
        $this->pageVars->set('title', $this->variableApi->getSystemVar('defaultpagetitle'));
        $this->pageVars->set('meta.charset', $this->kernel->getCharset());
        $this->pageVars->set('meta.description', $this->variableApi->getSystemVar('defaultmetadescription'));
        $this->pageVars->set('homepath', $this->router->generate('home'));
        $this->pageVars->set('coredata', [
            'version' => ZikulaKernel::VERSION,
            'minimumPhpVersion' => ZikulaKernel::PHP_MINIMUM_VERSION
        ]);
    }
}
