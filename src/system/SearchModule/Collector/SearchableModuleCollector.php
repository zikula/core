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

namespace Zikula\SearchModule\Collector;

use Zikula\SearchModule\SearchableInterface;

/**
 * Class SearchableModuleCollector
 */
class SearchableModuleCollector
{
    /**
     * @var SearchableInterface[] e.g. [<moduleName> => <ServiceObject>]
     */
    private $searchableModules = [];

    /**
     * @param SearchableInterface[] $searchables
     */
    public function __construct(iterable $searchables = [])
    {
        foreach ($searchables as $searchable) {
            $this->add($searchable);
        }
    }

    /**
     * Add a service to the collection.
     */
    public function add(SearchableInterface $searchable): void
    {
        $this->searchableModules[$searchable->getBundleName()] = $searchable;
    }

    /**
     * Get a SearchableInterface from the collection by module name.
     */
    public function get(string $moduleName): ?SearchableInterface
    {
        return $this->searchableModules[$moduleName] ?? null;
    }

    /**
     * Get all the searchable modules in the collection.
     *
     * @return SearchableInterface[]
     */
    public function getAll(): iterable
    {
        return $this->searchableModules;
    }
}
