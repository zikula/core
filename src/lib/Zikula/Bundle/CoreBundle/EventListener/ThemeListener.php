<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\Theme\AssetBag;
use Zikula\Core\Theme\Engine;
use Zikula\Core\Theme\ParameterBag;
use Zikula_View_Theme;

class ThemeListener implements EventSubscriberInterface
{
    private $loader;
    private $themeEngine;
    private $cssAssetBag;
    private $jsAssetBag;
    private $pageVars;

    function __construct(\Twig_Loader_Filesystem $loader, Engine $themeEngine, AssetBag $jsAssetBag, AssetBag $cssAssetBag, ParameterBag $pageVars)
    {
        $this->loader = $loader;
        $this->themeEngine = $themeEngine;
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->pageVars = $pageVars;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()
            || $response instanceof RedirectResponse) {
            return;
        }
        // this is needed for the profiler?
        if (!isset($response->legacy) && !$request->attributes->get('_legacy', false)) {
            return;
        }

        // @todo in Core-2.0 this can simply return the themedResponse if instanceof ThemedResponse
        // and the above checks can be reduced to only checking for ThemedResponse
        $twigThemedResponse = $this->themeEngine->wrapResponseInTheme($response);
        if ($twigThemedResponse) {
            $event->setResponse($twigThemedResponse);
        } else {
            // theme is not a twig based theme, revert to smarty
            $theme = $this->themeEngine->themeIsOverridden() ? $this->themeEngine->getThemeName() : null;
            $smartyThemedResponse = Zikula_View_Theme::getInstance($theme)->themefooter($response);
            $event->setResponse($smartyThemedResponse);
        }
    }

    /**
     * The ThemeEngine::requestAttributes MUST be updated based on EACH Request and not only the initial Request.
     * @param GetResponseEvent $event
     */
    public function setThemeEngineRequestAttributes(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $this->themeEngine->setRequestAttributes($event->getRequest());
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
        $this->jsAssetBag->add(array(
            $basePath . '/web/jquery/jquery.min.js',
            $basePath . '/web/bootstrap/js/bootstrap.min.js',
            $basePath . '/javascript/helpers/bootstrap-zikula.js',
//            $basePath . '/javascript/helpers/Zikula.js', // @todo legacy remove at Core 2.0
            $basePath . '/web/bundles/fosjsrouting/js/router.js',
            $basePath . '/web/js/fos_js_routes.js',
        ));
        // @todo this is a hack and should be done differently
        // it adds a script to the header that defines `Zikula.Config` which is needed for NoConflict and Ajax
        $header = $this->pageVars->get('header');
        $header[] = \JCSSUtil::getJSConfig();
        $this->pageVars->set('header', $header);
        $this->cssAssetBag->add(array(
            $basePath . '/web/bootstrap-font-awesome.css',
            $basePath . '/style/core.css',
        ));
    }

    /**
     * Add ThemePath to searchable paths when locating templates using name-spaced scheme
     * @param FilterControllerEvent $event
     * @throws \Twig_Error_Loader
     */
    public function setUpThemePathOverrides(FilterControllerEvent $event)
    {
        // add theme path to template locator
        // This 'twig.loader' functions only when @Bundle/template (name-spaced) name-scheme is used
        // if old name-scheme (Bundle:template) or controller annotations (@Template) are used
        // the \Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel::locateResource method is used instead
        $controller = $event->getController()[0];
        if ($controller instanceof AbstractController) {
            $theme = $this->themeEngine->getTheme();
            $bundleName = $controller->getName();
            if ($theme) {
                $overridePath = $theme->getPath() . '/Resources/' . $bundleName . '/views';
                if (is_readable($overridePath)) {
                    $paths = $this->loader->getPaths($bundleName);
                    // inject themeOverridePath before the original path in the array
                    array_splice($paths, count($paths) - 1, 0, array($overridePath));
                    $this->loader->setPaths($paths, $bundleName);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse')),
            KernelEvents::REQUEST => array(
                array('setThemeEngineRequestAttributes'),
                array('setDefaultPageAssets', 201),
            ),
            KernelEvents::CONTROLLER => array(array('setUpThemePathOverrides')),
        );
    }
}
