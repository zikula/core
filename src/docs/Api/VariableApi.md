# VariableApi

classname: `\Zikula\ExtensionsModule\Api\VariableApi`.

This class manages the storage and retrieval of extension variables and is the intended replacement
for ModUtil::* methods (getVar, setVar, etc) as well as similar functionality in System:: and ThemeUtil::

The class makes the following methods available:

```php
/**
 * Replace specified variable values with their localized value
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
 *
 * @param $extensionName
 * @return array
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
 * The setAll method sets multiple extension variables.
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
```

The class is fully tested.

In classes extending `\Zikula\Core\Controller\AbstractController` several convenience methods are available:

```php
/**
 * Convenience shortcut to get extension variable.
 *
 * @param mixed $default
 * @return mixed
 */
public function getVar(string $variableName, $default = false);

/**
 * Convenience shortcut to get all extension variables.
 */
public function getVars(): array;

/**
 * Convenience shortcut to set extension variable.
 *
 * @param string|integer|boolean $value
 */
public function setVar(string $variableName, $value = ''): bool;

/**
 * Convenience shortcut to set many extension variables.
 */
public function setVars(array $variables = []): bool;

/**
 * Convenience shortcut to delete an extension variable.
 */
public function delVar(string $variableName): bool;

/**
 * Convenience shortcut to delete all extension variables.
 */
public function delVars(): bool;
```

The class is fully tested.
