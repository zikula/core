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

namespace Zikula\BlocksModule\Tests\Helper\Fixture;

use Zikula\ExtensionsModule\AbstractModule;

class TestModule extends AbstractModule
{
    public function getNamespace()
    {
        return parent::getNamespace();
    }
}
