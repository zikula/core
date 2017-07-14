<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets x-origin headers to prevent clickjacking attacks.
 *
 * Consider https://github.com/nelmio/NelmioSecurityBundle for a future major release refs #3646
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
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse']
            ]
        ];
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
