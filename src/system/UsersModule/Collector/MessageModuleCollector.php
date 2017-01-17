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

use Zikula\ExtensionsModule\Api\VariableApi;
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

    /**
     * MessageModuleCollector constructor.
     * @param VariableApi $variableApi
     */
    public function __construct(VariableApi $variableApi)
    {
        $this->currentMessageModuleName = $variableApi->getSystemVar(SettingsConstant::SYSTEM_VAR_MESSAGE_MODULE, '');
    }

    /**
     * Add a service to the collection.
     * @param string $moduleName
     * @param MessageModuleInterface $service
     */
    public function add($moduleName, MessageModuleInterface $service)
    {
        if (isset($this->messageModules[$moduleName])) {
            throw new \InvalidArgumentException('Attempting to register a message module with a duplicate module name. (' . $moduleName . ')');
        }
        $this->messageModules[$moduleName] = $service;
    }

    /**
     * Get a MessageModuleInterface from the collection by moduleName.
     * @param $moduleName
     * @return MessageModuleInterface|null
     */
    public function get($moduleName)
    {
        return isset($this->messageModules[$moduleName]) ? $this->messageModules[$moduleName] : null;
    }

    /**
     * Get all the messageModules in the collection.
     * @return MessageModuleInterface[]
     */
    public function getAll()
    {
        return $this->messageModules;
    }

    /**
     * @return array of service aliases
     */
    public function getKeys()
    {
        return array_keys($this->messageModules);
    }

    /**
     * @return MessageModuleInterface
     */
    public function getSelected()
    {
        if (!empty($this->currentMessageModuleName) && isset($this->messageModules[$this->currentMessageModuleName])) {
            return $this->messageModules[$this->currentMessageModuleName];
        } else {
            return new IdentityMessageModule();
        }
    }
}
