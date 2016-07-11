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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeModule\Engine\Engine;
use Zikula_View_Theme;

/**
 * Class CreateThemedResponseListener
 *
 * This class intercepts the Response and modifies it to return a themed Response.
 * It is currently fully BC with Core-1.3 in order to return a smarty-based themed response.
 */
class CreateThemedResponseListener implements EventSubscriberInterface
{
    private $themeEngine;

    public function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
    }

    public function createThemedResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        $response = $event->getResponse();
        $format = $event->getRequest()->getRequestFormat();
        $route = $event->getRequest()->attributes->has('_route') ? $event->getRequest()->attributes->get('_route') : '0'; // default must not be '_'
        if (!($response instanceof Response)
            || is_subclass_of($response, '\Symfony\Component\HttpFoundation\Response')
            || $event->getRequest()->isXmlHttpRequest()
            || $format != 'html'
            || false === strpos($response->headers->get('Content-Type'), 'text/html')
            || $route[0] === '_' // the profiler and other symfony routes begin with '_' @todo this is still too permissive
        ) {
            return;
        }

        // all responses are assumed to be themed. PlainResponse will have already returned.
        $twigThemedResponse = $this->themeEngine->wrapResponseInTheme($response);
        if ($twigThemedResponse) {
            $event->setResponse($twigThemedResponse);
        } else {
            // theme is not a twig based theme, revert to smarty
            $smartyThemedResponse = Zikula_View_Theme::getInstance()->themefooter($response);
            $event->setResponse($smartyThemedResponse);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['createThemedResponse']
            ]
        ];
    }
}
