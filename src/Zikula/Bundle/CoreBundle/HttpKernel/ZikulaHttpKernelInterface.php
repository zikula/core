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

use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\RebootableInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Zikula\Bundle\CoreBundle\AbstractModule;
use Zikula\Bundle\CoreBundle\AbstractTheme;

interface ZikulaHttpKernelInterface extends KernelInterface, TerminableInterface, RebootableInterface
{
    /**
     * Gets named module bundle.
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     */
    public function getModule(string $moduleName): AbstractModule;

    public function getModules(): array;

    /**
     * Gets named theme bundle.
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     */
    public function getTheme(string $themeName): AbstractTheme;

    public function getThemes(): array;

    /**
     * Is this a Bundle?
     */
    public function isBundle(string $name): bool;
}
