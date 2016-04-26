<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

/**
 * Interface InstallerInterface
 */
interface InstallerInterface
{
    public function install();

    public function upgrade($oldVersion);

    public function uninstall();
}
