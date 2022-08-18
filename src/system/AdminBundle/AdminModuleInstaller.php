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

namespace Zikula\AdminBundle;

use Zikula\ExtensionsBundle\Installer\AbstractExtensionInstaller;

class AdminModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            // 2.0.0 shipped with Core-1.4.3 through Core-2.0.15
            case '2.9.9':
                // do nothing
        }

        return true;
    }

    public function uninstall(): bool
    {
        return false;
    }
}
