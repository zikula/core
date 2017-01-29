<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     * SearchableModuleCollector constructor.
     */
    public function __construct()
    {
    }

    /**
     * Add a service to the collection.
     * @param string $moduleName
     * @param SearchableInterface $service
     */
    public function add($moduleName, SearchableInterface $service)
    {
        if (isset($this->searchableModules[$moduleName])) {
            throw new \InvalidArgumentException('Attempting to register a searchable module with a duplicate module name. (' . $moduleName . ')');
        }
        $this->searchableModules[$moduleName] = $service;
    }

    /**
     * Get a SearchableInterface from the collection by moduleName.
     * @param $moduleName
     * @return SearchableInterface|null
     */
    public function get($moduleName)
    {
        return isset($this->searchableModules[$moduleName]) ? $this->searchableModules[$moduleName] : null;
    }

    /**
     * Get all the searchableModules in the collection.
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
