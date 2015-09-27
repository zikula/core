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
use Zikula\Core\Theme\AssetBag;

class DefaultPageAssetSetterListener implements EventSubscriberInterface
{
    private $cssAssetBag;
    private $jsAssetBag;
    private $params;

    public function __construct(AssetBag $jsAssetBag, AssetBag $cssAssetBag)
    {
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
    }

    public function setParameters(ContainerInterface $container)
    {
        $this->params = [
            'env' => $container->getParameter('env'),
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
        $jquery = $this->params['env'] != 'dev' ? 'jquery.min.js' : 'jquery.js';

        // add default javascripts to jsAssetBag
        $this->jsAssetBag->add(
            [
                $basePath . "/web/jquery/$jquery" => 0,
                $basePath . '/web/bootstrap/js/bootstrap.min.js' => 1,
                $basePath . '/javascript/helpers/bootstrap-zikula.js' => 2,
                $basePath . '/web/html5shiv/dist/html5shiv.js' => 3,
//            $basePath . '/javascript/helpers/Zikula.js', // @todo legacy remove at Core 2.0
        // the following currently added by JCSSUtil in Engine::filter()
//            $basePath . '/web/bundles/fosjsrouting/js/router.js',
//            $basePath . '/web/js/fos_js_routes.js',
            ]
        );

        // add default stylesheets to cssAssetBag
        $this->cssAssetBag->add(
            [
                $basePath . '/web/bootstrap-font-awesome.css' => 0,
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
}
