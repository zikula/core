<?php

namespace Zikula\BlocksModule\Tests\Api\Fixture;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\BlockControllerInterface;

class FooBlock implements BlockControllerInterface
{
    public function getType()
    {
        return "FooType";
    }

    public function display($content)
    {
        // TODO: Implement display() method.
    }

    public function modify(Request $request, $content)
    {
        // TODO: Implement modify() method.
    }

}