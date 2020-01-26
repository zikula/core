# Cache clearer

The cache clearer is implemented by the `\Zikula\Bundle\CoreBundle\CacheClearer` class and intended to be used
for clearing (parts of) the Symfony cache. The cache clearer provides one method: `clear($type)`. The `$type`
argument determines what part of the cache shall be deleted. The following types are supported:

1. `symfony`
  - `symfony.routing.generator`: Deletes the url generator files.
  - `symfony.routing.matcher`:   Deletes the url matcher files.
  - `symfony.routing.fosjs`:     Deletes the cache files for route generation in javascript (using the FOSJsRoutingBundle)
  - `symfony.config`:            Deletes the container configuration cache files.
  - `symfony.annotations`
  - `symfony.translations`
2. `twig`
3. `purifier`
4. `assets`

**Note:** The service keys are "namespaced", meaning you can also specify `symfony.routing` to delete the url `generator`
AND `matcher` files. Or, specifying simply `symfony` clears *all* symfony caches.

Usage example:

```php
$cacheClearer->clear('symfony.config');
```
