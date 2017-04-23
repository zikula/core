<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Tests\Engine;

use Zikula\ThemeModule\Engine\AssetBag;

class AssetBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers AssetBag::count()
     */
    public function testCount()
    {
        $bag = new AssetBag();
        $bag->add('A');
        $bag->add('B');
        $bag->add('C');
        $this->assertEquals(3, $bag->count());
    }

    /**
     * @covers AssetBag::clear()
     */
    public function testClear()
    {
        $bag = new AssetBag();
        $bag->add('A');
        $bag->add('B');
        $bag->clear();
        $this->assertEquals(0, $bag->count());
    }

    /**
     * @covers AssetBag::remove()
     */
    public function testRemove()
    {
        $bag = new AssetBag();
        $bag->add('A');
        $bag->add('B');
        $bag->remove('A');
        $this->assertEquals(1, $bag->count());
    }

    /**
     * @covers AssetBag::add()
     */
    public function testAddWeighted()
    {
        $bag = new AssetBag();
        $bag->add(['A' => 3]);
        $bag->add(['B' => 1]);
        $bag->add(['C' => 2]);
        $this->assertEquals(['B', 'C', 'A'], $bag->all());
    }

    /**
     * @covers AssetBag::add()
     */
    public function testAddSameWeighted()
    {
        $bag = new AssetBag();
        $bag->add(['A' => 3]);
        $bag->add(['B' => 3]);
        $bag->add(['C' => 3]);
        $this->assertNotEquals(['A', 'B', 'C'], $bag->all()); // order cannot be assumed with same weight
    }

    /**
     * @covers AssetBag::getIterator()
     */
    public function testIterator()
    {
        $bag = new AssetBag();
        $bag->add(['A' => 3]);
        $bag->add(['B' => 1]);
        $bag->add(['C' => 2]);
        $e = [1 => 'B', 2 => 'C', 3 => 'A'];
        $i = 1;
        foreach ($bag as $asset => $weight) {
            $this->assertEquals($i, $weight);
            $this->assertEquals($e[$i], $asset);
            $i++;
        }
    }

    /**
     * @covers AssetBag::add()
     */
    public function testAddDuplicates()
    {
        $bag = new AssetBag();
        $bag->add(['A' => 6]);
        $bag->add(['A' => 8]);
        $bag->add(['A' => 3]);
        $bag->add(['B' => 1]);
        $bag->add(['C' => 2]);
        $bag->add(['A' => 5]);
        $bag->add(['A' => 7]);
        $this->assertEquals(3, $bag->count());
    }

    /**
     * @covers AssetBag::add()
     */
    public function testKeepLowestWeightedSubmission()
    {
        $bag = new AssetBag();
        $bag->add(['A' => 6]);
        $bag->add(['C' => 3]);
        $bag->add(['A' => 8]);
        $bag->add(['A' => 3]);
        $bag->add(['B' => 1]);
        $bag->add(['C' => 2]);
        $bag->add(['A' => 5]);
        $bag->add(['A' => 7]);
        $this->assertEquals('A', $bag->all()[2]); // asset listed at lowest weight submitted
    }
}
