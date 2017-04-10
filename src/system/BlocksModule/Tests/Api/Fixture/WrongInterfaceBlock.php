<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Tests\Api\Fixture;

use Zikula\Core\InstallerInterface;

class WrongInterfaceBlock implements InstallerInterface
{
    public function install()
    {
        // TODO: Implement install() method.
    }

    public function upgrade($oldVersion)
    {
        // TODO: Implement upgrade() method.
    }

    public function uninstall()
    {
        // TODO: Implement uninstall() method.
    }
}
