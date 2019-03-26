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

namespace Zikula\Bundle\FormExtensionBundle\Tests;

use Zikula\Bundle\FormExtensionBundle\FormTypesChoices;

class FormTypesChoicesTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyInstantiation()
    {
        $foo = new FormTypesChoices();
        $this->assertInstanceOf(\ArrayAccess::class, $foo);
        $this->assertInstanceOf(\Iterator::class, $foo);
        $this->assertInstanceOf(\Traversable::class, $foo);
    }

    public function testInstantiationWithArg()
    {
        $foo = new FormTypesChoices([
            'foo' => 'bar',
            'three' => 'nine',
            1 => 17
        ]);
        $this->assertInstanceOf(\ArrayAccess::class, $foo);
        $this->assertInstanceOf(\Iterator::class, $foo);
        $this->assertInstanceOf(\Traversable::class, $foo);
        $this->assertArrayHasKey('foo', $foo);
        $this->assertArrayHasKey('three', $foo);
        $this->assertArrayHasKey(1, $foo);
    }

    public function testAdd()
    {
        $foo = new FormTypesChoices([
            'foo' => 'bar',
            'three' => 'nine',
            1 => 17
        ]);
        $this->assertArrayNotHasKey(6, $foo);
        $foo[6] = 'sixvalue';
        $this->assertArrayHasKey(6, $foo);
        $this->assertEquals('sixvalue', $foo[6]);
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionOnUnset()
    {
        $foo = new FormTypesChoices([
            'foo' => 'bar',
            'three' => 'nine',
            1 => 17
        ]);
        unset($foo['foo']);
    }
}
