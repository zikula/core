<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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
    private $xFrameOptions;

    public function __construct(string $xFrameOptions)
    {
        $this->xFrameOptions = $xFrameOptions;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -99]
            ]
        ];
    }

    /**
     * Sets x-origin headers in the response object.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        $response->headers->set('X-Frame-Options', $this->xFrameOptions);
        $response->headers->set('X-XSS-Protection', '1');
    }
}
