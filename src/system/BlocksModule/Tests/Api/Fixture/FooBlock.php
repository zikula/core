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

use Zikula\BlocksModule\BlockHandlerInterface;

class FooBlock implements BlockHandlerInterface
{
    public function getType()
    {
        return 'FooType';
    }

    public function display(array $properties)
    {
        // display() method.
    }

    public function getFormClassName()
    {
        // getFormClassName() method.
    }

    public function getFormOptions()
    {
        // getFormOptions() method.
    }

    public function getFormTemplate()
    {
        // getFormTemplate() method.
    }
}
