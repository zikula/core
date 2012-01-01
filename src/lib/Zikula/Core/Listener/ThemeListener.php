<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Listener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;


class ThemeListener implements EventSubscriberInterface
{
    public function renderTheme(FilterResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        $response = $event->getResponse();
        $content = $response->getContent();
        $code = $response->getStatusCode();

        $themedContent = \Zikula_View_Theme::getInstance()->themefooter($content);
        $themeResponse = new Response($themedContent, $code);
        $event->setResponse($themeResponse);
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::RESPONSE => 'renderTheme');
    }
}
