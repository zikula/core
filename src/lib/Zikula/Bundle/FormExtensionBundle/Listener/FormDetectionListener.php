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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\ParameterBag;

/**
 * Event handler class which checks the response for any forms.
 */
class FormDetectionListener implements EventSubscriberInterface
{
    /**
     * @var Asset
     */
    private $assetHelper;

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
     * @param Asset $assetHelper
     * @param AssetBag     $jsAssetBag
     * @param ParameterBag $pageVars
     */
    public function __construct(Asset $assetHelper, AssetBag $jsAssetBag, ParameterBag $pageVars)
    {
        $this->assetHelper = $assetHelper;
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
        if ($response instanceof BinaryFileResponse || $response instanceof JsonResponse || $response instanceof StreamedResponse) {
            return;
        }
        $content = $response->getContent();
        if (false === strpos($content, '<form')) {
            return;
        }

        // a form has been detected, add default polyfills
        $features = ['forms', 'forms-ext'];

        $this->jsAssetBag->add([$this->assetHelper->resolve('webshim/js-webshim/minified/polyfiller.js') => AssetBag::WEIGHT_JQUERY + 1]);
        $this->jsAssetBag->add([$this->assetHelper->resolve('bundles/core/js/polyfiller.init.js') => AssetBag::WEIGHT_JQUERY + 2]);

        $existingFeatures = $this->pageVars->get('polyfill_features', []);
        $features = array_unique(array_merge($existingFeatures, $features));
        $this->pageVars->set('polyfill_features', $features);
    }
}
