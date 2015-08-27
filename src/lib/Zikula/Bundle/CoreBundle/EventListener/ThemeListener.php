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
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Annotations\Reader;

class ThemeListener implements EventSubscriberInterface
{
    private $loader;
    private $themeEngine;
    private $annotationReader;
    private $cssAssetBag;
    private $jsAssetBag;
    private $pageVars;

    function __construct(\Twig_Loader_Filesystem $loader, Engine $themeEngine, Reader $annotationReader, AssetBag $jsAssetBag, AssetBag $cssAssetBag, ParameterBag $pageVars)
    {
        $this->loader = $loader;
        $this->themeEngine = $themeEngine;
        $this->annotationReader = $annotationReader;
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
            $smartyThemedResponse = Zikula_View_Theme::getInstance()->themefooter($response);
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

        // add default javascripts to jsAssetBag
        $this->jsAssetBag->add(array(
            $basePath . '/web/jquery/jquery.min.js',
            $basePath . '/web/bootstrap/js/bootstrap.min.js',
            $basePath . '/javascript/helpers/bootstrap-zikula.js',
//            $basePath . '/javascript/helpers/Zikula.js', // @todo legacy remove at Core 2.0
            $basePath . '/web/bundles/fosjsrouting/js/router.js',
            $basePath . '/web/js/fos_js_routes.js',
        ));

        // add default stylesheets to cssAssetBag
        $this->cssAssetBag->add(array(
            $basePath . '/web/bootstrap-font-awesome.css',
            $basePath . '/style/core.css',
        ));
    }

    /**
     * Add default pagevar settings to every page
     * @param GetResponseEvent $event
     */
    public function setDefaultPageVars(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        // set some defaults
        $this->pageVars->set('lang', \ZLanguage::getLanguageCode());
        $this->pageVars->set('title', \System::getVar('defaultpagetitle'));
        $this->pageVars->set('meta.description', \System::getVar('defaultmetadescription'));
        $this->pageVars->set('meta.keywords', \System::getVar('metakeywords'));
    }

    /**
     * Add ThemePath to searchable paths when locating templates using name-spaced scheme
     * @param FilterControllerEvent $event
     * @throws \Twig_Error_Loader
     */
    public function setUpThemePathOverrides(FilterControllerEvent $event)
    {
        // @todo check isMasterRequest() ????
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

    /**
     * Read the controller annotations and change theme if the annotation indicate that need
     * @param FilterControllerEvent $event
     */
    public function readControllerAnnotations(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // prevents calling this for controller usage within a template or elsewhere
            return;
        }
        $controller = $event->getController();
        list($controller, $method) = $controller;
        $this->changeThemeByAnnotation($controller, $method);
    }

    /**
     * Change a theme based on the annotation
     * @param $controller
     * @param $method
     * @return array|bool|string
     */
    public function changeThemeByAnnotation($controller, $method)
    {
        // the controller could be a proxy, e.g. when using the JMSSecuriyExtraBundle or JMSDiExtraBundle
        $className = is_object($controller) ? ClassUtils::getClass($controller) : $controller;
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $adminAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, 'Zikula\Core\Theme\Annotation\Admin');
        if (isset($adminAnnotation)) {
            $this->themeEngine->setAnnotation('admin');
            // method annotations contain `@Admin` so set theme as admintheme
            $adminThemeName = \ModUtil::getVar('ZikulaAdminModule', 'admintheme');
            if ($adminThemeName) {
                $this->themeEngine->setActiveTheme($adminThemeName);

                return $adminThemeName;
            }
        }

        return false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setThemeEngineRequestAttributes', 32),
                array('setDefaultPageAssets', 201),
                array('setDefaultPageVars', 201),
            ),
            KernelEvents::CONTROLLER => array(
                array('readControllerAnnotations'),
                array('setUpThemePathOverrides'),
            ),
            KernelEvents::RESPONSE => array(
                array('onKernelResponse')
            ),
        );
    }
}
