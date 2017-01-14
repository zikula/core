<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Collector;

use Zikula\UsersModule\ProfileModule\ProfileModuleInterface;

/**
 * Class ProfileModuleCollector
 */
class ProfileModuleCollector
{
    /**
     * @var ProfileModuleInterface[] e.g. [<moduleName> => <ServiceObject>]
     */
    private $profileModules = [];

    /**
     * ProfileModuleCollector constructor.
     */
    public function __construct()
    {
    }

    /**
     * Add a service to the collection.
     * @param string $moduleName
     * @param ProfileModuleInterface $service
     */
    public function add($moduleName, ProfileModuleInterface $service)
    {
        if (isset($this->profileModules[$moduleName])) {
            throw new \InvalidArgumentException('Attempting to register an authentication method with a duplicate alias. (' . $moduleName . ')');
        }
        $this->profileModules[$moduleName] = $service;
    }

    /**
     * Get an ProfileModuleInterface from the collection by moduleName.
     * @param $moduleName
     * @return ProfileModuleInterface|null
     */
    public function get($moduleName)
    {
        return isset($this->profileModules[$moduleName]) ? $this->profileModules[$moduleName] : null;
    }

    /**
     * Get all the profileModules in the collection.
     * @return ProfileModuleInterface[]
     */
    public function getAll()
    {
        return $this->profileModules;
    }

    /**
     * @return array of service aliases
     */
    public function getKeys()
    {
        return array_keys($this->profileModules);
    }
}
