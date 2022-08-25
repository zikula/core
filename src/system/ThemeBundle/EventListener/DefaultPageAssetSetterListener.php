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
use Zikula\ThemeBundle\Engine\Asset;
use Zikula\ThemeBundle\Engine\AssetBag;
use Zikula\ThemeBundle\Engine\Engine;

/**
 * This class adds default assets (javascripts and stylesheets) to every page, regardless of the selected theme.
 * In some cases, the actual asset is configurable or able to be overridden.
 */
class DefaultPageAssetSetterListener implements EventSubscriberInterface
{
    private array $params;

    public function __construct(
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly AssetBag $jsAssetBag,
        private readonly AssetBag $cssAssetBag,
        private readonly RouterInterface $router,
        private readonly Asset $assetHelper,
        private readonly Engine $themeEngine,
        string $installed,
        string $bootstrapJavascriptPath,
        string $bootstrapStylesheetPath,
        string $fontAwesomePath
    ) {
        $this->params = [
            'installed' => '0.0.0' !== $installed,
            'bootstrap_js_path' => $bootstrapJavascriptPath,
            'bootstrap_css_path' => $bootstrapStylesheetPath,
            'font_awesome_path' => $fontAwesomePath,
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['setDefaultPageAssets', 1028],
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
            $this->assetHelper->resolve($this->params['bootstrap_js_path']) => AssetBag::WEIGHT_BOOTSTRAP_JS,
            $this->assetHelper->resolve('bundles/core/js/bootstrap-zikula.js') => AssetBag::WEIGHT_BOOTSTRAP_ZIKULA,
        ]);
        $this->addFosJsRouting();
        $this->addJsTranslation();

        // add default stylesheets to cssAssetBag
        $this->addBootstrapCss();
        $this->cssAssetBag->add([
            $this->assetHelper->resolve('bundles/core/css/core.css') => 1,
            $this->assetHelper->resolve($this->params['font_awesome_path']) => 1,
        ]);
    }

    private function addJquery(): void
    {
        $jsConfigFile = 'js/ZikulaThemeBundle.JSConfig.js';
        $jsConfigPath = $this->assetHelper->resolve('bundles/zikulatheme/' . $jsConfigFile);
        if ($this->params['installed'] && null !== $this->themeEngine->getTheme()) {
            $jsConfigPath = $this->assetHelper->resolve('@ZikulaThemeBundle:' . $jsConfigFile);
        }

        $this->jsAssetBag->add([
            $this->assetHelper->resolve('jquery/jquery.min.js') => AssetBag::WEIGHT_JQUERY,
            $jsConfigPath => AssetBag::WEIGHT_JQUERY + 1,
        ]);
    }

    private function addFosJsRouting(): void
    {
        if ('prod' === $this->kernel->getEnvironment() && file_exists($this->kernel->getProjectDir() . '/public/js/fos_js_routes.js')) {
            $routeScript = $this->assetHelper->resolve('js/fos_js_routes.js');
        } else {
            $routeScript = $this->router->generate('fos_js_routing_js', ['callback' => 'fos.Router.setData']);
        }
        $this->jsAssetBag->add([
            $this->assetHelper->resolve('bundles/fosjsrouting/js/router.js') => AssetBag::WEIGHT_ROUTER_JS,
            $routeScript => AssetBag::WEIGHT_ROUTES_JS,
        ]);
    }

    private function addJsTranslation(): void
    {
        $this->jsAssetBag->add([
            $this->assetHelper->resolve('bundles/bazingajstranslation/js/translator.min.js') => AssetBag::WEIGHT_JS_TRANSLATOR,
            $this->router->generate('bazinga_jstranslation_js') => AssetBag::WEIGHT_JS_TRANSLATIONS,
        ]);
    }

    private function addBootstrapCss(): void
    {
        $bootstrapPath = $this->params['bootstrap_css_path'];
        if ($this->params['installed'] && null !== $this->themeEngine->getTheme()) {
            $theme = $this->themeEngine->getTheme();
            // Check for override of bootstrap css path
            if (!empty($theme->getConfig()['bootstrapPath'])) {
                $bootstrapPath = $theme->getConfig()['bootstrapPath'];
            }
        }

        $this->cssAssetBag->add([
            $this->assetHelper->resolve($bootstrapPath) => 0,
        ]);
    }
}
