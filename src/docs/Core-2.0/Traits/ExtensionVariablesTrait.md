ExtensionVariablesTrait
=======================

name: \Zikula\ExtensionsModule\ExtensionVariablesTrait

Adds the following methods to your class:

 - getVar($variableName, $default = false)
 - getVars()
 - setVar($variableName, $value = '')
 - setVars(array $variables)
 - delVar($variableName)
 - delVars()

In your constructor, you are required to set the following properties included in the trait:

 - $variableApi
   - Must be set to the "zikula_extensions_module.api.variable" service
 - $extensionName
   - Must be set to the "common name" of the extension (e.g. "ZikulaBlocksModule")

See \Zikula\Core\AbstractExtensionInstaller for usage example.