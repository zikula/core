<?php

namespace Zikula\BlocksModule\Tests\Api\Fixture;

use Zikula\BlocksModule\BlockHandlerInterface;

class FooBlock implements BlockHandlerInterface
{
    public function getType()
    {
        return "FooType";
    }

    public function display(array $properties)
    {
        // TODO: Implement display() method.
    }

    public function getFormClassName()
    {
        // TODO: Implement getFormClassName() method.
    }

    public function getFormOptions()
    {
        // TODO: Implement getFormOptions() method.
    }

    public function getFormTemplate()
    {
        // TODO: Implement getFormTemplate() method.
    }
}
