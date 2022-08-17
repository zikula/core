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
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Zikula\ThemeModule\Engine\AssetBag;

class AddWebPackAssetsListener implements EventSubscriberInterface
{
    private bool $installed;

    public function __construct(
        private readonly AssetBag $jsAssetBag,
        private readonly AssetBag $cssAssetBag,
        private readonly EntrypointLookupCollectionInterface $lookupCollection,
        string $installed,
        private readonly string $projectDir,
        private readonly string $entryPoint = '_default',
        private readonly string $entryName = 'app'
    ) {
        $this->installed = '0.0.0' !== $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['addWebPackAssets', 1020]
            ]
        ];
    }

    public function addWebPackAssets(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }
        if (!file_exists($this->projectDir . '/public/build/manifest.json')) {
            return;
        }

        $webPackCssFiles = $this->lookupCollection->getEntrypointLookup($this->entryPoint)
            ->getCssFiles($this->entryName);
        $cssFiles = array_flip($webPackCssFiles);
        array_walk($cssFiles, function (&$weight) { $weight += AssetBag::WEIGHT_WEBPACK_OFFSET; });
        $this->cssAssetBag->add($cssFiles);

        $webPackJsFiles = $this->lookupCollection->getEntrypointLookup($this->entryPoint)
            ->getJavaScriptFiles($this->entryName);
        $jsFiles = array_flip($webPackJsFiles);
        array_walk($jsFiles, function (&$weight) { $weight += AssetBag::WEIGHT_WEBPACK_OFFSET; });
        $this->jsAssetBag->add($jsFiles);
    }
}
