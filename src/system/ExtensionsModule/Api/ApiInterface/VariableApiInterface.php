<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Api\ApiInterface;

interface VariableApiInterface
{
    /**
     * Replace specified variable values with their localized value
     * @see \Zikula\SettingsModule\Listener\LocalizedVariableListener
     * @param $lang
     */
    public function localizeVariables($lang);

    /**
     * Checks to see if an extension variable is set.
     * @api Core-2.0
     *
     * @param string $extensionName The name of the extension
     * @param string $variableName The name of the variable
     *
     * @return boolean True if the variable exists in the database, false if not
     */
    public function has($extensionName, $variableName);

    /**
     * Get an extension variable.
     * @api Core-2.0
     *
     * @param string $extensionName The name of the extension or pseudo-extension (e.g., 'ZikulaUsersModule', 'ZConfig', '/EventHandlers')
     * @param string $variableName The name of the variable
     * @param mixed $default The value to return if the requested var is not set
     *
     * @return mixed - extension variable value
     */
    public function get($extensionName, $variableName, $default = false);

    /**
     * Get a system variable.
     * @api Core-2.0
     *
     * @param string $variableName The name of the variable
     * @param mixed $default The value to return if the requested var is not set
     *
     * @return mixed - extension variable value
     */
    public function getSystemVar($variableName, $default = false);

    /**
     * Get all the variables for an extension.
     * @api Core-2.0
     *
     * @param $extensionName
     * @return array
     */
    public function getAll($extensionName);

    /**
     * Set an extension variable.
     * @api Core-2.0
     *
     * @param string $extensionName The name of the extension
     * @param string $variableName The name of the variable
     * @param string $value The value of the variable
     *
     * @return boolean True if successful, false otherwise
     */
    public function set($extensionName, $variableName, $value = '');

    /**
     * The setAll method sets multiple extension variables.
     * @api Core-2.0
     *
     * @param string $extensionName The name of the extension
     * @param array $vars An associative array of varnames/varvalues
     *
     * @return boolean True if successful, false otherwise
     */
    public function setAll($extensionName, array $vars);

    /**
     * Delete an extension variable.
     * @api Core-2.0
     *
     * @param string $extensionName The name of the extension
     * @param string $variableName The name of the variable
     *
     * @return boolean True if successful (or var didn't exist), false otherwise
     */
    public function del($extensionName, $variableName);

    /**
     * Delete all variables for one extension.
     * @api Core-2.0
     *
     * @param $extensionName
     * @return bool
     */
    public function delAll($extensionName);
}
