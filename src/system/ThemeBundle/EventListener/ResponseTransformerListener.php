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
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeBundle\Engine\ResponseTransformer;

class ResponseTransformerListener implements EventSubscriberInterface
{
    public function __construct(private readonly ResponseTransformer $responseTransformer, private readonly bool $trimWhitespace = false)
    {
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

        if (!$this->trimWhitespace) {
            return;
        }

        $response = $event->getResponse();
        $this->responseTransformer->trimWhitespace($response);
        $event->setResponse($response);
    }
}
