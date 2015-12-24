<?php

namespace Zikula\BlocksModule\Tests\Api\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\BlockHandlerInterface;

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

    public function modify(Request $request, array $properties)
    {
        // TODO: Implement modify() method.
    }
}
