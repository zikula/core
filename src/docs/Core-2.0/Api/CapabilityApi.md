CapabilityApi
=============

classname: \Zikula\ExtensionsModule\Api\CapabilityApi

service id="zikula_extensions_module.api.capability"

This class defines and tests for the capabilities of an extension. This class replaces similar functionality
from ModUtil::* methods.

The class makes the following methods available:

    - getExtensionsCapableOf($capability)
    - isCapable($extensionName, $requestedCapability)
    - getCapabilitiesOf($extensionName)

The class is fully tested.

CapabilityApiInterface
----------------------

classname: \Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface

This interface defines several constants for use in the Core and Extensions. The **strings** should be used
when defining the capabilities array in an extension's `composer.json` file.

    CapabilityApiInterface::HOOK_SUBSCRIBER = 'hook_subscriber';
    CapabilityApiInterface::HOOK_PROVIDER = 'hook_provider';
    CapabilityApiInterface::HOOK_SUBSCRIBE_OWN = 'subscribe_own';
    CapabilityApiInterface::SEARCHABLE = 'searchable';
    CapabilityApiInterface::CATEGORIZABLE = 'categorizable';
    CapabilityApiInterface::USER = 'user';
    CapabilityApiInterface::ADMIN = 'admin';

See the Interface class for more details.


Defining Capabilities in an Extension
=====================================

The capabilities array is defined in an extension's `composer.json` file as an "array of arrays".
Although no extension would implement every capability, here is a full example of capabilities tested within the Core.

    extra": {
        "zikula": {
            "url": "
            "capabilities": {
                "hook_subscriber": {"class": "Acme\\FooModule\\Container\\HookContainer", "subscribe_own": true},
                "hook_provider": {"class": "Acme\\FooModule\\Container\\HookContainer"},
                "searchable": {"class": "Acme\\FooModule\\Helper\\SearchHelper"},
                "categorizable": {"entities": ["Acme\\FooModule\\Entity\\FooEntity", "Acme\\FooModule\\Entity\\BarEntity"]},
                "user": {"route": "acmefoomodule_user_index"}
                "admin": {"route": "acmefoomodule_admin_index"}
            }
        }
    }

Please note: the `version` strings currently have no meaning unless the providing extension utilizes it.

Third party extensions are welcome to add custom capabilities and test for those as required.
