<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Category;

use Zikula\Bundle\HookBundle\Hook\FilterHook;

class FilterHooksCategory implements CategoryInterface
{
    const NAME = 'filter_hooks';

    /**
     * Dispatches FilterHook instances.
     */
    const TYPE_FILTER = 'filter';

    public function getName()
    {
        return self::NAME;
    }

    public function getTypes()
    {
        return [
            self::TYPE_FILTER,
        ];
    }
}
