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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class CacheHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var CacheClearer
     */
    private $zikulaCacheClearer;

    /**
     * @var CacheClearerInterface
     */
    private $symfonyCacheClearer;

    /**
     * CacheHelper constructor.
     */
    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        CacheClearer $zikulaCacheClearer,
        CacheClearerInterface $symfonyCacheClearer
    ) {
        $this->kernel = $kernel;
        $this->zikulaCacheClearer = $zikulaCacheClearer;
        $this->symfonyCacheClearer = $symfonyCacheClearer;
    }

    public function clearCaches(): bool
    {
        // clear cache with zikula's method
        $this->zikulaCacheClearer->clear('symfony');
        // use full symfony cache_clearer not zikula's to clear entire cache and set for warmup
        // console commands always run in `dev` mode but site should be `prod` mode. clear both for good measure.
        $this->symfonyCacheClearer->clear('dev');
        $this->symfonyCacheClearer->clear('prod');
        if (!in_array($this->kernel->getEnvironment(), ['dev', 'prod'], true)) {
            // this is just in case anyone ever creates a mode that isn't dev|prod
            $this->symfonyCacheClearer->clear($this->env);
        }

        return true;
    }
}
