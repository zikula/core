<?php
/**
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
 * @package Zikula\BlocksModule\Listener
 */
class CollapseableBlockListener implements EventSubscriberInterface
{
    private $jsAssetBag;
    private $variableApi;
    private $assetHelper;

    public function __construct(AssetBag $jsAssetBag, VariableApi $variableApi, Asset $assetHelper)
    {
        $this->jsAssetBag = $jsAssetBag;
        $this->variableApi = $variableApi;
        $this->assetHelper = $assetHelper;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function addCollapseableBehavior(GetResponseEvent $event)
    {
        if ((System::isInstalling()) || (System::isUpgrading())) {
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
        return array(
            KernelEvents::REQUEST => array(
                array('addCollapseableBehavior', 202),
            ),
        );
    }
}
