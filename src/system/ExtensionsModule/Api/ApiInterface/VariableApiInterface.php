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

namespace Zikula\ExtensionsModule\Api\ApiInterface;

interface VariableApiInterface
{
    /**
     * Replace specified variable values with their localized value.
     * @see \Zikula\SettingsModule\Listener\LocalizedVariableListener
     */
    public function localizeVariables(string $lang): void;

    /**
     * Checks to see if an extension variable is set.
     * @api Core-2.0
     */
    public function has(string $extensionName, string $variableName): bool;

    /**
     * Get an extension variable.
     * @api Core-2.0
     *
     * @param mixed $default The value to return if the requested var is not set
     * @return mixed - extension variable value
     */
    public function get(string $extensionName, string $variableName, $default = false);

    /**
     * Get a system variable.
     * @api Core-2.0
     *
     * @param mixed $default The value to return if the requested var is not set
     * @return mixed - extension variable value
     */
    public function getSystemVar(string $variableName, $default = false);

    /**
     * Get all the variables for an extension.
     * @api Core-2.0
     */
    public function getAll(string $extensionName): array;

    /**
     * Set an extension variable.
     * @api Core-2.0
     *
     * @param mixed $value The value of the variable
     * @return boolean True if successful, false otherwise
     */
    public function set(string $extensionName, string $variableName, $value = ''): bool;

    /**
     * Sets multiple extension variables.
     * @api Core-2.0
     */
    public function setAll(string $extensionName, array $variables = []): bool;

    /**
     * Delete an extension variable.
     * @api Core-2.0
     */
    public function del(string $extensionName, string $variableName): bool;

    /**
     * Delete all variables for one extension.
     * @api Core-2.0
     */
    public function delAll(string $extensionName): bool;
}
