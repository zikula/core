<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

/**
 * Interface ExtensionInstallerInterface
 */
interface ExtensionInstallerInterface extends InstallerInterface
{
    public function setBundle(AbstractBundle $bundle);
}
