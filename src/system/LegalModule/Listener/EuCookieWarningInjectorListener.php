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

namespace Zikula\LegalModule\Listener;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\ThemeModule\Api\PageAssetApi;
use Zikula\ThemeModule\Engine\Asset;

/**
 * EuCookieWarningInjectorListener injects a warning to the user that cookies are
 * in use in order to comply with EU regulations.
 *
 * The onKernelResponse method must be connected to the kernel.response event.
 *
 * The Warning is only injected on well-formed HTML (with a proper <body> tag).
 * This means that the Warning is never included in sub-requests or ESI requests.
 */
class EuCookieWarningInjectorListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var PageAssetApi
     */
    private $pageAssetApi;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $stylesheetOverride;

    public function __construct(
        RouterInterface $router,
        Asset $assetHelper,
        PageAssetApi $pageAssetApi,
        VariableApiInterface $variableApi,
        string $stylesheetOverride = null
    ) {
        $this->router = $router;
        $this->assetHelper = $assetHelper;
        $this->pageAssetApi = $pageAssetApi;
        $this->enabled = (bool) $variableApi->get('ZikulaLegalModule', 'eucookie', 0);
        $this->stylesheetOverride = $stylesheetOverride;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();

        try {
            $routeInfo = $this->router->match($request->getPathInfo());
        } catch (Exception $exception) {
            return;
        }
        $containsProhibitedRoute = in_array($routeInfo['_route'], ['_wdt', 'bazinga_jstranslation_js', 'fos_js_routing_js', 'zikulasearchmodule_search_opensearch']);
        $containsProhibitedRoute = $containsProhibitedRoute || (false !== mb_strpos($routeInfo['_route'], '_profiler'));

        // do not capture redirects or modify XML HTTP Requests or routing or toolbar requests
        if ($containsProhibitedRoute
            || $request->isXmlHttpRequest()
            || $response->isRedirect()) {
            return;
        }

        // is cookie set?
        if ($request->cookies->has('cb-enabled') && 'accepted' === $request->cookies->get('cb-enabled')) {
            return;
        }

        $this->injectWarning();
    }

    /**
     * Injects the warning into the given Response.
     */
    protected function injectWarning(): void
    {
        // add javascript to bottom of body - jquery is assumed to be present
        $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/jquery.cookiebar/jquery.cookiebar.js');
        $this->pageAssetApi->add('javascript', $path, 100);
        $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/ZikulaLegalModule.Listener.EUCookieConfig.js');
        $this->pageAssetApi->add('javascript', $path, 101);
        // add stylesheet to head
        if (!empty($this->stylesheetOverride) && file_exists($this->stylesheetOverride)) {
            $path = $this->stylesheetOverride;
        } else {
            $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/jquery.cookiebar/jquery.cookiebar.css');
        }
        $this->pageAssetApi->add('stylesheet', $path);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse']
        ];
    }
}
