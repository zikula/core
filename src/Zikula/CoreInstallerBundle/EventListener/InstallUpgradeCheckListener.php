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

namespace Zikula\Bundle\CoreInstallerBundle\EventListener;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreInstallerBundle\Helper\PreCore3UpgradeHelper;
use Zikula\RoutesModule\Helper\MultilingualRoutingHelper;

class InstallUpgradeCheckListener implements EventSubscriberInterface
{
    /**
     * @var bool
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

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var PreCore3UpgradeHelper
     */
    private $preCoreUpgradeHelper;

    public function __construct(
        string $installed,
        RouterInterface $router,
        MultilingualRoutingHelper $multiLingualRoutingHelper,
        ParameterBagInterface $parameterBag,
        PreCore3UpgradeHelper $preCore3UpgradeHelper
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->currentVersion = $installed;
        $this->router = $router;
        $this->multiLingualRoutingHelper = $multiLingualRoutingHelper;
        $this->parameterBag = $parameterBag;
        $this->preCoreUpgradeHelper = $preCore3UpgradeHelper;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->checkForCore3Upgrade($event)) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        /** @var Request $request */
        $request = $event->getRequest();
        // create several booleans to test condition of request regarding install/upgrade
        $requiresUpgrade = false;
        if ($this->installed && !empty($this->currentVersion)) {
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
        $containsWdt =  '_wdt' === $routeInfo['_route'];
        $containsProfiler = false !== mb_strpos($routeInfo['_route'], '_profiler');
        $containsRouter = 'fos_js_routing_js' === $routeInfo['_route'];
        $doNotRedirect = $containsProfiler || $containsWdt || $containsRouter || $request->isXmlHttpRequest();

        // check if Zikula Core is not installed
        if (!$this->installed && !$containsInstall && !$doNotRedirect) {
            $this->router->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->router->generate('install');
            $this->multiLingualRoutingHelper->reloadMultilingualRoutingSettings();
            $event->setResponse(new RedirectResponse($url));
        }
        // check if Zikula Core requires upgrade
        if ($requiresUpgrade && !$containsLogin && !$containsUpgrade && !$doNotRedirect) {
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
                ['onKernelRequest', 2000]
            ],
        ];
    }

    private function checkForCore3Upgrade(RequestEvent $event): bool
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        if (!file_exists($projectDir . '/config/services_custom.yaml')) {
            throw new FileNotFoundException(sprintf('Could not find file %s', $projectDir . '/config/services_custom.yaml'));
        }
        $coreInstalledVersion = $this->parameterBag->has('core_installed_version') ? $this->parameterBag->get('core_installed_version') : null;
        if ('0.0.0' === $this->currentVersion && isset($coreInstalledVersion) && version_compare($coreInstalledVersion, '3.0.0', '<')) {
            if ($this->preCoreUpgradeHelper->preUpgrade()) {
                $url = $event->getRequest()->getBaseUrl();
                $event->setResponse(new RedirectResponse($url));
                $event->stopPropagation();

                return true;
            }
        }

        return false;
    }
}
