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

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SortableColumns
 *
 * SortableColumns is a zikula component to help manage data table column headings that can be clicked to sort the data.
 * The collection is an ArrayCollection of Zikula\Component\SortableColumns\Column objects.
 * Use the ::generateSortableColumns method to create an array of attributes (url, css class) indexed by column name
 * which can be used in the generation of table headings/links.
 */
class SortableColumns
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * The route name string to generate urls for column headers
     * @var string
     */
    private $routeName;

    /**
     * A collection of Columns to manage
     * @var ArrayCollection
     */
    private $columnCollection;

    /**
     * The default column (if unset, the first column add is used)
     * @var Column
     */
    private $defaultColumn;

    /**
     * The column used to sort the data
     * @var Column
     */
    private $sortColumn;

    /**
     * The direction to sorted (constant from Column class)
     * @var string
     */
    private $sortDirection = Column::DIRECTION_ASCENDING;

    /**
     * The name of the html field that holds the selected orderBy field (default: `sort-field`)
     * @var string
     */
    private $sortFieldName;

    /**
     * The name of the html field that holds the selected orderBy direction (default: `sort-direction`)
     * @var string
     */
    private $directionFieldName;

    /**
     * Additional url parameters that must be included in the generated urls
     * @var array
     */
    private $additionalUrlParameters = [];

    public function __construct(
        RouterInterface $router,
        string $routeName,
        string $sortFieldName = 'sort-field',
        string $directionFieldName = 'sort-direction'
    ) {
        $this->router = $router;
        $this->routeName = $routeName;
        $this->sortFieldName = $sortFieldName;
        $this->directionFieldName = $directionFieldName;
        $this->columnCollection = new ArrayCollection();
    }

    /**
     * Create an array of column definitions indexed by column name
     * <code>
     *   ['a' =>
     *     ['url' => '/foo?sort-direction=ASC&sort-field=a',
     *      'class' => 'z-order-unsorted'
     *     ],
     *   ]
     * </code>
     */
    public function generateSortableColumns(): array
    {
        $resultArray = [];
        /** @var Column $column */
        foreach ($this->columnCollection as $column) {
            $this->additionalUrlParameters[$this->directionFieldName] = $column->isSortColumn() ? $column->getReverseSortDirection() : $column->getCurrentSortDirection();
            $this->additionalUrlParameters[$this->sortFieldName] = $column->getName();
            $resultArray[$column->getName()] = [
                'url' => $this->router->generate($this->routeName, $this->additionalUrlParameters),
                'class' => $column->getCssClassString(),
            ];
        }

        return $resultArray;
    }

    /**
     * Add one column.
     */
    public function addColumn(Column $column): void
    {
        $this->columnCollection->set($column->getName(), $column);
    }

    /**
     * Shortcut to add an array of columns.
     */
    public function addColumns(array $columns = []): void
    {
        foreach ($columns as $column) {
            if ($column instanceof Column) {
                $this->addColumn($column);
            } else {
                throw new InvalidArgumentException('Columns must be an instance of \Zikula\Component\SortableColumns\Column.');
            }
        }
    }

    public function removeColumn(string $name): void
    {
        $this->columnCollection->remove($name);
    }

    public function getColumn(string $name): ?Column
    {
        return $this->columnCollection->get($name);
    }

    /**
     * Set the column to sort by and the sort direction.
     */
    public function setOrderBy(Column $sortColumn = null, string $sortDirection = null): void
    {
        $sortColumn = $sortColumn ?: $this->getDefaultColumn();
        if (null === $sortColumn) {
            return;
        }
        $sortDirection = $sortDirection ?: Column::DIRECTION_ASCENDING;
        $this->setSortDirection($sortDirection);
        $this->setSortColumn($sortColumn);
    }

    /**
     * Shortcut to set OrderBy using the Request object.
     */
    public function setOrderByFromRequest(Request $request): void
    {
        if (null === $this->getDefaultColumn()) {
            return;
        }
        $sortColumnName = $request->get($this->sortFieldName, $this->getDefaultColumn()->getName());
        $sortDirection = $request->get($this->directionFieldName, Column::DIRECTION_ASCENDING);
        $this->setOrderBy($this->getColumn($sortColumnName), $sortDirection);
    }

    public function getSortColumn(): ?Column
    {
        return $this->sortColumn ?? $this->getDefaultColumn();
    }

    private function setSortColumn(Column $sortColumn): void
    {
        if ($this->columnCollection->contains($sortColumn)) {
            $this->sortColumn = $sortColumn;
            $sortColumn->setSortColumn(true);
            $sortColumn->setCurrentSortDirection($this->getSortDirection());
        }
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    private function setSortDirection(string $sortDirection): void
    {
        if (in_array($sortDirection, [Column::DIRECTION_ASCENDING, Column::DIRECTION_DESCENDING], true)) {
            $this->sortDirection = $sortDirection;
        }
    }

    public function getDefaultColumn(): ?Column
    {
        if (!empty($this->defaultColumn)) {
            return $this->defaultColumn;
        }

        return $this->columnCollection->first();
    }

    public function setDefaultColumn(Column $defaultColumn): void
    {
        $this->defaultColumn = $defaultColumn;
    }

    public function getAdditionalUrlParameters(): array
    {
        return $this->additionalUrlParameters;
    }

    public function setAdditionalUrlParameters(array $additionalUrlParameters = []): void
    {
        $this->additionalUrlParameters = $additionalUrlParameters;
    }
}
