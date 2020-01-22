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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\ExtensionsModule\ExtensionEvents;

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

    public function __construct(bool $active, CacheClearer $cacheClearer)
    {
        $this->mergerActive = $active;
        $this->cacheClearer = $cacheClearer;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionEvents::MODULE_INSTALL => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_UPGRADE => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_ENABLE => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_DISABLE => ['clearCombinedAssetCache'],
            ExtensionEvents::MODULE_REMOVE => ['clearCombinedAssetCache']
        ];
    }

    public function clearCombinedAssetCache(): void
    {
        if ($this->mergerActive) {
            $this->cacheClearer->clear('assets');
        }
    }
}
