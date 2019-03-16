<?php

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
     * Constructor.
     *
     * @param SearchableInterface[] $searchables
     */
    public function __construct(iterable $searchables)
    {
        foreach ($searchables as $searchable) {
            $this->add($searchable);
        }
    }

    /**
     * Add a service to the collection.
     *
     * @param SearchableInterface $searchable
     */
    public function add(SearchableInterface $searchable)
    {
        $this->searchableModules[$searchable->getBundleName()] = $searchable;
    }

    /**
     * Get a SearchableInterface from the collection by moduleName.
     *
     * @param $moduleName
     * @return SearchableInterface|null
     */
    public function get($moduleName)
    {
        return isset($this->searchableModules[$moduleName]) ? $this->searchableModules[$moduleName] : null;
    }

    /**
     * Get all the searchableModules in the collection.
     *
     * @return SearchableInterface[]
     */
    public function getAll()
    {
        return $this->searchableModules;
    }

    /**
     * @return array of service aliases
     */
    public function getKeys()
    {
        return array_keys($this->searchableModules);
    }
}
