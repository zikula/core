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

namespace Zikula\ThemeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeBundle\Engine\ParameterBag;

/**
 * This class sets default pagevars that are available in all Twig templates in a global scope.
 */
class DefaultPageVarSetterListener implements EventSubscriberInterface
{
    private bool $installed;

    public function __construct(
        private readonly SiteDefinitionInterface $site,
        private readonly ParameterBag $pageVars,
        private readonly RouterInterface $router,
        private readonly ZikulaHttpKernelInterface $kernel,
        string $installed
    ) {
        $this->installed = '0.0.0' !== $installed;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['setDefaultPageVars', 1028],
        ];
    }

    /**
     * Add default pagevar settings to every page.
     */
    public function setDefaultPageVars(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        // set some defaults
        $this->pageVars->set('title', $this->site->getPageTitle());
        $this->pageVars->set('meta.charset', $this->kernel->getCharset());
        $this->pageVars->set('meta.description', $this->site->getMetaDescription());
        $this->pageVars->set('homepath', $this->router->generate('home'));
        $this->pageVars->set('coredata', [
            'version' => ZikulaKernel::VERSION,
            'minimumPhpVersion' => ZikulaKernel::PHP_MINIMUM_VERSION
        ]);
    }
}
