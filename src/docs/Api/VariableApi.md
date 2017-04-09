VariableApi
===========

classname: \Zikula\ExtensionsModule\Api\VariableApi

service id="zikula_extensions_module.api.variable"

This class manages the storage and retrieval of extension variables and is the intended replacement
for ModUtil::* methods (getVar, setVar, etc) as well as similar functionality in System:: and ThemeUtil::

The class makes the following methods available:

    - has($extensionName, $variableName)
    - get($extensionName, $variableName, $default = false)
    - getSystemVar($variableName, $default = false)
    - getAll($extensionName)
    - set($extensionName, $variableName, $value = '')
    - setAll($extensionName, array $vars)
    - del($extensionName, $variableName)
    - delAll($extensionName)

The class is fully tested.

From classes extending \Zikula\Core\Controller\AbstractController several convenience methods are available:

    - hasVar($variableName)
    - getVar($variableName, $default = false)
    - getVars()
    - setVar($variableName, $value = '')
    - setVars(array $vars)
    - delVar($variableName)
    - delVars()
