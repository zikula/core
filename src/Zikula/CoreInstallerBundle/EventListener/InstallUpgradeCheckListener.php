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

namespace Zikula\Bundle\CoreInstallerBundle\EventListener;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\RoutesModule\Helper\MultilingualRoutingHelper;

class InstallUpgradeCheckListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $installed;

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var MultilingualRoutingHelper
     */
    private $multiLingualRoutingHelper;

    public function __construct(
        string $installed,
        string $currentVersion,
        RouterInterface $router,
        MultilingualRoutingHelper $multiLingualRoutingHelper
    ) {
        $this->installed = $installed;
        $this->currentVersion = $currentVersion;
        $this->router = $router;
        $this->multiLingualRoutingHelper = $multiLingualRoutingHelper;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        /** @var Request $request */
        $request = $event->getRequest();
        // create several booleans to test condition of request regarding install/upgrade
        $requiresUpgrade = false;
        if ($this->installed) {
            $requiresUpgrade = $this->installed && version_compare($this->currentVersion, ZikulaKernel::VERSION, '<');
        }

        try {
            $routeInfo = $this->router->match($request->getPathInfo());
        } catch (Exception $exception) {
            return;
        }
        $containsInstall = 'install' === $routeInfo['_route'];
        $containsUpgrade = 'upgrade' === $routeInfo['_route'];
        $containsLogin = 'Zikula\\UsersModule\\Controller\\AccessController::loginAction' === $routeInfo['_controller'];
        $containsDoc = 'doc' === $routeInfo['_route'];
        $containsWdt =  '_wdt' === $routeInfo['_route'];
        $containsProfiler = false !== mb_strpos($routeInfo['_route'], '_profiler');
        $containsRouter = 'fos_js_routing_js' === $routeInfo['_route'];
        $doNotRedirect = $containsProfiler || $containsWdt || $containsRouter || $request->isXmlHttpRequest();

        // check if Zikula Core is not installed
        if (!$this->installed && !$containsDoc && !$containsInstall && !$doNotRedirect) {
            $this->router->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->router->generate('install');
            $this->multiLingualRoutingHelper->reloadMultilingualRoutingSettings();
            $event->setResponse(new RedirectResponse($url));
        }
        // check if Zikula Core requires upgrade
        if ($requiresUpgrade && !$containsLogin && !$containsDoc && !$containsUpgrade && !$doNotRedirect) {
            $this->router->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->router->generate('upgrade');
            $this->multiLingualRoutingHelper->reloadMultilingualRoutingSettings();
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => [
                ['onKernelRequest', 200]
            ],
        ];
    }
}
