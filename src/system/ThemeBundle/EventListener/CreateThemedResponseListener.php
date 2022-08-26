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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeBundle\Engine\Engine;

/**
 * This class intercepts the Response and modifies it to return a themed Response.
 */
class CreateThemedResponseListener implements EventSubscriberInterface
{
    private bool $installed;

    private bool $debug;

    public function __construct(string $installed, string $debug, private readonly Engine $themeEngine)
    {
        $this->installed = '0.0.0' !== $installed;
        $this->debug = !empty($debug);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['createThemedResponse', -2],
        ];
    }

    public function createThemedResponse(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }
        // TODO disabled
        return;

        $response = $event->getResponse();
        $format = $event->getRequest()->getRequestFormat();
        $route = $event->getRequest()->attributes->has('_route') ? $event->getRequest()->attributes->get('_route') : '0'; // default must not be '_'
        if (!($response instanceof Response)
            || 'html' !== $format
            || 0 === mb_strpos($route, '_') // the profiler and other symfony routes begin with '_' @todo this is still too permissive
            || is_subclass_of($response, Response::class)
            || $event->getRequest()->isXmlHttpRequest()
            || false === mb_strpos($response->headers->get('Content-Type'), 'text/html')
            || $this->debug && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
        ) {
            return;
        }

        // all responses are assumed to be themed. PlainResponse will have already returned.
        $twigThemedResponse = $this->themeEngine->wrapResponseInTheme($response);
        $event->setResponse($twigThemedResponse);
    }
}
