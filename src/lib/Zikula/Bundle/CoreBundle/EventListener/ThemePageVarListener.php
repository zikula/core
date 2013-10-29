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
        $css = $this->getcss();
        $content = str_replace('</head>', "$css\n</head>", $content);

        $js = '';
        $content = str_replace('</body>', "$js\n</body>", $content);

        $response->setContent($content);
    }

    public function getcss()
    {
        $cssjscombine = \ModUtil::getVar('ZikulaThemeModule', 'cssjscombine', false);
        // get list of stylesheets and scripts from JCSSUtil
        $jcss = \JCSSUtil::prepareJCSS($cssjscombine, \ServiceUtil::getManager()->getParameter('kernel.cache_dir'));
        $styles = '';
        foreach ($jcss['stylesheets'] as $css) {
            $styles .= ' <link rel="stylesheet" type="text/css" href="'.$css.'" />'."\n";
        }

        return $styles;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -256)),
        );
    }
}
