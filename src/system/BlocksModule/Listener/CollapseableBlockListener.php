<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use System;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class CollapseableBlockListener
 */
class CollapseableBlockListener implements EventSubscriberInterface
{
    private $jsAssetBag;
    private $variableApi;
    private $assetHelper;
    private $installed;
    private $isUpgrading;

    public function __construct(AssetBag $jsAssetBag, VariableApi $variableApi, Asset $assetHelper, $installed, $isUpgrading = false)
    {
        $this->jsAssetBag = $jsAssetBag;
        $this->variableApi = $variableApi;
        $this->assetHelper = $assetHelper;
        $this->installed = $installed;
        $this->isUpgrading = $isUpgrading;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function addCollapseableBehavior(GetResponseEvent $event)
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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['addCollapseableBehavior', 202]
            ]
        ];
    }
}
