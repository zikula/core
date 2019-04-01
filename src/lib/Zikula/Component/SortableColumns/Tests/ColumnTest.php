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

namespace Zikula\Component\SortableColumns\Tests;

use PHPUnit\Framework\TestCase;
use Zikula\Component\SortableColumns\Column;

class ColumnTest extends TestCase
{
    /**
     * @var Column
     */
    private $column;

    protected function setup(): void
    {
        $this->column = new Column('foo');
    }

    /**
     * @covers Column::getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('foo', $this->column->getName());
    }

    /**
     * @covers Column::setName
     */
    public function testSetName(): void
    {
        $this->column->setName('bar');
        $this->assertEquals('bar', $this->column->getName());
    }

    /**
     * @covers Column::getDefaultSortDirection
     */
    public function testGetDefaultSortDirection(): void
    {
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->column->getDefaultSortDirection());
    }

    /**
     * @covers Column::setDefaultSortDirection
     */
    public function getSetDefaultSortDirection(): void
    {
        $this->column->setDefaultSortDirection(Column::DIRECTION_DESCENDING);
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->column->getDefaultSortDirection());
    }

    /**
     * @covers Column::getCurrentSortDirection
     */
    public function testGetCurrentSortDirection(): void
    {
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->column->getCurrentSortDirection());
    }

    /**
     * @covers Column::setCurrentSortDirection
     */
    public function testSetCurrentSortDirection(): void
    {
        $this->column->setCurrentSortDirection(Column::DIRECTION_DESCENDING);
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->column->getCurrentSortDirection());
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->column->getReverseSortDirection());
        $this->assertEquals(Column::CSS_CLASS_DESCENDING, $this->column->getCssClassString());
    }

    /**
     * @covers Column::getReverseSortDirection
     */
    public function testGetReverseSortDirection(): void
    {
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->column->getReverseSortDirection());
    }

    /**
     * @covers Column::getCssClassString
     */
    public function testGetCssClassString(): void
    {
        $this->assertEquals(Column::CSS_CLASS_UNSORTED, $this->column->getCssClassString());
    }

    /**
     * @covers Column::setCssClassString
     */
    public function testSetCssClassString(): void
    {
        $this->column->setCssClassString(Column::CSS_CLASS_ASCENDING);
        $this->assertEquals(Column::CSS_CLASS_ASCENDING, $this->column->getCssClassString());
        $this->column->setCssClassString(Column::CSS_CLASS_DESCENDING);
        $this->assertEquals(Column::CSS_CLASS_DESCENDING, $this->column->getCssClassString());
    }

    /**
     * @covers Column::isSortColumn
     */
    public function testIsSortColumn(): void
    {
        $this->assertFalse($this->column->isSortColumn());
    }

    /**
     * @covers Column::setSortColumn
     */
    public function testSetSortColumn(): void
    {
        $this->column->setSortColumn(true);
        $this->assertTrue($this->column->isSortColumn());
    }
}
