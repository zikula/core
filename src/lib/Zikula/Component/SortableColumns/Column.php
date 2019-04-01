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

namespace Zikula\Component\SortableColumns;

/**
 * Class Column
 *
 * A column defines a column of a data table that is used in conjunction with SortableColumns to
 * assist in the display of column headers and links to facilitate resorting based on column and direction.
 */
class Column
{
    public const DIRECTION_ASCENDING = 'ASC';

    public const DIRECTION_DESCENDING = 'DESC';

    public const CSS_CLASS_UNSORTED = 'unsorted';

    public const CSS_CLASS_ASCENDING = 'sorted-asc';

    public const CSS_CLASS_DESCENDING = 'sorted-desc';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $defaultSortDirection;

    /**
     * @var string
     */
    private $currentSortDirection;

    /**
     * @var string
     */
    private $reverseSortDirection;

    /**
     * @var string
     */
    private $cssClassString;

    /**
     * @var bool
     */
    private $isSortColumn = false;

    public function __construct(string $name, string $currentSortDirection = null, string $defaultSortDirection = null)
    {
        $this->name = $name;
        $this->currentSortDirection = !empty($currentSortDirection) ? $currentSortDirection : self::DIRECTION_ASCENDING;
        $this->reverseSortDirection = $this->reverse($this->currentSortDirection);
        $this->defaultSortDirection = !empty($defaultSortDirection) ? $defaultSortDirection : self::DIRECTION_ASCENDING;
        $this->cssClassString = self::CSS_CLASS_UNSORTED;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }

    public function setDefaultSortDirection(string $defaultSortDirection): void
    {
        $this->defaultSortDirection = $defaultSortDirection;
    }

    public function getCurrentSortDirection(): string
    {
        return $this->currentSortDirection;
    }

    public function setCurrentSortDirection(string $currentSortDirection): void
    {
        $this->currentSortDirection = $currentSortDirection;
        $this->setCssClassString($this->cssFromDirection($currentSortDirection));
        $this->reverseSortDirection = $this->reverse($currentSortDirection);
    }

    public function getReverseSortDirection(): string
    {
        return $this->reverseSortDirection;
    }

    public function setReverseSortDirection(string $reverseSortDirection): void
    {
        $this->reverseSortDirection = $reverseSortDirection;
    }

    public function getCssClassString(): string
    {
        return $this->cssClassString;
    }

    public function setCssClassString(string $cssClassString): void
    {
        $this->cssClassString = $cssClassString;
    }

    public function isSortColumn(): bool
    {
        return $this->isSortColumn;
    }

    public function setSortColumn(bool $isSortColumn): void
    {
        $this->isSortColumn = $isSortColumn;
    }

    /**
     * Reverse the direction constants.
     */
    private function reverse(string $direction): string
    {
        return (self::DIRECTION_ASCENDING === $direction) ? self::DIRECTION_DESCENDING : self::DIRECTION_ASCENDING;
    }

    /**
     * Determine a css class based on the direction.
     */
    private function cssFromDirection(string $direction): string
    {
        return (self::DIRECTION_ASCENDING === $direction) ? self::CSS_CLASS_ASCENDING : self::CSS_CLASS_DESCENDING;
    }
}
