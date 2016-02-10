<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
