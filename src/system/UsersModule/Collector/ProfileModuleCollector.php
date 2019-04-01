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

use InvalidArgumentException;
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
     */
    public function add(ProfileModuleInterface $service): void
    {
        $moduleName = $service->getBundleName();
        if ('ZikulaUsersModule' === $moduleName) {
            return;
        }
        if (isset($this->profileModules[$moduleName])) {
            throw new InvalidArgumentException('Attempting to register a profile module with a duplicate module name. (' . $moduleName . ')');
        }
        $this->profileModules[$moduleName] = $service;
    }

    /**
     * Get a ProfileModuleInterface from the collection by moduleName.
     */
    public function get(string $moduleName): ?ProfileModuleInterface
    {
        return $this->profileModules[$moduleName] ?? null;
    }

    /**
     * Get all the modules in the collection.
     *
     * @return ProfileModuleInterface[]
     */
    public function getAll(): iterable
    {
        return $this->profileModules;
    }

    /**
     * Get an array of service aliases.
     */
    public function getKeys(): array
    {
        return array_keys($this->profileModules);
    }

    public function getSelected(): ProfileModuleInterface
    {
        if (!empty($this->currentProfileModuleName) && isset($this->profileModules[$this->currentProfileModuleName])) {
            return $this->profileModules[$this->currentProfileModuleName];
        }

        return new IdentityProfileModule($this->userRepository, $this->currentUserApi);
    }
}
