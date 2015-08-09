<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Bundle\CoreInstallerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Bundle\CoreInstallerBundle\Util\VersionUtil;
use Zikula\Core\Event\GenericEvent;
use Zikula_Request_Http as Request;

class InstallUpgradeCheckListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onCoreInit(GenericEvent $event)
    {
        /** @var Request $request */
        $request = $this->container->get('request');
        // create several booleans to test condition of request regarding install/upgrade
        $installed = $this->container->getParameter('installed');
        if ($installed) {
            VersionUtil::defineCurrentInstalledCoreVersion($this->container);
        }
        $requiresUpgrade = $installed && version_compare(ZIKULACORE_CURRENT_INSTALLED_VERSION, \Zikula_Core::VERSION_NUM, '<');

        // can't use $request->get('_route') to get any of the following
        // all these routes are hard-coded in xml files
        $uriContainsInstall = strpos($request->getRequestUri(), '/install') !== false;
        $uriContainsUpgrade = strpos($request->getRequestUri(), '/upgrade') !== false;
        $uriContainsDoc = strpos($request->getRequestUri(), '/installdoc') !== false;
        $uriContainsWdt = strpos($request->getRequestUri(), '/_wdt') !== false;
        $uriContainsProfiler = strpos($request->getRequestUri(), '/_profiler') !== false;
        $uriContainsRouter = strpos($request->getRequestUri(), '/js/routing?callback=fos.Router.setData') !== false;
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
        return array(
            'core.init' => array(array('onCoreInit')),
        );
    }
}
