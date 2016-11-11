<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Bundle\CoreInstallerBundle\Util\VersionUtil;
use Zikula\Core\Event\GenericEvent;

class InstallUpgradeCheckListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onCoreInit(GenericEvent $event)
    {
        /** @var Request $request */
        $request = $this->container->get('request');
        // create several booleans to test condition of request regarding install/upgrade
        $installed = $this->container->getParameter('installed');
        $requiresUpgrade = false;
        if ($installed) {
            VersionUtil::defineCurrentInstalledCoreVersion($this->container);
            $currentVersion = $this->container->getParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM);
            $requiresUpgrade = $installed && version_compare($currentVersion, \Zikula_Core::VERSION_NUM, '<');
        }

        // can't use $request->get('_route') to get any of the following
        // all these routes are hard-coded in xml files
        $requestedUri = $request->getRequestUri();
        $uriContainsInstall = strpos($requestedUri, '/install') !== false;
        $uriContainsUpgrade = strpos($requestedUri, '/upgrade') !== false;
        $uriContainsDoc = strpos($requestedUri, '/installdoc') !== false;
        $uriContainsWdt = strpos($requestedUri, '/_wdt') !== false;
        $uriContainsProfiler = strpos($requestedUri, '/_profiler') !== false;
        $uriContainsRouter = strpos($requestedUri, '/js/routing?callback=fos.Router.setData') !== false;
        $doNotRedirect = $uriContainsProfiler || $uriContainsWdt || $uriContainsRouter || $request->isXmlHttpRequest();

        // check if Zikula Core is not installed
        if (!$installed && !$uriContainsDoc && !$uriContainsInstall && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('install');
            $response = new RedirectResponse($url);
            $response->send();
            \System::shutDown();
        }
        // check if Zikula Core requires upgrade
        if ($requiresUpgrade && !$uriContainsDoc && !$uriContainsUpgrade && !$doNotRedirect) {
            $this->container->get('router')->getContext()->setBaseUrl($request->getBasePath()); // compensate for sub-directory installs
            $url = $this->container->get('router')->generate('upgrade');
            $response = new RedirectResponse($url);
            $response->send();
            \System::shutDown();
        }
        if (!$installed || $requiresUpgrade || $this->container->hasParameter('upgrading')) {
            \System::setInstalling(true);
        }
    }

    public static function getSubscribedEvents()
    {
        // @todo can this be done on kernel.request instead?
        return [
            'core.init' => [
                ['onCoreInit']
            ],
        ];
    }
}
