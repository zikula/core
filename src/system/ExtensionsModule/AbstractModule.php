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

namespace Zikula\ExtensionsModule;

use Zikula\Bundle\CoreBundle\AbstractBundle;

abstract class AbstractModule extends AbstractBundle
{
    public function getNameType(): string
    {
        return 'Module';
    }
}
