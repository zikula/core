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

namespace Zikula\ExtensionsModule\Installer;

use Zikula\ExtensionsModule\AbstractExtension;

/**
 * Interface ExtensionInstallerInterface
 */
interface ExtensionInstallerInterface extends InstallerInterface
{
    public function setExtension(AbstractExtension $extension): void;
}
