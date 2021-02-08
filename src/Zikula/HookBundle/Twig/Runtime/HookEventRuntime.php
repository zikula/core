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

namespace Zikula\Bundle\HookBundle\Twig\Runtime;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;

class HookEventRuntime implements RuntimeExtensionInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        RequestStack $requestStack,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return bool|string|array
     */
//    public function notifyDisplayHooks(string $eventName, int $id = null, UrlInterface $urlObject = null, bool $outputAsArray = false)
//    {
//        if (empty($eventName)) {
//            return trigger_error('Error! "eventname" must be set in notifydisplayhooks');
//        }
//        if ($urlObject && !($urlObject instanceof UrlInterface)) {
//            return trigger_error('Error! "urlobject" must be an instance of Zikula\Bundle\CoreBundle\UrlInterface');
//        }
//
//        // create event and notify
//        $hook = new DisplayHook($id, $urlObject);
//        $this->hookDispatcher->dispatch($eventName, $hook);
//        $responses = $hook->getResponses();
//
//        if ($outputAsArray) {
//            return $responses;
//        }
//
//        $output = '';
//        foreach ($responses as $result) {
//            if (null === $result) {
//                continue;
//            }
//            $output .= '<div class="z-displayhook">' . $result . '</div>' . "\n";
//        }
//
//        return $output;
//    }

//    public function createRouteUrl(string $routeName, array $routeParameters = [], string $fragment = null): UrlInterface
//    {
//        $url = new RouteUrl($routeName, $routeParameters, $fragment);
//
//        if (!isset($routeParameters['_locale']) && null !== $this->requestStack->getCurrentRequest()) {
//            $url->setLanguage($this->requestStack->getCurrentRequest()->getLocale());
//        }
//
//        return $url;
//    }

    public function dispatchFilterHookEvent(string $content, string $filterEventName): string
    {
        $a =\class_exists($filterEventName);
        $b = \is_subclass_of($filterEventName, FilterHookEvent::class);
        if (\class_exists($filterEventName) && \is_subclass_of($filterEventName, FilterHookEvent::class)) {
            $hook = $this->eventDispatcher->dispatch(new $filterEventName($content));

            return $hook->getData();
        }

        return $content;
    }
}
