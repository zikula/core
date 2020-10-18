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

namespace Zikula\ThemeModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class ThemeModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        // 3.4.3 shipped with Core-1.4.3
        // 3.4.4 shipped with Core-2.0.15
        // version number reset to 3.0.0 at Core 3.0.0
        switch ($oldVersion) {
            case '2.9.9':
            case '3.0.99':
                $this->delVars();
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }
}
