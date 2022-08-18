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

namespace Zikula\DefaultThemeBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeBundle\Engine\Asset;
use Zikula\ThemeBundle\Engine\AssetBag;
use Zikula\ThemeBundle\Engine\Engine;

class BootstrapAssetSetterListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetBag $cssAssetBag,
        private readonly Asset $assetHelper,
        private readonly VariableApiInterface $variableApi,
        private readonly Engine $themeEngine
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['setBootstrap', 300] // after \Zikula\ThemeBundle\EventListener\DefaultPageAssetSetterListener
            ]
        ];
    }

    public function setBootstrap(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $theme = $this->themeEngine->getTheme();
        if (empty($theme) || 'ZikulaDefaultTheme' !== $theme->getName()) {
            return;
        }
        $themeStyle = $event->getRequest()->hasSession() ? $event->getRequest()->getSession()->get('currentBootstrapStyle', '') : '';
        $themeStyle = $themeStyle ?: $this->variableApi->get('ZikulaDefaultTheme', 'theme_style', 'default');
        $this->cssAssetBag->remove([
            $this->assetHelper->resolve('bootswatch/dist/cerulean/bootstrap.min.css') => 0 // bootstrapPath set in theme.yaml
        ]);
        if ('default' === $themeStyle) {
            $bootstrapPath = 'bootstrap/css/bootstrap.min.css';
        } else {
            $bootstrapPath = 'bootswatch/dist/' . $themeStyle . '/bootstrap.min.css';
        }
        $this->cssAssetBag->add([
            $this->assetHelper->resolve($bootstrapPath) => 0
        ]);
    }
}
