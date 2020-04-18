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

namespace Zikula\BootstrapTheme\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;

class BootstrapAssetSetterListener implements EventSubscriberInterface
{
    /**
     * @var AssetBag
     */
    private $cssAssetBag;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        AssetBag $cssAssetBag,
        Asset $assetHelper,
        VariableApiInterface $variableApi
    ) {
        $this->cssAssetBag = $cssAssetBag;
        $this->assetHelper = $assetHelper;
        $this->variableApi = $variableApi;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['setBootstrap', 300] // after \Zikula\ThemeModule\EventListener\DefaultPageAssetSetterListener
            ]
        ];
    }

    public function setBootstrap(ResponseEvent $event): void
    {
        $themeStyle = $event->getRequest()->hasSession() ? $event->getRequest()->getSession()->get('currentBootstrapStyle', '') : '';
        $themeStyle = $themeStyle ? $themeStyle : $this->variableApi->get('ZikulaBootstrapTheme', 'theme_style', 'cerulean');
        if ('default' === $themeStyle) {
            return;
        }
        $this->cssAssetBag->remove([
            $this->assetHelper->resolve('bootswatch/dist/cerulean/bootstrap.min.css') => 0 // bootstrapPath set in theme.yaml
        ]);
        $bootstrapPath = 'bootswatch/dist/' . $themeStyle . '/bootstrap.min.css';
        $this->cssAssetBag->add([
            $this->assetHelper->resolve($bootstrapPath) => 0
        ]);
    }
}
