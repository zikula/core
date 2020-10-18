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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeModule\Engine\ResponseTransformer;

class ResponseTransformerListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $trimWhitespace;

    public function __construct(bool $trimWhitespace = false)
    {
        $this->trimWhitespace = $trimWhitespace;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['transformResponse', -3]
            ]
        ];
    }

    public function transformResponse(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        if ($this->trimWhitespace) {
            $responseTransformer = new ResponseTransformer();
            $responseTransformer->trimWhitespace($response);
        }
        $event->setResponse($response);
    }
}
