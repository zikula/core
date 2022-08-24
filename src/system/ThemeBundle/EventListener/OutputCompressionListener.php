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

namespace Zikula\ThemeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OutputCompressionListener implements EventSubscriberInterface
{
    public function __construct(private readonly bool $useCompression)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1023],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // check if compression is desired
        if (!$this->useCompression) {
            return;
        }

        if (!$event->isMasterRequest()) {
            return;
        }

        // check if Zlib extension is available
        if (!extension_loaded('zlib')) {
            return;
        }

        // set compression on
        ini_set('zlib.output_handler', '');
        ini_set('zlib.output_compression', 'On');
        ini_set('zlib.output_compression_level', 6);
    }
}
