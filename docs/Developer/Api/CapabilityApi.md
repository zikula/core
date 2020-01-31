# CapabilityApi

classname: `\Zikula\ExtensionsModule\Api\CapabilityApi`.

This class defines and tests for the capabilities of an extension.

The class makes the following methods available:

```php
/**
 * Get all the Extensions with a requested capability.
 *
 * @return ExtensionEntity[]
 */
public function getExtensionsCapableOf(string $capability): iterable;

/**
 * Determine if extension is capable of requested capability.
 * Returns capability array if capability is true.
 *
 * @return array|bool capability definition or false
 */
public function isCapable(string $extensionName, string $requestedCapability);

/**
 * Get the capabilities array of an extension.
 */
public function getCapabilitiesOf(string $extensionName): array;
```

The class is fully tested.

## CapabilityApiInterface

classname: `\Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface`.

This interface defines several constants for use in the Core and Extensions. The **strings** should be used
when defining the capabilities array in an extension's `composer.json` file.

```php
CapabilityApiInterface::CATEGORIZABLE = 'categorizable';
CapabilityApiInterface::USER = 'user';
CapabilityApiInterface::ADMIN = 'admin';
```

See the interface class for more details.

## Defining capabilities in an extension

The capabilities array is defined in an extension's `composer.json` file as an "array of arrays".
Although no extension would implement every capability, here is a full example of capabilities tested within the Core.

```yaml
extra": {
    "zikula": {
        "url": "
        "capabilities": {
            "admin": {
                "route": "acmefoomodule_admin_index",
                "icon": "fas fa-rocket"
            },
            "user": {
                "route": "acmefoomodule_user_index"
            },
            "categorizable": {
                "entities": ["Acme\\FooModule\\Entity\\FooEntity", "Acme\\FooModule\\Entity\\BarEntity"]
            }
        }
    }
}
```

Third party extensions are welcome to add custom capabilities and test for those as required.
