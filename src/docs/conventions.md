Conventions
===========

Service Names
-------------
Service names within the Zikula Core must adhere to this naming scheme.

```
zikula_core.internal.<servicename>
zikula_core.legacy.<servicename>
zikula_core.common.<servicename>
```

All extensions must prefix their service names with the full extension name, replacing camel-case with underscores.
```
<vendor>_<extensionname>_module.<servicename>
```

for example:
```
zikula_search_module.front_controller_listener
```


Dependency Management
---------------------

Extensions are responsible to provide/include their own dependencies or create companion modules/bundles to do so.


Translation Provision Responsibility
------------------------------------

#### Core
The core will be released with only English. Translations will be provided as secondary downloads.

#### Third Party Extensions
Third party extensions should endeavor to include all available translations within their release. A quick release 
cycle should be used in order to quickly make available new translations and small bug fixes as they become available. 
While a contributor may make an extension translation available as an additional download, this is discouraged and 
should be included in the main package as soon as the next release.
