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

namespace Zikula\ExtensionsModule;

use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class ExtensionVariablesTrait
 */
trait ExtensionVariablesTrait
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var string the name of the extension in use
     */
    private $extensionName;

    /**
     * Convenience shortcut to get extension variable.
     *
     * @param mixed $default
     * @return mixed
     */
    public function getVar(string $variableName, $default = false)
    {
        return $this->variableApi->get($this->extensionName, $variableName, $default);
    }

    /**
     * Convenience shortcut to get all extension variables.
     */
    public function getVars(): array
    {
        return $this->variableApi->getAll($this->extensionName);
    }

    /**
     * Convenience shortcut to set extension variable.
     *
     * @param string|integer|boolean $value
     */
    public function setVar(string $variableName, $value = ''): bool
    {
        return $this->variableApi->set($this->extensionName, $variableName, $value);
    }

    /**
     * Convenience shortcut to set many extension variables.
     */
    public function setVars(array $variables = []): bool
    {
        return $this->variableApi->setAll($this->extensionName, $variables);
    }

    /**
     * Convenience shortcut to delete an extension variable.
     */
    public function delVar(string $variableName): bool
    {
        return $this->variableApi->del($this->extensionName, $variableName);
    }

    /**
     * Convenience shortcut to delete all extension variables.
     */
    public function delVars(): bool
    {
        return $this->variableApi->delAll($this->extensionName);
    }
}
