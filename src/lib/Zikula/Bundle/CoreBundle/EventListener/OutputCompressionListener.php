<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OutputCompressionListener implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        // Check if compression is desired
        if (\System::getVar('UseCompression') != 1) {
            return;
        }

        // Check if zlib extension is available
        if (!extension_loaded('zlib')) {
            return;
        }

        // Set compression on
        ini_set('zlib.output_handler', '');
        ini_set('zlib.output_compression', 'On');
        ini_set('zlib.output_compression_level', 6);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 1023]
            ]
        ];
    }
}
