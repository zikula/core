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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\Engine;

/**
 * This class adds default assets (javascripts and stylesheets) to every page, regardless of the selected theme.
 * In some cases, the actual asset is configurable or able to be overridden.
 */
class DefaultPageAssetSetterListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var AssetBag
     */
    private $cssAssetBag;

    /**
     * @var AssetBag
     */
    private $jsAssetBag;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var Engine
     */
    private $themeEngine;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var array
     */
    private $params;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        AssetBag $jsAssetBag,
        AssetBag $cssAssetBag,
        RouterInterface $router,
        Asset $assetHelper,
        Engine $themeEngine,
        VariableApiInterface $variableApi,
        string $installed,
        string $bootstrapJavascriptPath,
        string $bootstrapStylesheetPath,
        string $fontAwesomePath
    ) {
        $this->kernel = $kernel;
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->router = $router;
        $this->assetHelper = $assetHelper;
        $this->themeEngine = $themeEngine;
        $this->variableApi = $variableApi;
        $this->params = [
            'installed' => '0.0.0' !== $installed,
            'zikula.javascript.bootstrap.min.path' => $bootstrapJavascriptPath,
            'zikula.stylesheet.bootstrap.min.path' => $bootstrapStylesheetPath,
            'zikula.stylesheet.fontawesome.min.path' => $fontAwesomePath
        ];
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['setDefaultPageAssets', 1028]
            ]
        ];
    }

    /**
     * Add all default assets to every page (scripts and stylesheets).
     */
    public function setDefaultPageAssets(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // add default javascripts to jsAssetBag
        $this->addJquery();
        $this->jsAssetBag->add([
            $this->assetHelper->resolve($this->params['zikula.javascript.bootstrap.min.path']) => AssetBag::WEIGHT_BOOTSTRAP_JS,
            $this->assetHelper->resolve('bundles/core/js/bootstrap-zikula.js') => AssetBag::WEIGHT_BOOTSTRAP_ZIKULA,
        ]);
        $this->addFosJsRouting($event->getRequest()->getLocale());
        $this->addJsTranslation();

        // add default stylesheets to cssAssetBag
        $this->addBootstrapCss($event->getRequest());
        $this->cssAssetBag->add([
            $this->assetHelper->resolve('bundles/core/css/core.css') => 1,
        ]);
    }

    private function addJquery(): void
    {
        $jquery = 'dev' !== $this->kernel->getEnvironment() ? 'jquery.min.js' : 'jquery.js';
        $this->jsAssetBag->add([
            $this->assetHelper->resolve("jquery/${jquery}") => AssetBag::WEIGHT_JQUERY,
            $this->assetHelper->resolve('modules/zikulatheme/js/ZikulaThemeModule.JSConfig.js') => AssetBag::WEIGHT_JQUERY + 1,
            $this->assetHelper->resolve('bundles/core/js/jquery_config.js') => AssetBag::WEIGHT_JQUERY + 2
        ]);
    }

    private function addFosJsRouting(string $locale): void
    {
        // reenable after https://github.com/FriendsOfSymfony/FOSJsRoutingBundle/issues/221 OR https://github.com/zikula/core/issues/4027 is solved
        //if ('dev' !== $this->kernel->getEnvironment() && file_exists($this->kernel->getProjectDir() . '/public/js/fos_js_routes.' . $locale . '.js')) {
        //    $routeScript = $this->assetHelper->resolve('js/fos_js_routes.' . $locale . '.js');
        //} else {
        $routeScript = $this->router->generate('fos_js_routing_js', ['callback' => 'fos.Router.setData']);
        //}
        $this->jsAssetBag->add([
            $this->assetHelper->resolve('bundles/fosjsrouting/js/router.js') => AssetBag::WEIGHT_ROUTER_JS,
            $routeScript => AssetBag::WEIGHT_ROUTES_JS
        ]);
    }

    private function addJsTranslation(): void
    {
        $this->jsAssetBag->add([
            $this->assetHelper->resolve('bundles/bazingajstranslation/js/translator.min.js') => AssetBag::WEIGHT_JS_TRANSLATOR,
            $this->router->generate('bazinga_jstranslation_js') => AssetBag::WEIGHT_JS_TRANSLATIONS,
        ]);
    }

    private function addBootstrapCss(Request $request): void
    {
        $bootstrapPath = $this->params['zikula.stylesheet.bootstrap.min.path'];
        if ($this->params['installed'] && null !== $this->themeEngine->getTheme()) {
            $theme = $this->themeEngine->getTheme();
            // Check for override of bootstrap css path
            if (!empty($theme->getConfig()['bootstrapPath'])) {
                $bootstrapPath = $theme->getConfig()['bootstrapPath'];
            } elseif ('ZikulaBootstrapTheme' === $theme->getName()) {
                $themeStyle = $request->hasSession() ? $request->getSession()->get('currentBootstrapStyle', '') : '';
                $themeStyle = $themeStyle ? $themeStyle : $this->variableApi->get($theme->getName(), 'theme_style');
                if ('default' !== $themeStyle) {
                    $bootstrapPath = 'bootswatch/dist/' . $themeStyle . '/bootstrap.min.css';
                }
            }
        }

        $this->cssAssetBag->add([
            $this->assetHelper->resolve($bootstrapPath) => 0,
            $this->assetHelper->resolve($this->params['zikula.stylesheet.fontawesome.min.path']) => 1,
        ]);
    }
}
