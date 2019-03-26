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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\Engine;

/**
 * Class DefaultPageAssetSetterListener
 *
 * This class adds default assets (javascripts and stylesheets) to every page, regardless of the selected theme.
 * In some cases, the actual asset is configurable or able to be overridden.
 */
class DefaultPageAssetSetterListener implements EventSubscriberInterface
{
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
     * @var array
     */
    private $params;

    /**
     * DefaultPageAssetSetterListener constructor.
     * @param AssetBag $jsAssetBag
     * @param AssetBag $cssAssetBag
     * @param RouterInterface $router
     * @param Asset $assetHelper
     * @param Engine $themeEngine
     * @param string $env
     * @param bool $installed
     * @param string $bootstrapJavascriptPath
     * @param string $bootstrapFontAwesomeStylesheetPath
     * @param string $fontAwesomePath
     * @param string $bootstrapStylesheetPath
     */
    public function __construct(
        AssetBag $jsAssetBag,
        AssetBag $cssAssetBag,
        RouterInterface $router,
        Asset $assetHelper,
        Engine $themeEngine,
        $env,
        $installed,
        $bootstrapJavascriptPath,
        $bootstrapFontAwesomeStylesheetPath,
        $fontAwesomePath,
        $bootstrapStylesheetPath
    ) {
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->router = $router;
        $this->assetHelper = $assetHelper;
        $this->themeEngine = $themeEngine;
        $this->params = [
            'env' => $env,
            'installed' => $installed,
            'zikula.javascript.bootstrap.min.path' => $bootstrapJavascriptPath,
            'zikula.stylesheet.bootstrap-font-awesome.path' => $bootstrapFontAwesomeStylesheetPath,
            'zikula.stylesheet.fontawesome.min.path' => $fontAwesomePath,
            'zikula.stylesheet.bootstrap.min.path' => $bootstrapStylesheetPath
        ];
    }

    /**
     * Add all default assets to every page (scripts and stylesheets)
     * @param GetResponseEvent $event
     */
    public function setDefaultPageAssets(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // add default javascripts to jsAssetBag
        $this->addJquery();
        $this->jsAssetBag->add(
            [
                $this->assetHelper->resolve($this->params['zikula.javascript.bootstrap.min.path']) => AssetBag::WEIGHT_BOOTSTRAP_JS,
                $this->assetHelper->resolve('bundles/core/js/bootstrap-zikula.js') => AssetBag::WEIGHT_BOOTSTRAP_ZIKULA,
                $this->assetHelper->resolve('html5shiv/dist/html5shiv.js') => AssetBag::WEIGHT_HTML5SHIV,
            ]
        );
        $this->addFosJsRouting();
        $this->addJsTranslation();

        // add default stylesheets to cssAssetBag
        $this->addBootstrapCss();
        $this->cssAssetBag->add(
            [
                $this->assetHelper->resolve('bundles/core/css/core.css') => 1,
            ]
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setDefaultPageAssets', 1]
            ]
        ];
    }

    private function addJquery()
    {
        $jquery = 'dev' !== $this->params['env'] ? 'jquery.min.js' : 'jquery.js';
        $this->jsAssetBag->add(
            [
                $this->assetHelper->resolve("jquery/${jquery}") => AssetBag::WEIGHT_JQUERY,
                $this->assetHelper->resolve('bundles/core/js/jquery_config.js') => AssetBag::WEIGHT_JQUERY + 1
            ]
        );
    }

    private function addFosJsRouting()
    {
        // commented out as a workaround for #3807 until #3804 is solved
        /*if ($this->params['env'] != 'dev' && file_exists(realpath('web/js/fos_js_routes.js'))) {
            $this->jsAssetBag->add([
                $this->assetHelper->resolve('bundles/fosjsrouting/js/router.js') => AssetBag::WEIGHT_ROUTER_JS,
                $this->assetHelper->resolve('js/fos_js_routes.js') => AssetBag::WEIGHT_ROUTES_JS
            ]);
        } else {*/
        $routeScript = $this->router->generate('fos_js_routing_js', ['callback' => 'fos.Router.setData']);
        $this->jsAssetBag->add([
            $this->assetHelper->resolve('bundles/fosjsrouting/js/router.js') => AssetBag::WEIGHT_ROUTER_JS,
            $routeScript => AssetBag::WEIGHT_ROUTES_JS
        ]);
        /*}*/
    }

    private function addJsTranslation()
    {
        // consider option of dumping the translations to /web
        // add bundle translations? need domain name e.g. zikulapagesmodule
        // #3650
        $jsScript = $this->router->generate('bazinga_jstranslation_js', ['domain' => 'zikula_javascript'], RouterInterface::ABSOLUTE_URL);
        $this->jsAssetBag->add([
            $this->assetHelper->resolve('bundles/bazingajstranslation/js/translator.min.js') => AssetBag::WEIGHT_JS_TRANSLATOR,
            $this->assetHelper->resolve('bundles/core/js/Zikula.Translator.js') => AssetBag::WEIGHT_ZIKULA_JS_TRANSLATOR,
            $jsScript => AssetBag::WEIGHT_JS_TRANSLATIONS,
        ]);
    }

    private function addBootstrapCss()
    {
        $overrideBootstrapPath = '';
        if ($this->params['installed']) {
            // Check for override of bootstrap css path
            if (!empty($this->params['zikula.stylesheet.bootstrap.min.path'])) {
                $overrideBootstrapPath = $this->params['zikula.stylesheet.bootstrap.min.path'];
            } elseif (null !== $this->themeEngine->getTheme() && !empty($this->themeEngine->getTheme()->getConfig()['bootstrapPath'])) {
                $overrideBootstrapPath = $this->themeEngine->getTheme()->getConfig()['bootstrapPath'];
            }
        }
        if (empty($overrideBootstrapPath)) {
            $this->cssAssetBag->add([$this->assetHelper->resolve($this->params['zikula.stylesheet.bootstrap-font-awesome.path']) => 0]);
        } else {
            $this->cssAssetBag->add([
                $this->assetHelper->resolve($overrideBootstrapPath) => 0, // throws exception if asset not found
                $this->assetHelper->resolve($this->params['zikula.stylesheet.fontawesome.min.path']) => 1,
            ]);
        }
    }
}
