<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesBundle;

use Zikula\ExtensionsBundle\Installer\AbstractExtensionInstaller;

class RoutesModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        // initialisation successful
        return true;
    }
    
    public function upgrade(string $oldVersion): bool
    {
        return true;
    }
    
    public function uninstall(): bool
    {
        // uninstallation successful
        return true;
    }
}
