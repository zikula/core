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

namespace Zikula\BlocksModule\Tests\Api\Fixture;

use Zikula\BlocksModule\BlockHandlerInterface;

class FooBlock implements BlockHandlerInterface
{
    public function getType(): string
    {
        return 'FooType';
    }

    public function display(array $properties): string
    {
        return '';
    }

    public function getFormClassName(): string
    {
        return '';
    }

    public function getFormOptions(): array
    {
        return [];
    }

    public function getFormTemplate(): string
    {
        return '';
    }

    public function getPropertyDefaults(): array
    {
        return [];
    }
}
