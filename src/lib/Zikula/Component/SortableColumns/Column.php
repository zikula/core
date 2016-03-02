<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\SortableColumns;

/**
 * Class Column
 * @package Zikula\Component\SortableColumns
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
    public function setIsSortColumn($isSortColumn)
    {
        $this->isSortColumn = $isSortColumn;
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
