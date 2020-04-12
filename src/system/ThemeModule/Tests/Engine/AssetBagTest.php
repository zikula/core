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
use Zikula\ThemeModule\Engine\AssetBag;

class AssetBagTest extends TestCase
{
    /**
     * @covers AssetBag::count()
     */
    public function testCount(): void
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
    public function testClear(): void
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
    public function testRemove(): void
    {
        $bag = new AssetBag();
        $bag->add('A');
        $bag->add('B');
        $bag->remove('A');
        $this->assertEquals(1, $bag->count());
        $bag->add(['a' => 0]);
        $bag->add(['b' => 5]);
        $bag->add(['c' => 10]);
        $this->assertEquals(4, $bag->count());
        $bag->remove(['b' => 5]);
        $this->assertEquals(3, $bag->count());
        $bag->remove(['a' => 10]); // doesn't exist
        $this->assertEquals(3, $bag->count());
    }

    /**
     * @covers AssetBag::add()
     */
    public function testAddWeighted(): void
    {
        $bag = new AssetBag();
        $bag->add(['A' => 3]);
        $bag->add(['B' => 1]);
        $bag->add(['C' => 2]);
        $this->assertEquals(['B', 'C', 'A'], $bag->all());
    }

    /**
     * @covers AssetBag::add()
     * @see http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.other.sort-order
     */
    public function testAddSameWeighted(): void
    {
        $this->markTestSkipped('Currently skipped due to indeterminate behaviour in PHP 7.');

        $bag = new AssetBag();
        $bag->add(['B' => 3]);
        $bag->add(['A' => 3]);
        $bag->add(['C' => 3]);
        $this->assertNotEquals(['A', 'B', 'C'], $bag->all()); // order cannot be assumed with same weight
    }

    /**
     * @covers AssetBag::getIterator()
     */
    public function testIterator(): void
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
    public function testAddDuplicates(): void
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
    public function testKeepLowestWeightedSubmission(): void
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
