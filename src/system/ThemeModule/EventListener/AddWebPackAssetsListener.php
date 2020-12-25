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
use Symfony\Component\Routing\RouterInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\ParameterBag;

class AddWebPackAssetsListener implements EventSubscriberInterface
{
    /* @var AssetBag */
    private $cssAssetBag;

    /* @var AssetBag */
    private $jsAssetBag;

    /* @var EntrypointLookupCollectionInterface */
    private $lookupCollection;

    /* @var string */
    private $entryPoint;

    /* @var string */
    private $entryName;

    /* @var bool */
    private $installed;

    /* @var string */
    private $projectDir;

    public function __construct(
        AssetBag $jsAssetBag,
        AssetBag $cssAssetBag,
        EntrypointLookupCollectionInterface $lookupCollection,
        string $entryPoint = '_default',
        string $entryName = 'app',
        string $installed,
        string $projectDir
    ) {
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->lookupCollection = $lookupCollection;
        $this->entryPoint = $entryPoint;
        $this->entryName = $entryName;
        $this->installed = '0.0.0' !== $installed;
        $this->projectDir = $projectDir;
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
        array_walk($cssFiles, function(&$weight) { $weight += AssetBag::WEIGHT_WEBPACK_OFFSET; });
        $this->cssAssetBag->add($cssFiles);

        $webPackJsFiles = $this->lookupCollection->getEntrypointLookup($this->entryPoint)
            ->getJavaScriptFiles($this->entryName);
        $jsFiles = array_flip($webPackJsFiles);
        array_walk($jsFiles, function(&$weight) { $weight += AssetBag::WEIGHT_WEBPACK_OFFSET; });
        $this->jsAssetBag->add($jsFiles);
    }
}
