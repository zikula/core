VariableApi
===========

classname: \Zikula\ExtensionsModule\Api\VariableApi

service id="zikula_extensions_module.api.variable"

This class manages the storage and retrieval of extension variables and is the intended replacement
for ModUtil::* methods (getVar, setVar, etc) as well as similar functionality in System:: and ThemeUtil::

The class makes the following methods available:

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

The class is fully tested.

From classes extending \Zikula\Core\Controller\AbstractController several convenience methods are available:

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
