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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
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
     * @var Engine
     */
    private $themeEngine;

    /**
     * @var string
     */
    private $rootdir;

    /**
     * @var array
     */
    private $params;

    /**
     * DefaultPageAssetSetterListener constructor.
     * @param AssetBag $jsAssetBag
     * @param AssetBag $cssAssetBag
     * @param RouterInterface $router
     * @param Engine $themeEngine
     * @param string $rootdir
     */
    public function __construct(
        AssetBag $jsAssetBag,
        AssetBag $cssAssetBag,
        RouterInterface $router,
        Engine $themeEngine,
        $rootdir
    ) {
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->router = $router;
        $this->themeEngine = $themeEngine;
        $this->rootdir = $rootdir;
    }

    public function setParameters(ContainerInterface $container)
    {
        $this->params = [
            'env' => $container->getParameter('env'),
            'zikula.javascript.bootstrap.min.path' => $container->getParameter('zikula.javascript.bootstrap.min.path'),
            'zikula.stylesheet.bootstrap-font-awesome.path' => $container->getParameter('zikula.stylesheet.bootstrap-font-awesome.path'),
            'zikula.stylesheet.fontawesome.min.path' => $container->getParameter('zikula.stylesheet.fontawesome.min.path'),
        ];
        $this->params['zikula.stylesheet.bootstrap.min.path'] = $container->hasParameter('zikula.stylesheet.bootstrap.min.path') ? $container->getParameter('zikula.stylesheet.bootstrap.min.path') : '';
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
        $basePath = $event->getRequest()->getBasePath();

        // add default javascripts to jsAssetBag
        $this->addJquery($basePath);
        $this->jsAssetBag->add(
            [
                $basePath . '/' . $this->params['zikula.javascript.bootstrap.min.path'] => AssetBag::WEIGHT_BOOTSTRAP_JS,
                $basePath . '/javascript/helpers/bootstrap-zikula.js' => AssetBag::WEIGHT_BOOTSTRAP_ZIKULA,
                $basePath . '/web/html5shiv/dist/html5shiv.js' => AssetBag::WEIGHT_HTML5SHIV,
            ]
        );
        $this->addFosJsRouting($basePath);
        $this->addJsTranslation($basePath);

        // add default stylesheets to cssAssetBag
        $this->addBootstrapCss($basePath);
        $this->cssAssetBag->add(
            [
                $basePath . '/style/core.css' => 1,
            ]
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setDefaultPageAssets', 201]
            ]
        ];
    }

    private function addJquery($basePath)
    {
        $jquery = $this->params['env'] != 'dev' ? 'jquery.min.js' : 'jquery.js';
        $this->jsAssetBag->add([$basePath . "/web/jquery/$jquery" => AssetBag::WEIGHT_JQUERY]);
    }

    private function addFosJsRouting($basePath)
    {
        if ($this->params['env'] != 'dev' && file_exists(realpath('web/js/fos_js_routes.js'))) {
            $this->jsAssetBag->add([
                $basePath . '/web/bundles/fosjsrouting/js/router.js' => AssetBag::WEIGHT_ROUTER_JS,
                $basePath . '/web/js/fos_js_routes.js' => AssetBag::WEIGHT_ROUTES_JS
            ]);
        } else {
            $routeScript = $this->router->generate('fos_js_routing_js', ['callback' => 'fos.Router.setData']);
            $this->jsAssetBag->add([
                $basePath . '/web/bundles/fosjsrouting/js/router.js' => AssetBag::WEIGHT_ROUTER_JS,
                $routeScript => AssetBag::WEIGHT_ROUTES_JS
            ]);
        }
    }

    private function addJsTranslation($basePath)
    {
        // @todo consider option of dumping the translations to /web
        // @todo add bundle translations? need domain name e.g. zikulapagesmodule
        $jsScript = $this->router->generate('bazinga_jstranslation_js', ['domain' => 'zikula_javascript'], RouterInterface::ABSOLUTE_URL);
        $this->jsAssetBag->add([
            $basePath . "/web/bundles/bazingajstranslation/js/translator.min.js" => AssetBag::WEIGHT_JS_TRANSLATOR,
            $basePath . "/web/bundles/core/js/Zikula.Translator.js" => AssetBag::WEIGHT_ZIKULA_JS_TRANSLATOR,
            $jsScript => AssetBag::WEIGHT_JS_TRANSLATIONS,
        ]);
    }

    private function addBootstrapCss($basePath)
    {
        $overrideBootstrapPath = '';
        if (!\System::isInstalling()) {
            // Check for override of bootstrap css path
            if (!empty($this->params['zikula.stylesheet.bootstrap.min.path'])) {
                $overrideBootstrapPath = $this->params['zikula.stylesheet.bootstrap.min.path'];
            } elseif (null !== $this->themeEngine->getTheme() && !empty($this->themeEngine->getTheme()->getConfig()['bootstrapPath'])) {
                $overrideBootstrapPath = $this->themeEngine->getTheme()->getConfig()['bootstrapPath'];
            }
        }
        $path = realpath($this->rootdir . '/../') . "/$overrideBootstrapPath";
        $overrideBootstrapPath = !empty($overrideBootstrapPath) && is_readable($path) ? $overrideBootstrapPath : '';

        if (empty($overrideBootstrapPath)) {
            $bootstrapFontAwesomePath = $this->params['zikula.stylesheet.bootstrap-font-awesome.path'];
            $this->cssAssetBag->add(["$basePath/$bootstrapFontAwesomePath" => 0]);
        }
        if (!empty($overrideBootstrapPath)) {
            $fontAwesomePath = $this->params['zikula.stylesheet.fontawesome.min.path'];
            $this->cssAssetBag->add([
                "$basePath/$overrideBootstrapPath" => 0,
                "$basePath/$fontAwesomePath" => 1,
            ]);
        }
    }
}
