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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\VariableApi;

class OutputCompressionListener implements EventSubscriberInterface
{
    private $variableApi;

    /**
     * OutputCompressionListener constructor.
     * @param $variableApi
     */
    public function __construct(VariableApi $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        // Check if compression is desired
        if ($this->variableApi->get(VariableApi::CONFIG, 'UseCompression') != 1) {
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
