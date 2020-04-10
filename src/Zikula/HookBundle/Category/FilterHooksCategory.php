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

namespace Zikula\Bundle\HookBundle\Category;

class FilterHooksCategory implements CategoryInterface
{
    public const NAME = 'filter_hooks';

    /**
     * Dispatches FilterHook instances.
     */
    public const TYPE_FILTER = 'filter';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTypes(): array
    {
        return [
            self::TYPE_FILTER
        ];
    }
}
