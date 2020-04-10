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

namespace Zikula\ThemeModule\Tests\Engine;

use PHPUnit\Framework\TestCase;
use Zikula\ThemeModule\Engine\ParameterBag;

class ParameterBagTest extends TestCase
{
    /**
     * @var ParameterBag
     */
    private $bag;

    protected function setUp(): void
    {
        $this->bag = new ParameterBag(['foo' => 10]);
    }

    /**
     * @covers ParameterBag::clear()
     */
    public function testClear(): void
    {
        $this->bag->clear();
        $this->assertEquals('', $this->bag->get('foo'));
    }

    /**
     * @covers ParameterBag::has()
     */
    public function testHasSimpleValue(): void
    {
        $this->assertTrue($this->bag->has('foo'));
        $this->assertFalse($this->bag->has('bar'));
    }

    /**
     * @covers ParameterBag::get()
     */
    public function testGetSimpleValue(): void
    {
        $this->assertEquals(10, $this->bag->get('foo'));
        $this->assertNotNull($this->bag->get('bar'));
        $this->assertEquals('default', $this->bag->get('bar', 'default'));
        $this->assertEquals('', $this->bag->get('bar'));
        $this->assertNull($this->bag->get('bar', null));
    }

    /**
     * @covers ParameterBag::set()
     * @covers ParameterBag::get()
     */
    public function testSetSimpleValue(): void
    {
        $this->bag->set('bar', 1);
        $this->assertEquals(1, $this->bag->get('bar'));
        $this->bag->set('bar', 13);
        $this->assertEquals(13, $this->bag->get('bar'));
    }

    /**
     * @covers ParameterBag::set()
     * The parameter `foo` is already set as a scalar value in the constructor. Attempting to recast that
     * value as a namespaced parameter should fail. Resetting the entire parameter to an array will
     * work as expected (see `testSetAndGetArrayValue()` below)
     * Warning: Cannot use a scalar value as an array
     */
    public function testExpectedFailureToRecastToNamespace(): void
    {
        $this->expectException(\TypeError::class);
        $this->bag->set('foo.bang', 6);
    }

    /**
     * @covers ParameterBag::set()
     * @covers ParameterBag::get()
     */
    public function testSetAndGetArrayValue(): void
    {
        $foo = ['bar' => 1, 'baz' => 2];
        $this->bag->set('foo', $foo);
        $this->assertEquals($foo, $this->bag->get('foo'));
    }

    /**
     * @covers ParameterBag::set()
     * @covers ParameterBag::get()
     */
    public function testSetAndGetNameSpacedValue(): void
    {
        $this->bag->set('fum.bar', 1);
        $this->bag->set('fum.baz', 2);
        $this->assertEquals(1, $this->bag->get('fum.bar'));
        $this->assertEquals(2, $this->bag->get('fum.baz'));
        $fum = ['bar' => 1, 'baz' => 2];
        $this->assertEquals($fum, $this->bag->get('fum'));
    }

    /**
     * @covers ParameterBag::remove()
     */
    public function testRemove(): void
    {
        $bar = [
            'foo' => 1,
            'bang' => 5,
            'baz' => 13
        ];
        $this->bag->set('bar', $bar);
        $this->assertTrue($this->bag->has('bar.bang'));
        $this->assertEquals(5, $this->bag->remove('bar.bang'));
        $this->assertTrue($this->bag->has('bar'));
        $this->assertFalse($this->bag->has('bar.bang'));
        unset($bar['bang']);
        $this->assertEquals($bar, $this->bag->get('bar'));
    }

    /**
     * @covers ParameterBag::count()
     */
    public function testCount(): void
    {
        $bar = [
            'foo' => 1,
            'bang' => 5,
            'baz' => 13
        ];
        $this->bag->set('bar', $bar);
        $this->assertEquals(2, $this->bag->count()); // contains `['foo' => 10, 'bar' => ['foo'...]]`
    }

    /**
     * @covers ParameterBag::all()
     */
    public function testAll(): void
    {
        $bar = [
            'foo' => 1,
            'bang' => 5,
            'baz' => 13
        ];
        $this->bag->set('bar', $bar);
        $all = ['foo' => 10, 'bar' => $bar];
        $this->assertEquals($all, $this->bag->all());
    }
}
