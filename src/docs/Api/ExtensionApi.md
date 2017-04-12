ExtensionApi
============

NOTE: This entire class @deprecated remove at Core-2.0

classname: \Zikula\ExtensionsModule\Api\ExtensionApi

service id="zikula_extensions_module.api.extension"

The class makes the following methods available:

    - getModuleInstanceOrNull($name) 

The class also hosts several constants which identify the state of an extension:

    const STATE_UNINITIALISED = 1;
    const STATE_INACTIVE = 2;
    const STATE_ACTIVE = 3;
    const STATE_MISSING = 4;
    const STATE_UPGRADED = 5;
    const STATE_NOTALLOWED = 6;
    const STATE_INVALID = -1;
    const INCOMPATIBLE_CORE_SHIFT = 20;
