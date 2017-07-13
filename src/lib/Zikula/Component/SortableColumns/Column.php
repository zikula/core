<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\SortableColumns;

/**
 * Class Column
 *
 * A column defines a column of a data table that is used in conjunction with SortableColumns to
 * assist in the display of column headers and links to facilitate resorting based on column and direction.
 */
class Column
{
    const DIRECTION_ASCENDING = 'ASC';
    const DIRECTION_DESCENDING = 'DESC';

    const CSS_CLASS_UNSORTED = 'z-order-unsorted';
    const CSS_CLASS_ASCENDING = 'z-order-asc';
    const CSS_CLASS_DESCENDING = 'z-order-desc';

    private $name;

    private $defaultSortDirection;

    private $currentSortDirection;

    private $reverseSortDirection;

    private $cssClassString;

    private $isSortColumn = false;

    public function __construct($name, $currentSortDirection = null, $defaultSortDirection = null)
    {
        $this->name = $name;
        $this->currentSortDirection = !empty($currentSortDirection) ? $currentSortDirection : self::DIRECTION_ASCENDING;
        $this->reverseSortDirection = $this->reverse($this->currentSortDirection);
        $this->defaultSortDirection = !empty($defaultSortDirection) ? $defaultSortDirection : self::DIRECTION_ASCENDING;
        $this->cssClassString = self::CSS_CLASS_UNSORTED;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDefaultSortDirection()
    {
        return $this->defaultSortDirection;
    }

    /**
     * @param string $defaultSortDirection
     */
    public function setDefaultSortDirection($defaultSortDirection)
    {
        $this->defaultSortDirection = $defaultSortDirection;
    }

    /**
     * @return string
     */
    public function getCurrentSortDirection()
    {
        return $this->currentSortDirection;
    }

    /**
     * @param string $currentSortDirection
     */
    public function setCurrentSortDirection($currentSortDirection)
    {
        $this->currentSortDirection = $currentSortDirection;
        $this->setCssClassString($this->cssFromDirection($currentSortDirection));
        $this->reverseSortDirection = $this->reverse($currentSortDirection);
    }

    /**
     * @return string
     */
    public function getReverseSortDirection()
    {
        return $this->reverseSortDirection;
    }

    /**
     * @param string $reverseSortDirection
     */
    public function setReverseSortDirection($reverseSortDirection)
    {
        $this->reverseSortDirection = $reverseSortDirection;
    }

    /**
     * @return string
     */
    public function getCssClassString()
    {
        return $this->cssClassString;
    }

    /**
     * @param string $cssClassString
     */
    public function setCssClassString($cssClassString)
    {
        $this->cssClassString = $cssClassString;
    }

    /**
     * @return boolean
     */
    public function isSortColumn()
    {
        return $this->isSortColumn;
    }

    /**
     * @param boolean $isSortColumn
     */
    public function setSortColumn($isSortColumn)
    {
        $this->isSortColumn = $isSortColumn;
    }

    /**
     * @param boolean $isSortColumn
     * @deprecated use `setSortColumn()` instead
     */
    public function setIsSortColumn($isSortColumn)
    {
        $this->setSortColumn($isSortColumn);
    }

    /**
     * reverse the direction constants
     * @param $direction
     * @return string
     */
    private function reverse($direction)
    {
        return ($direction == self::DIRECTION_ASCENDING) ? self::DIRECTION_DESCENDING : self::DIRECTION_ASCENDING;
    }

    /**
     * determine a css class based on the direction
     * @param $direction
     * @return string
     */
    private function cssFromDirection($direction)
    {
        return ($direction == self::DIRECTION_ASCENDING) ? self::CSS_CLASS_ASCENDING : self::CSS_CLASS_DESCENDING;
    }
}
