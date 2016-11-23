<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Core\CoreEvents;

/**
 * Clear the combined asset cache when a module or theme state is changed
 */
class ExtensionInstallationListener implements EventSubscriberInterface
{
    private $cacheClearer;

    public function __construct(CacheClearer $cacheClearer)
    {
        $this->cacheClearer = $cacheClearer;
    }

    public function clearCombinedAssetCache()
    {
        $this->cacheClearer->clear('assets');
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_INSTALL => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_UPGRADE => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_ENABLE => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_DISABLE => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_REMOVE => ['clearCombinedAssetCache'],
            // @todo create theme events for same and add here
        ];
    }
}
