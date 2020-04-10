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

namespace Zikula\Bundle\FormExtensionBundle\Tests;

use ArrayAccess;
use Iterator;
use PHPUnit\Framework\TestCase;
use Traversable;
use Zikula\Bundle\FormExtensionBundle\FormTypesChoices;

class FormTypesChoicesTest extends TestCase
{
    public function testEmptyInstantiation(): void
    {
        $foo = new FormTypesChoices();
        $this->assertInstanceOf(ArrayAccess::class, $foo);
        $this->assertInstanceOf(Iterator::class, $foo);
        $this->assertInstanceOf(Traversable::class, $foo);
    }

    public function testInstantiationWithArg(): void
    {
        $foo = new FormTypesChoices([
            'foo' => 'bar',
            'three' => 'nine',
            1 => 17
        ]);
        $this->assertInstanceOf(ArrayAccess::class, $foo);
        $this->assertInstanceOf(Iterator::class, $foo);
        $this->assertInstanceOf(Traversable::class, $foo);
        $this->assertArrayHasKey('foo', $foo);
        $this->assertArrayHasKey('three', $foo);
        $this->assertArrayHasKey(1, $foo);
    }

    public function testAdd(): void
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

    public function testExceptionOnUnset(): void
    {
        $this->expectException(\Exception::class);
        $foo = new FormTypesChoices([
            'foo' => 'bar',
            'three' => 'nine',
            1 => 17
        ]);
        unset($foo['foo']);
    }
}
