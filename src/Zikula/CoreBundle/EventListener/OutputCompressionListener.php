<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class OutputCompressionListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(VariableApiInterface $variableApi, $installed)
    {
        $this->variableApi = $variableApi;
        $this->installed = $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 1023]
            ]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        // Check if compression is desired
        if (1 !== $this->variableApi->getSystemVar('UseCompression')) {
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
}
