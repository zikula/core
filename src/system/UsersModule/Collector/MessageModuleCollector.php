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
use Zikula\UsersModule\MessageModule\IdentityMessageModule;
use Zikula\UsersModule\MessageModule\MessageModuleInterface;

/**
 * Class MessageModuleCollector
 */
class MessageModuleCollector
{
    /**
     * @var MessageModuleInterface[] e.g. [<moduleName> => <ServiceObject>]
     */
    private $messageModules = [];

    /**
     * @var string
     */
    private $currentMessageModuleName;

    public function __construct(VariableApiInterface $variableApi, iterable $modules)
    {
        $this->currentMessageModuleName = $variableApi->getSystemVar(SettingsConstant::SYSTEM_VAR_MESSAGE_MODULE, '');
        foreach ($modules as $module) {
            $this->add($module);
        }
    }

    /**
     * Add a service to the collection.
     */
    public function add(MessageModuleInterface $service): void
    {
        $moduleName = $service->getBundleName();
        if ('ZikulaUsersModule' === $moduleName) {
            return;
        }
        if (isset($this->messageModules[$moduleName])) {
            throw new InvalidArgumentException('Attempting to register a message module with a duplicate module name. (' . $moduleName . ')');
        }
        $this->messageModules[$moduleName] = $service;
    }

    /**
     * Get a MessageModuleInterface from the collection by moduleName.
     */
    public function get(string $moduleName): ?MessageModuleInterface
    {
        return $this->messageModules[$moduleName] ?? null;
    }

    /**
     * Get all the modules in the collection.
     *
     * @return MessageModuleInterface[]
     */
    public function getAll(): iterable
    {
        return $this->messageModules;
    }

    /**
     * Get an array of service aliases.
     */
    public function getKeys(): array
    {
        return array_keys($this->messageModules);
    }

    public function getSelected(): MessageModuleInterface
    {
        if (!empty($this->currentMessageModuleName) && isset($this->messageModules[$this->currentMessageModuleName])) {
            return $this->messageModules[$this->currentMessageModuleName];
        }

        return new IdentityMessageModule();
    }
}
