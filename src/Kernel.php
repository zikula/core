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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Zikula\Bundle\CoreBundle\Helper\PersistedBundleHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class Kernel extends ZikulaKernel
{
    use MicroKernelTrait;

    /**
     * @var string
     */
    private $databaseUrl;

    public function __construct(string $environment, bool $debug, string $databaseUrl = '')
    {
        parent::__construct($environment, $debug);

        $this->databaseUrl = $databaseUrl;
    }

    public function registerBundles(): iterable
    {
        $bundleHelper = new PersistedBundleHelper($this->databaseUrl);
        $bundles = require $this->getProjectDir() . '/config/bundles.php';
        $bundleHelper->getPersistedBundles($this, $bundles);

        foreach ($bundles as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }
}
