<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\SortableColumns\Tests;

use Zikula\Component\SortableColumns\Column;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Column
     */
    private $column;

    public function setup()
    {
        $this->column = new Column('foo');
    }

    /**
     * @covers Column::getName
     */
    public function testGetName()
    {
        $this->assertEquals('foo', $this->column->getName());
    }

    /**
     * @covers Column::setName
     */
    public function testSetName()
    {
        $this->column->setName('bar');
        $this->assertEquals('bar', $this->column->getName());
    }

    /**
     * @covers Column::getDefaultSortDirection
     */
    public function testGetDefaultSortDirection()
    {
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->column->getDefaultSortDirection());
    }

    /**
     * @covers Column::setDefaultSortDirection
     */
    public function getSetDefaultSortDirection()
    {
        $this->column->setDefaultSortDirection(Column::DIRECTION_DESCENDING);
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->column->getDefaultSortDirection());
    }

    /**
     * @covers Column::getCurrentSortDirection
     */
    public function testGetCurrentSortDirection()
    {
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->column->getCurrentSortDirection());
    }

    /**
     * @covers Column::setCurrentSortDirection
     */
    public function testSetCurrentSortDirection()
    {
        $this->column->setCurrentSortDirection(Column::DIRECTION_DESCENDING);
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->column->getCurrentSortDirection());
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->column->getReverseSortDirection());
        $this->assertEquals(Column::CSS_CLASS_DESCENDING, $this->column->getCssClassString());
    }

    /**
     * @covers Column::getReverseSortDirection
     */
    public function testGetReverseSortDirection()
    {
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->column->getReverseSortDirection());
    }

    /**
     * @covers Column::getCssClassString
     */
    public function testGetCssClassString()
    {
        $this->assertEquals(Column::CSS_CLASS_UNSORTED, $this->column->getCssClassString());
    }

    /**
     * @covers Column::setCssClassString
     */
    public function testSetCssClassString()
    {
        $this->column->setCssClassString(Column::CSS_CLASS_ASCENDING);
        $this->assertEquals(Column::CSS_CLASS_ASCENDING, $this->column->getCssClassString());
        $this->column->setCssClassString(Column::CSS_CLASS_DESCENDING);
        $this->assertEquals(Column::CSS_CLASS_DESCENDING, $this->column->getCssClassString());
    }

    /**
     * @covers Column::isSortColumn
     */
    public function testIsSortColumn()
    {
        $this->assertFalse($this->column->isSortColumn());
    }

    /**
     * @covers Column::setIsSortColumn
     */
    public function testSetIsSortColumn()
    {
        $this->column->setIsSortColumn(true);
        $this->assertTrue(true, $this->column->isSortColumn());
    }
}
