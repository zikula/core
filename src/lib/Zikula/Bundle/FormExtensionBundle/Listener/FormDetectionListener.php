<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\ParameterBag;

/**
 * Event handler class which checks the response for any forms.
 */
class FormDetectionListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var AssetBag
     */
    private $jsAssetBag;

    /**
     * @var ParameterBag
     */
    private $pageVars;

    /**
     * FormDetectionListener constructor.
     *
     * @param RequestStack $requestStack
     * @param AssetBag     $jsAssetBag
     * @param ParameterBag $pageVars
     */
    public function __construct(RequestStack $requestStack, AssetBag $jsAssetBag, ParameterBag $pageVars)
    {
        $this->requestStack = $requestStack;
        $this->jsAssetBag = $jsAssetBag;
        $this->pageVars = $pageVars;
    }

    /**
     * Makes our handlers known to the event system.
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 5]
        ];
    }

    /**
     * Listener for the `kernel.response` event.
     *
     * @param FilterResponseEvent $event The event instance
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $content = $response->getContent();
        if (false === strpos($content, '<form')) {
            return;
        }

        // a form has been detected, add default polyfills
        $features = ['forms', 'forms-ext'];

        $basePath = $this->requestStack->getCurrentRequest()->getBasePath();
        $this->jsAssetBag->add([$basePath . '/web/webshim/js-webshim/minified/polyfiller.js' => AssetBag::WEIGHT_JQUERY + 1]);
        $this->jsAssetBag->add([$basePath . '/javascript/polyfiller.init.js' => AssetBag::WEIGHT_JQUERY + 2]);

        $existingFeatures = $this->pageVars->get('polyfill_features', []);
        $features = array_unique(array_merge($existingFeatures, $features));
        $this->pageVars->set('polyfill_features', $features);
    }
}
