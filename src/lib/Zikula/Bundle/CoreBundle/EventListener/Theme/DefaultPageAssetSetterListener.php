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

namespace Zikula\Bundle\CoreBundle\EventListener\Theme;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Theme\AssetBag;

class DefaultPageAssetSetterListener implements EventSubscriberInterface
{
    private $cssAssetBag;
    private $jsAssetBag;
    private $router;
    private $params;

    public function __construct(AssetBag $jsAssetBag, AssetBag $cssAssetBag, RouterInterface $router)
    {
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->router = $router;
    }

    public function setParameters(ContainerInterface $container)
    {
        $this->params = [
            'env' => $container->getParameter('env'),
            'zikula.javascript.bootstrap.min.path' => $container->getParameter('zikula.javascript.bootstrap.min.path'),
            'zikula.stylesheet.bootstrap-font-awesome.path' => $container->getParameter('zikula.stylesheet.bootstrap-font-awesome.path'),
            'zikula.stylesheet.fontawesome.min.path' => $container->getParameter('zikula.stylesheet.fontawesome.min.path'),
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
        $basePath = $event->getRequest()->getBasePath();

        // add default javascripts to jsAssetBag
        $this->addJquery($basePath);
        $this->jsAssetBag->add(
            [
                $basePath . '/' . $this->params['zikula.javascript.bootstrap.min.path'] => 1,
                $basePath . '/javascript/helpers/bootstrap-zikula.js' => 2,
                $basePath . '/web/html5shiv/dist/html5shiv.js' => 3,
//            $basePath . '/javascript/helpers/Zikula.js', // @todo legacy remove at Core 2.0
            ]
        );
        $this->addFosJsRouting($basePath);

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
        return array(
            KernelEvents::REQUEST => array(
                array('setDefaultPageAssets', 201),
            ),
        );
    }

    private function addJquery($basePath)
    {
        $jquery = $this->params['env'] != 'dev' ? 'jquery.min.js' : 'jquery.js';
        $this->jsAssetBag->add([$basePath . "/web/jquery/$jquery" => 0]);
    }

    private function addFosJsRouting($basePath)
    {
        if ($this->params['env'] != 'dev' && file_exists(realpath('web/js/fos_js_routes.js'))) {
            $this->jsAssetBag->add([
                $basePath . '/web/bundles/fosjsrouting/js/router.js' => 4,
                $basePath . '/web/js/fos_js_routes.js' => 5
            ]);
        } else {
            $routeScript = $this->router->generate('fos_js_routing_js', array('callback' => 'fos.Router.setData'));
            $this->jsAssetBag->add([
                $basePath . '/web/bundles/fosjsrouting/js/router.js' => 4,
                $routeScript => 5
            ]);
        }
    }

    private function addBootstrapCss($basePath)
    {
        $overrideBootstrapPath = \ThemeUtil::getVar('bootstrapPath', ''); // allows for theme override of bootstrap css path
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
