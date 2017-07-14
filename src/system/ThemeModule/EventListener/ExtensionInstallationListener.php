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
    /**
     * @var bool
     */
    private $mergerActive;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * ExtensionInstallationListener constructor.
     * @param bool $active
     * @param CacheClearer $cacheClearer
     */
    public function __construct($active, CacheClearer $cacheClearer)
    {
        $this->mergerActive = $active;
        $this->cacheClearer = $cacheClearer;
    }

    public function clearCombinedAssetCache()
    {
        if ($this->mergerActive) {
            $this->cacheClearer->clear('assets');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_INSTALL => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_UPGRADE => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_ENABLE => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_DISABLE => ['clearCombinedAssetCache'],
            CoreEvents::MODULE_REMOVE => ['clearCombinedAssetCache'],
        ];
    }
}
