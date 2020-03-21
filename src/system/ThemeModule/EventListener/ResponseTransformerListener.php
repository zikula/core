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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\ResponseTransformer;

class ResponseTransformerListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
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
        $trimWhitespace = $this->variableApi->get('ZikulaThemeModule', 'trimwhitespace');
        if ($trimWhitespace) {
            $responseTransformer = new ResponseTransformer();
            $responseTransformer->trimWhitespace($response);
        }
        $event->setResponse($response);
    }
}
