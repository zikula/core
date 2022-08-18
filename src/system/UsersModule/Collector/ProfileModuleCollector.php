<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Collector;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SettingsModule\SettingsConstant;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\ProfileModule\IdentityProfileModule;
use Zikula\UsersModule\ProfileModule\ProfileModuleInterface;
use Zikula\UsersModule\Repository\UserRepositoryInterface;

class ProfileModuleCollector
{
    /**
     * @var ProfileModuleInterface[] e.g. [<moduleName> => <ServiceObject>]
     */
    private array $profileModules = [];

    private string $currentProfileModuleName;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly CurrentUserApiInterface $currentUserApi,
        VariableApiInterface $variableApi,
        #[TaggedIterator('zikula.profile_module')]
        iterable $modules
    ) {
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
