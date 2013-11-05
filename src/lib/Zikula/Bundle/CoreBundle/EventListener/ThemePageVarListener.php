<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Response\PlainResponse;

class ThemePageVarListener implements EventSubscriberInterface
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()
            || $request->attributes->get('_route') == '_profiler') {
            return;
        }

        // if theme has already been processed the new way, stop here
        if (isset($response->legacy)) {
            return;
        }

        $content = $response->getContent();
        if (false === strpos($content, '<body') && false === strpos($content, '</head>')) {
            return;
        }

        // do replacements
        $css = $this->getCss($request);
        $content = str_replace('</head>', "$css\n</head>", $content);

        $js = $this->getJs($request);
        $content = str_replace('</head>', "$js\n</head>", $content);
//        $content = str_replace('</body>', "$js\n</body>", $content);

        $response->setContent($content);
    }

    public function getCss(Request $request)
    {
        $cssjscombine = \ModUtil::getVar('ZikulaThemeModule', 'cssjscombine', false);
        // get list of stylesheets and scripts from JCSSUtil
        $jcss = \JCSSUtil::prepareJCSS($cssjscombine, \ServiceUtil::getManager()->getParameter('kernel.cache_dir'));
        $styles = '';

        $basePath = $request->getBasePath();
        foreach ($jcss['stylesheets'] as $css) {
            $styles .= ' <link rel="stylesheet" type="text/css" href="'."$basePath/".$css.'" />'."\n";
        }

        return $styles;
    }

    public function getJs(Request $request)
    {
        $cssjscombine = \ModUtil::getVar('ZikulaThemeModule', 'cssjscombine', false);
        // get list of stylesheets and scripts from JCSSUtil
        $jcss = \JCSSUtil::prepareJCSS($cssjscombine, \ServiceUtil::getManager()->getParameter('kernel.cache_dir'));
        $jss = '';
        $basePath = $request->getBasePath();
        foreach ($jcss['javascripts'] as $js) {
            $jss .= ' <script type="text/javascript" href="'."$basePath/".$js.'" />'."</script>\n";
        }

        return $jss;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -256)),
        );
    }
}
