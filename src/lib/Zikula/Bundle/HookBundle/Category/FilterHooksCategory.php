<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Category;

class FilterHooksCategory implements CategoryInterface
{
    const NAME = 'filter_hooks';

    /**
     * dispatches \Zikula\Bundle\HookBundle\Hook\FilterHook
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
