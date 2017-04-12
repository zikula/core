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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

/**
 * Strips the front controller (index.php) from the URI.
 */
class StripFrontControllerListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var bool
     */
    private $installed;

    /**
     * OutputCompressionListener constructor.
     * @param VariableApiInterface $variableApi
     * @param bool $installed
     */
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

    /**
     * Strips the front controller (index.php) from the URI.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }
        if (!$event->getRequest()->isMethod('GET')) {
            // because this issue is purely 'cosmetic', only fix GET requests.
            return;
        }
        $requestUri = $event->getRequest()->getRequestUri();
        $frontController = $this->variableApi->getSystemVar('entrypoint', 'index.php');
        $stripEntryPoint = (bool) $this->variableApi->getSystemVar('shorturlsstripentrypoint', false);
        $containsFrontController = (strpos($requestUri, "$frontController") !== false);

        if ($containsFrontController && $stripEntryPoint) {
            $replacedString = (strpos($requestUri, "$frontController/") !== false) ? "$frontController/" : $frontController;
            $url = str_ireplace($replacedString, "", $requestUri);
            $response = new RedirectResponse($url, 301);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
