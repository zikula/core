# ExtensionVariablesTrait

The trait implemented by `\Zikula\ExtensionsModule\ExtensionVariablesTrait` adds the following methods to your class:

- `getVar($variableName, $default = false)`
- `getVars()`
- `setVar($variableName, $value = '')`
- `setVars(array $variables)`
- `delVar($variableName)`
- `delVars()`

In your constructor, you are required to set the following properties included in the trait:

- `$variableApi`
  - Must be set to the `VariableApi` service
- $extensionName
  - Must be set to the "common name" of the extension (e.g. `ZikulaBlocksModule`)

See `\Zikula\Bundle\CoreBundle\AbstractExtensionInstaller` for usage example.
