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

namespace Zikula\UsersModule\Collector;

use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SettingsModule\SettingsConstant;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\ProfileModule\IdentityProfileModule;
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
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var string
     */
    private $currentProfileModuleName;

    /**
     * Constructor.
     *
     * @param UserRepositoryInterface $userRepository
     * @param CurrentUserApiInterface $currentUserApi
     * @param VariableApiInterface $variableApi
     * @param ProfileModuleInterface[] $modules
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        CurrentUserApiInterface $currentUserApi,
        VariableApiInterface $variableApi,
        iterable $modules
    ) {
        $this->userRepository = $userRepository;
        $this->currentUserApi = $currentUserApi;
        $this->currentProfileModuleName = $variableApi->getSystemVar(SettingsConstant::SYSTEM_VAR_PROFILE_MODULE, '');
        foreach ($modules as $module) {
            $this->add($module);
        }
    }

    /**
     * Add a service to the collection.
     *
     * @param ProfileModuleInterface $service
     */
    public function add(ProfileModuleInterface $service)
    {
        $moduleName = $service->getBundleName();
        if ('ZikulaUsersModule' === $moduleName) {
            return;
        }
        if (isset($this->profileModules[$moduleName])) {
            throw new \InvalidArgumentException('Attempting to register a profile module with a duplicate module name. (' . $moduleName . ')');
        }
        $this->profileModules[$moduleName] = $service;
    }

    /**
     * Get a ProfileModuleInterface from the collection by moduleName.
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

    /**
     * @return ProfileModuleInterface
     */
    public function getSelected()
    {
        if (!empty($this->currentProfileModuleName) && isset($this->profileModules[$this->currentProfileModuleName])) {
            return $this->profileModules[$this->currentProfileModuleName];
        }

        return new IdentityProfileModule($this->userRepository, $this->currentUserApi);
    }
}
