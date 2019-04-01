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

namespace Zikula\BlocksModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;

class CollapseableBlockListener implements EventSubscriberInterface
{
    /**
     * @var AssetBag
     */
    private $jsAssetBag;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var bool
     */
    private $isUpgrading;

    public function __construct(
        AssetBag $jsAssetBag,
        VariableApiInterface $variableApi,
        Asset $assetHelper,
        bool $installed,
        bool $isUpgrading = false
    ) {
        $this->jsAssetBag = $jsAssetBag;
        $this->variableApi = $variableApi;
        $this->assetHelper = $assetHelper;
        $this->installed = $installed;
        $this->isUpgrading = $isUpgrading;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['addCollapseableBehavior', 202]
            ]
        ];
    }

    public function addCollapseableBehavior(GetResponseEvent $event): void
    {
        if (!$this->installed || $this->isUpgrading) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        if ($this->variableApi->get('ZikulaBlocksModule', 'collapseable')) {
            $this->jsAssetBag->add($this->assetHelper->resolve('@ZikulaBlocksModule:js/Zikula.Blocks.Common.Minimizer.js'));
        }
    }
}
