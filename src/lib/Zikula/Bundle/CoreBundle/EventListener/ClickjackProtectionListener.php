<?php

/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
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
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets x-origin headers to prevent clickjacking attacks.
 *
 * TODO consider https://github.com/nelmio/NelmioSecurityBundle for a future major release
 */
class ClickjackProtectionListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $xFrameOptions = '';

    public function __construct($xFrameOptions)
    {
        $this->xFrameOptions = $xFrameOptions;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(
                array('onKernelResponse'),
            )
        );
    }

    /**
     * Sets x-origin headers in the response object.
     *
     * @param FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        $response->headers->set('X-Frame-Options', $this->xFrameOptions);
        //$response->headers->set('X-Content-Security-Policy', "frame-ancestors 'self'");
        $response->headers->set('X-XSS-Protection', '1');
    }
}
