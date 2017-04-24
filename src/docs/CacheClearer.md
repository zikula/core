Cache clearer
=============

class: `\Zikula\Bundle\CoreBundle\CacheClearer`

service name: `zikula.cache_clearer`

The cache clearer is intended to be used for clearing (parts of) the Symfony cache. The cache clearer provides one 
method: `clear($type)`. `$type` determines what part of the cache shall be deleted. The following types are supported:

1. `symfony`
    - `symfony.routing.generator`: Deletes the url generator files.
    - `symfony.routing.matcher`:   Deletes the url matcher files.
    - `symfony.routing.fosjs`:     Deletes the cache files for route generation in javascript (using the FOSJsRoutingBundle)
    - `symfony.config`: Deletes the container configuration cache files.
    - `symfony.annotations`
2. `twig`
3. `purifier`
4. `legacy`
    - `legacy.cache.theme`
    - `legacy.cache.view`
    - `legacy.compiled.theme`
    - `legacy.compiled.view`

**Note:** The service keys are "namespaced", meaning you can also specify `symfony.routing` to delete the url `generator`
AND `matcher` files. Or, specifying simply `legacy` clears *all* legacy caches.

Usage example:

    $cacheClearer = $this->get('zikula.cache_clearer');
    $cacheClearer->clear('symfony.config');
