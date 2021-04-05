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
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\HookBundle\Event\HookPostChangeEvent;
use Zikula\Bundle\HookBundle\Event\ConnectionChangeEvent;

/**
 * Clear the combined asset cache when a hook state is changed.
 */
class HookChangeListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var bool
     */
    private $mergerActive;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        CacheClearer $cacheClearer,
        bool $mergerActive
    ) {
        $this->kernel = $kernel;
        $this->cacheClearer = $cacheClearer;
        $this->mergerActive = $mergerActive;
    }

    public static function getSubscribedEvents()
    {
        return [
            HookPostChangeEvent::class => ['clearCombinedAssetCache'], // @deprecated remove at Core 4.0.0
            ConnectionChangeEvent::class => ['clearCombinedAssetCache']
        ];
    }

    public function clearCombinedAssetCache(): void
    {
        if ('prod' === $this->kernel->getEnvironment() && $this->mergerActive) {
            $this->cacheClearer->clear('assets');
        }
    }
}
