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

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Exception;
use InvalidArgumentException;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\HttpKernel\Kernel;
use Zikula\ExtensionsBundle\AbstractExtension;
use Zikula\ExtensionsBundle\AbstractModule;
use Zikula\ExtensionsBundle\AbstractTheme;

// Defines for access levels
define('ACCESS_INVALID', -1);
define('ACCESS_NONE', 0);
define('ACCESS_OVERVIEW', 100);
define('ACCESS_READ', 200);
define('ACCESS_COMMENT', 300);
define('ACCESS_MODERATE', 400);
define('ACCESS_EDIT', 500);
define('ACCESS_ADD', 600);
define('ACCESS_DELETE', 700);
define('ACCESS_ADMIN', 800);

abstract class ZikulaKernel extends Kernel implements ZikulaHttpKernelInterface
{
    public const VERSION = '4.0.0-DEV';

    public const PHP_MINIMUM_VERSION = '7.2.5';

    private array $modules = [];

    private array $themes = [];

    /**
     * @var callable
     */
    private $autoloader;

    public function boot()
    {
        parent::boot();

        foreach ($this->bundles as $name => $bundle) {
            if ($bundle instanceof AbstractModule && !isset($this->modules[$name])) {
                $this->modules[$name] = $bundle;
            } elseif ($bundle instanceof AbstractTheme && !isset($this->themes[$name])) {
                $this->themes[$name] = $bundle;
            }
        }
    }

    public function getModule(string $moduleName): AbstractModule
    {
        if (!isset($this->modules[$moduleName])) {
            throw new InvalidArgumentException(sprintf('Module "%s" does not exist or it is not enabled.', $moduleName));
        }

        return $this->modules[$moduleName];
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function getTheme(string $themeName): AbstractTheme
    {
        if (!isset($this->themes[$themeName])) {
            throw new InvalidArgumentException(sprintf('Theme "%s" does not exist or it is not enabled.', $themeName));
        }

        return $this->themes[$themeName];
    }

    public function getThemes(): array
    {
        return $this->themes;
    }

    public function isBundle(string $name): bool
    {
        try {
            $this->getBundle($name);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
