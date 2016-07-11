<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     * @var string the name of the extension in use.
     */
    private $extensionName;

    /**
     * Convenience shortcut to get Extension Variable.
     * @param string $variableName
     * @param mixed $default
     * @return mixed
     */
    public function getVar($variableName, $default = false)
    {
        return $this->variableApi->get($this->extensionName, $variableName, $default);
    }

    /**
     * Convenience shortcut to get all Extension Variables.
     * @return array
     */
    public function getVars()
    {
        return $this->variableApi->getAll($this->extensionName);
    }

    /**
     * Convenience shortcut to set Extension Variable.
     * @param string $variableName
     * @param string $value
     * @return bool
     */
    public function setVar($variableName, $value = '')
    {
        return $this->variableApi->set($this->extensionName, $variableName, $value);
    }

    /**
     * Convenience shortcut to set many Extension Variables.
     * @param array $variables
     * @return bool
     */
    public function setVars(array $variables)
    {
        return $this->variableApi->setAll($this->extensionName, $variables);
    }

    /**
     * Convenience shortcut to delete an Extension Variable.
     * @param $variableName
     * @return bool
     */
    public function delVar($variableName)
    {
        return $this->variableApi->del($this->extensionName, $variableName);
    }

    /**
     * Convenience shortcut to delete all Extension Variables.
     * @return bool
     */
    public function delVars()
    {
        return $this->variableApi->delAll($this->extensionName);
    }
}
