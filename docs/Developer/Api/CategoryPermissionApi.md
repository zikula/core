---
currentMenu: developer-api
---
# CategoryPermissionApi

classname: `\Zikula\CategoriesModule\Api\CategoryPermissionApi`.

The CategoryPermissionApi helps with implementing permission checks based on categories.

The class makes the following method available:

```php
/**
 * Check for access to a certain set of categories.
 *
 * For each category property in the list, check if we have access to that category in that property.
 * Check is done as "ZikulaCategoriesModule:PropertyId:CategoryId", "$regId::$catId"
 *
 * @param AbstractCategoryAssignment[] $categoryAssignments
 */
public function hasCategoryAccess(
    array $categoryAssignments,
    int $permLevel = ACCESS_OVERVIEW,
    bool $requireAccessForAll = false
): bool;
```

`$categoryAssignments` must be an array of `\Zikula\CategoriesModule\Entity\AbstractCategoryAssignment`.

The class is fully tested.

Usage example:

```php
$hasAccess = $this->categoryPermissionApi->hasCategoryAccess($page->getCategoryAssignments());
```
