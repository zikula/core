---
currentMenu: search
---
# SearchApi

Interface: `\Zikula\SearchModule\Api\ApiInterface\SearchApiInterface`.  
Class: `\Zikula\SearchModule\Api\SearchApi`.

This class is used to perform a global search.

The class makes the following methods available:

```php
/**
 * Perform the search.
 *
 * @param string $q             query string to search
 * @param bool   $firstPage     (optional) is this first search attempt? is so - basic search is performed
 * @param string $searchType    (optional) search type (default='AND')
 * @param string $searchOrder   (optional) search order (default='newest')
 * @param int    $limit         (optional) number of items to return (default value based on Search settings, -1 for no limit)
 * @param int    $page          (optional) page number (default=1)
 * @param array  $moduleData    (optional) array of module-provided data from Zikula\SearchModule\SearchableInterface::amendForm
 *
 * @return array array of items array and result count
 */
public function search(
    string $q,
    bool $firstPage = false,
    string $searchType = 'AND',
    string $searchOrder = 'newest',
    int $limit = -1,
    int $page = 1,
    array $moduleData = []
): array;

/**
 * Log search query for search statistics.
 */
public function log(string $q = null): void;
```

The class is fully tested.
