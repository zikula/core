<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula\StaticContentModule package.
 *
 * Copyright Zikula.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\StaticContentModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class StaticContentModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }
}
