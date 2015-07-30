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

use Zikula\Core\Theme\Engine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Response\PlainResponse;
use Zikula_View_Theme;

class ThemeListener implements EventSubscriberInterface
{
    private $themeEngine;

    function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
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

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse')),
        );
    }
}
